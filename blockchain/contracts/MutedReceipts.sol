// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

/**
 * Minimal receipt logger. No transfers, only immutable events and a simple guard
 * to avoid duplicate writes for the same paymentId.
 */
contract MutedReceipts {
    event ReceiptRecorded(bytes32 indexed paymentId, address indexed payer, uint256 amountWei, uint256 timestamp);

    mapping(bytes32 => bool) public exists;

    function recordReceipt(bytes32 paymentId, uint256 amountWei, address payer) external {
        require(!exists[paymentId], "Already recorded");
        exists[paymentId] = true;
        emit ReceiptRecorded(paymentId, payer, amountWei, block.timestamp);
    }
}


