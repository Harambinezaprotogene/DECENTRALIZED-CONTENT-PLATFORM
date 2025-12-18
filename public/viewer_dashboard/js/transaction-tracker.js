class TransactionTracker {
    static async watchTransaction(txHash) {
        const receipt = await web3.eth.getTransactionReceipt(txHash);
        if (receipt) {
            const etherscanUrl = `https://sepolia.etherscan.io/tx/${txHash}`;
            PaymentUI.showSuccess(`
                Transaction confirmed! 
                <a href="${etherscanUrl}" target="_blank" class="text-white text-decoration-underline">
                    View on Etherscan
                </a>
            `);
            return receipt;
        }
        
        // Check again in 2 seconds
        await new Promise(resolve => setTimeout(resolve, 2000));
        return TransactionTracker.watchTransaction(txHash);
    }
}