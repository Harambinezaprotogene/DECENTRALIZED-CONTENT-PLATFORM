## Pay‑Per‑Download (PPD) – Troubleshooting Guide

Use this when USDT sends intermittently fail, verification doesn’t unlock downloads, or MetaMask throws RPC errors.

### 1) Quick Checklist (most fixes)
- PPD env vars are set in `public/.htaccess` and Apache restarted
  - `SetEnv PPD_FAKE_SUCCESS 0`
  - `SetEnv PPD_USDT_ADDRESS 0x<YOUR_TEST_USDT_CONTRACT>`
  - `SetEnv PPD_TOKEN_DECIMALS 6`
  - `SetEnv PPD_PRICE_USDT 1`
  - `SetEnv PPD_RPC_URL https://polygon-amoy-bor.publicnode.com`
- Sender on Polygon Amoy in MetaMask and has enough POL for gas
- Receiver imported token (prompt appears on first successful payment)
- Creator has a valid USDT address saved in Settings

If still failing, rotate the RPC in `.htaccess` to a more stable provider (below) and restart Apache.

### 2) Stable RPC options (Amoy)
Try one of the following if you see -32603/“circuit breaker open”:
- `https://polygon-amoy.g.alchemy.com/`
- `https://polygon-amoy.infura.io/v3/`
- `https://rpc-amoy.polygon.technology`
- `https://polygon-amoy-bor.publicnode.com` (current default)

Steps:
1. Edit `public/.htaccess` and change `PPD_RPC_URL`
2. Restart Apache (XAMPP Control Panel → Apache → Stop → Start)

### 3) Common MetaMask/RPC errors
- code -32603 “Execution prevented because the circuit breaker is open”
  - Cause: Provider throttling/instability
  - Fix: Wait 3–5s and retry once; if persists, switch RPC (above)
- code -32603 “Internal JSON‑RPC error”
  - Cause: Transient provider issue; sometimes gas estimation hiccup
  - Fix: Retry once; rotate RPC; ensure sender has POL for gas
- “Insufficient funds” (preflight/eth_call)
  - Fix: Top‑up sender USDT balance; keep small POL balance for gas

Note: We let MetaMask estimate fees; gas limit is pre‑estimated with a small buffer.

### 4) Verifying a payment manually
If a transaction confirmed but the file didn’t unlock:
1. Copy the tx hash from MetaMask
2. Call the verifier directly (POST):
   - Windows PowerShell
     ```powershell
     Invoke-RestMethod -Method Post -Uri "http://localhost:81/kabaka/public/blockchain_download/verify_payment.php" -Body @{ tx_hash = "0xYOUR_TX_HASH" }
     ```
   - curl
     ```bash
     curl -X POST -d "tx_hash=0xYOUR_TX_HASH" http://localhost:81/kabaka/public/blockchain_download/verify_payment.php
     ```
3. Expected success: `{ "ok": true, "verified": true, ... }`
4. If it returns reason like `wrong_token`, `wrong_recipient`, or `wrong_amount`:
   - Check `PPD_USDT_ADDRESS` and `PPD_TOKEN_DECIMALS`
   - Confirm the creator’s saved USDT address matches the intended recipient
   - Confirm `PPD_PRICE_USDT` is correct

### 5) Database checks
- Successful verifications insert into `ppd_payments`.
- Quick check (MySQL):
  ```sql
  SELECT id, content_id, creator_id, tx_hash, amount_smallest, created_at
  FROM ppd_payments
  ORDER BY id DESC
  LIMIT 10;
  ```
- In the creator dashboard → Payments → “Download Transactions” you should see the same rows.

### 6) Creator’s token visibility
- Receivers might not see USDT until imported. We automatically prompt via `wallet_watchAsset` after a successful payment.
- If missed, they can add the token manually in MetaMask using `PPD_USDT_ADDRESS` and `PPD_TOKEN_DECIMALS`.

### 7) Content still not downloading
- Ensure the viewer stayed on the modal until tx hash was obtained
- Verify endpoint must return success to unlock
- Check browser console for “PPD flow error” and see the last two network calls:
  1) `target.php?content_id=...`
  2) `verify_payment.php` (should be 200 with verified=true)

### 8) Environment examples (`public/.htaccess`)
```apache
SetEnv PPD_FAKE_SUCCESS 0
SetEnv PPD_USDT_ADDRESS 0x1234567890abcdef1234567890abcdef12345678
SetEnv PPD_TOKEN_DECIMALS 6
SetEnv PPD_PRICE_USDT 1
SetEnv PPD_RPC_URL https://polygon-amoy-bor.publicnode.com
```
After editing, restart Apache.

### 9) When to rotate RPC vs. fix config
- Rotate RPC when: errors are -32603 and random; some transactions work, others fail
- Fix config when: verify says `wrong_token`, `wrong_recipient`, `wrong_amount`, or creators don’t have USDT address

### 10) FAQs
- Q: Sender paid but receiver doesn’t see balance?
  - A: Import the token in MetaMask (prompt appears), or add manually via contract address.
- Q: Why does it sometimes fail then work later?
  - A: Testnet RPCs are rate‑limited and unstable at times. Rotate RPC or retry after a short delay.
- Q: Can I test without real transfers?
  - A: Set `PPD_FAKE_SUCCESS 1` to bypass on‑chain verification (dev only).

Keep this file next to the PPD code: `public/blockchain_download/TROUBLESHOOTING.md`.


