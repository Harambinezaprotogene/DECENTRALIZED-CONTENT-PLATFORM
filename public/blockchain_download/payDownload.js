// Minimal hook to gate the existing modal download link without UI changes
// Later, replace alert with MetaMask + verification flow
(function(){
    const LOCKED_TEXT = 'Processing…';

    function lockDownloadLink() {
        try {
            const link = document.getElementById('downloadLink');
            if (!link) return;
            // Keep original href but do not expose it
            const existing = link.getAttribute('href') || '';
            if (existing && existing !== '#' && !link.dataset.originalHref) {
                link.dataset.originalHref = existing;
            } else if (!existing || existing === '#') {
                // Retry once on next tick to catch late-bound href set by the app
                setTimeout(() => {
                    const late = link.getAttribute('href') || '';
                    if (late && late !== '#' && !link.dataset.originalHref) {
                        link.dataset.originalHref = late;
                    }
                }, 0);
            }
            link.setAttribute('href', '#');
            link.setAttribute('aria-disabled', 'true');
            link.addEventListener('click', handleClick, { once: false, passive: false });
        } catch (_) {}
    }

    function unlockDownloadLink(signedUrl) {
        const link = document.getElementById('downloadLink');
        if (!link) return;
        const url = signedUrl || link.dataset.originalHref || '#';
        link.setAttribute('href', url);
        link.removeAttribute('aria-disabled');
        // Important: remove with just the handler reference
        link.removeEventListener('click', handleClick);
    }

    async function handleClick(e) {
        try {
            e.preventDefault();
            const link = e.currentTarget;
            // Capture href at click time if it was bound late by the app
            if (!link.dataset.originalHref) {
                const late = link.getAttribute('href') || '';
                if (late && late !== '#') link.dataset.originalHref = late;
            }
            if (!window.currentContentId) {
                alert('Please open content first.');
                return;
            }
            // Disable while starting flow
            const originalHtml = link.innerHTML;
            link.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>'+LOCKED_TEXT;
            link.setAttribute('disabled', 'true');

            // Real flow: fetch target, request MetaMask USDT transfer, send tx hash for verification
            try {
                // Fetch payment target
                const tRes = await fetch(`/kabaka/public/blockchain_download/target.php?content_id=${encodeURIComponent(window.currentContentId)}`, { credentials: 'same-origin' });
                const tData = await tRes.json();
                if (!tRes.ok || !tData || !tData.ok) {
                    throw new Error((tData && tData.error) || 'Failed to load payment target');
                }
                const { chainId, token, price, creator } = tData.data;
                if (!creator || !creator.usdt_address) {
                    alert('Creator has no USDT address configured.');
                    link.innerHTML = originalHtml; link.removeAttribute('disabled');
                    return;
                }

                // Ensure MetaMask
                if (!window.ethereum || !window.ethereum.request) {
                    alert('MetaMask is not installed.');
                    link.innerHTML = originalHtml; link.removeAttribute('disabled');
                    return;
                }

                // Request accounts
                const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
                const from = accounts && accounts[0];
                if (!from) { throw new Error('No account'); }

                // Ensure chain
                const currentChainHex = await window.ethereum.request({ method: 'eth_chainId' });
                const desiredHex = '0x' + parseInt(chainId, 10).toString(16);
                if (currentChainHex !== desiredHex) {
                    try {
                        await window.ethereum.request({ method: 'wallet_switchEthereumChain', params: [{ chainId: desiredHex }] });
                    } catch (switchErr) {
                        // Try add then switch
                        try {
                            await window.ethereum.request({
                                method: 'wallet_addEthereumChain',
                                params: [{ chainId: desiredHex, chainName: 'Polygon Amoy', nativeCurrency: { name: 'MATIC', symbol: 'MATIC', decimals: 18 }, rpcUrls: ['https://rpc-amoy.polygon.technology'], blockExplorerUrls: ['https://www.oklink.com/amoy'] }]
                            });
                            await window.ethereum.request({ method: 'wallet_switchEthereumChain', params: [{ chainId: desiredHex }] });
                        } catch (addErr) {
                            throw new Error('Please switch to Polygon Amoy in MetaMask');
                        }
                    }
                }

                // Preflight: ensure the sender has enough token balance
                try {
                    const balMethod = '0x70a08231'; // balanceOf(address)
                    const fromPadded = '0'.repeat(24) + from.replace(/^0x/i, '');
                    const balanceHex = await window.ethereum.request({
                        method: 'eth_call',
                        params: [{ to: token.address, data: balMethod + fromPadded }, 'latest']
                    });
                    const balance = BigInt(balanceHex);
                    if (balance < BigInt(price.amount)) {
                        throw new Error('Insufficient token balance to pay.');
                    }
                } catch (preErr) {
                    // Do not block on MetaMask/RPC internal errors; continue to send and let it fail there
                    if (!(preErr && preErr.code === -32603)) {
                        // For clear user errors (like real insufficiency), rethrow
                        const msg = (preErr && (preErr.message || preErr.toString())) || '';
                        if (msg.toLowerCase().includes('insufficient')) throw preErr;
                    }
                    console.warn('Token balance preflight skipped:', preErr);
                }

                // ERC-20 transfer call data: transfer(address,uint256)
                const methodId = '0xa9059cbb';
                const toPadded = '0'.repeat(24) + creator.usdt_address.replace(/^0x/i, '');
                const amountHex = BigInt(price.amount).toString(16);
                const amountPadded = amountHex.padStart(64, '0');
                const data = methodId + toPadded + amountPadded;

                // Dry-run the transfer with eth_call to catch revert reasons early
                try {
                    await window.ethereum.request({
                        method: 'eth_call',
                        params: [{ from, to: token.address, data }, 'latest']
                    });
                } catch (simErr) {
                    const msg = (simErr && (simErr.message || simErr.toString())) || 'Transfer simulation failed';
                    // Ignore internal RPC errors, but surface clear insufficiency to user
                    if (simErr && simErr.code === -32603) {
                        console.warn('Transfer simulation skipped:', simErr);
                    } else if (msg.toLowerCase().includes('insufficient')) {
                        throw new Error('Insufficient token balance to pay.');
                    } else {
                        console.warn('Transfer simulation warning:', simErr);
                    }
                }

                // Estimate gas dynamically then add a safety bump, and pass it explicitly
                let gasHex = '0x5dc00';
                try {
                    const est = await window.ethereum.request({
                        method: 'eth_estimateGas',
                        params: [{ from, to: token.address, value: '0x0', data }]
                    });
                    const estBn = BigInt(est);
                    const bumped = (estBn + (estBn / 5n) + 21000n); // +20% and headroom
                    gasHex = '0x' + bumped.toString(16);
                } catch (_) { /* fall back to default */ }

                const txParams = {
                    from,
                    to: token.address,
                    value: '0x0',
                    data,
                    gas: gasHex
                    // Let MetaMask/provider set EIP-1559 fees
                };

                async function sendWithBreakerRetry(params) {
                    try {
                        return await window.ethereum.request({ method: 'eth_sendTransaction', params: [params] });
                    } catch (e) {
                        const msg = (e && (e.message || e.toString())) || '';
                        const isBreaker = (e && e.code === -32603) && (msg.toLowerCase().includes('circuit breaker') || msg.toLowerCase().includes('execution prevented'));
                        if (isBreaker) {
                            // brief cooldown + bump gas, then retry once
                            await new Promise(r => setTimeout(r, 2500));
                            // Retry once without modifying params (provider will re-estimate)
                            return await window.ethereum.request({ method: 'eth_sendTransaction', params: [params] });
                        }
                        // Generic single retry for transient -32603s
                        if (e && e.code === -32603) {
                            await new Promise(r => setTimeout(r, 1500));
                            return await window.ethereum.request({ method: 'eth_sendTransaction', params: [params] });
                        }
                        throw e;
                    }
                }

                const txHash = await sendWithBreakerRetry(txParams);

                // Call backend verification with tx hash
                const vRes = await fetch('/kabaka/public/blockchain_download/verify_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ content_id: window.currentContentId, tx_hash: txHash })
                });
                const vData = await vRes.json().catch(()=>({ ok:false }));
                if (vRes.ok && vData && vData.ok && vData.verified) {
                    // Prompt wallet to add the token so receiver can see balance (EIP-747)
                    try {
                        if (window.ethereum && window.ethereum.request && token && token.address && token.symbol && Number.isFinite(token.decimals)) {
                            await window.ethereum.request({
                                method: 'wallet_watchAsset',
                                params: {
                                    type: 'ERC20',
                                    options: {
                                        address: token.address,
                                        symbol: String(token.symbol).slice(0, 11),
                                        decimals: Number(token.decimals)
                                    }
                                }
                            }).catch(()=>{});
                        }
                    } catch(_) { /* non-fatal */ }
                    let finalUrl = vData.url || null;
                    if (!finalUrl) {
                        // Fallback to original link if server didn't provide a signed URL
                        finalUrl = link.dataset.originalHref || null;
                    }
                    link.innerHTML = originalHtml;
                    link.removeAttribute('disabled');
                    unlockDownloadLink(finalUrl);
                    setTimeout(() => link.click(), 0);
                    return;
                }
                alert((vData && vData.reason) || 'Payment not yet confirmed. Please wait for confirmation and try again.');
            } catch (flowErr) {
                console.error('PPD flow error', flowErr);
                try {
                    const msg = (flowErr && (flowErr.message || flowErr.toString())) || '';
                    const isBreaker = msg.toLowerCase().includes('circuit breaker') || msg.toLowerCase().includes('execution prevented');
                    if (flowErr && flowErr.code === -32603 && isBreaker) {
                        alert('MetaMask temporarily blocked execution. Switch networks away and back to Polygon Amoy, then retry. If it persists, reset account in MetaMask > Settings > Advanced.');
                    } else {
                        alert(flowErr.message || 'Payment flow failed.');
                    }
                } catch(_) {
                    alert('Payment flow failed.');
                }
            }

            // Revert UI (still locked)
            link.innerHTML = originalHtml;
            link.removeAttribute('disabled');
        } catch (err) {
            console.error('download click handler error', err);
            alert('Failed to start download. Try again.');
        }
    }

    // Expose minimal API for later wiring
    window.BlockchainDownload = {
        lock: lockDownloadLink,
        unlock: unlockDownloadLink
    };

    // When modal shows, lock the link
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('contentModal');
        if (!modal) return;
        modal.addEventListener('shown.bs.modal', function(){
            // Ensure the app set href first, then we lock it
            setTimeout(lockDownloadLink, 0);
        });
    });
})();


