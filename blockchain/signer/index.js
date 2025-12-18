/*
 Minimal signer service for writing muted receipts on Polygon Amoy.
 Separate from PHP codebase. Start with: `node index.js` after setting env.
*/
require('dotenv').config();
const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const { ethers } = require('ethers');

const app = express();
app.use(helmet());
app.use(cors({ origin: true }));
app.use(express.json());

// Environment
const PORT = process.env.PORT || 4001;
const RPC_URL = process.env.RPC_URL || 'https://polygon-amoy-bor-rpc.publicnode.com';
const PRIVATE_KEY = process.env.PRIVATE_KEY || 'adfc6e1c52e1f9119435c6b5ea82da9e08d299e3403b5a59b93b8c5b1eb4115b';
const CONTRACT_ADDRESS = process.env.CONTRACT_ADDRESS || '0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F'; // 0x0E97... from deploy
const AUTH_TOKEN = process.env.AUTH_TOKEN || 'kabaka-secret-2024'; // simple shared secret

if (!PRIVATE_KEY || !CONTRACT_ADDRESS) {
  // eslint-disable-next-line no-console
  console.error('Missing PRIVATE_KEY or CONTRACT_ADDRESS in environment.');
  process.exit(1);
}

const CONTRACT_ABI = [
  { inputs: [
      { internalType: 'bytes32', name: 'paymentId', type: 'bytes32' },
      { internalType: 'uint256', name: 'amountWei', type: 'uint256' },
      { internalType: 'address', name: 'payer', type: 'address' }
    ], name: 'recordReceipt', outputs: [], stateMutability: 'nonpayable', type: 'function' },
  { inputs: [{ internalType: 'bytes32', name: '', type: 'bytes32' }], name: 'exists', outputs: [{ internalType: 'bool', name: '', type: 'bool' }], stateMutability: 'view', type: 'function' }
];

const provider = new ethers.JsonRpcProvider(RPC_URL);
const wallet = new ethers.Wallet(PRIVATE_KEY, provider);
const contract = new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, wallet);

function toBytes32(value) {
  if (typeof value === 'string' && /^0x[0-9a-fA-F]{64}$/.test(value)) return value;
  return ethers.id(String(value));
}

function isAddress(addr) {
  try { return ethers.isAddress(addr); } catch { return false; }
}

app.post('/record', async (req, res) => {
  try {
    if (AUTH_TOKEN) {
      const header = req.headers.authorization || '';
      if (!header.startsWith('Bearer ') || header.slice(7) !== AUTH_TOKEN) {
        return res.status(401).json({ ok: false, error: 'unauthorized' });
      }
    }

    const { payment_id, amount_wei, payer_address } = req.body || {};
    if (!payment_id) return res.status(400).json({ ok: false, error: 'payment_id required' });
    if (amount_wei === undefined || amount_wei === null) return res.status(400).json({ ok: false, error: 'amount_wei required' });

    const paymentId = toBytes32(payment_id);
    const amountWei = BigInt(String(amount_wei));
    const payer = isAddress(payer_address) ? payer_address : wallet.address;

    // Estimate gas with buffer
    const estimated = await contract.recordReceipt.estimateGas(paymentId, amountWei, payer);
    const gasLimit = estimated + (estimated / 5n);

    const tx = await contract.recordReceipt(paymentId, amountWei, payer, { gasLimit });
    const receipt = await tx.wait(1);

    return res.json({
      ok: true,
      tx_hash: receipt.hash,
      block_number: receipt.blockNumber,
      contract: CONTRACT_ADDRESS,
      chain: 'polygon-amoy'
    });
  } catch (err) {
    const msg = (err && err.message) ? err.message : String(err);
    const isDuplicate = /Already recorded/i.test(msg);
    return res.status(isDuplicate ? 409 : 500).json({ ok: false, error: msg });
  }
});

app.get('/health', async (_req, res) => {
  try {
    const net = await provider.getNetwork();
    res.json({ ok: true, chainId: Number(net.chainId) });
  } catch (e) {
    res.status(500).json({ ok: false, error: String(e) });
  }
});

app.listen(PORT, () => {
  // eslint-disable-next-line no-console
  console.log(`Signer listening on http://localhost:${PORT}`);
});


