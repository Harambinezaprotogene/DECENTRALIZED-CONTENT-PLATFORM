class BlockchainPayment {
    constructor() {
        this.web3 = null;
        this.contract = null;
        this.account = null;
        this.networkId = null;
        this.supportedNetworks = {
            11155111: 'Sepolia',
            1: 'Ethereum Mainnet'
        };
    }

    async validateNetwork() {
        this.networkId = await this.web3.eth.net.getId();
        if (!this.supportedNetworks[this.networkId]) {
            throw new Error(`Please switch to ${this.supportedNetworks[11155111]} network`);
        }
    }

    async init() {
        try {
            // Check if MetaMask is installed
            if (typeof window.ethereum === 'undefined') {
                throw new Error('Please install MetaMask to make purchases');
            }

            // Request account access
            await window.ethereum.request({ method: 'eth_requestAccounts' });
            
            // Create Web3 instance
            this.web3 = new Web3(window.ethereum);
            
            // Get current account
            const accounts = await this.web3.eth.getAccounts();
            this.account = accounts[0];

            // Initialize contract
            this.contract = new this.web3.eth.Contract(
                BlockchainConfig.CONTRACT_ABI,
                BlockchainConfig.CONTRACT_ADDRESS
            );

            await this.validateNetwork();

            // Listen for network changes
            window.ethereum.on('networkChanged', (networkId) => {
                this.networkId = parseInt(networkId);
                if (!this.supportedNetworks[this.networkId]) {
                    this.showNetworkError();
                }
            });

            // Listen for account changes
            window.ethereum.on('accountsChanged', (accounts) => {
                this.account = accounts[0];
            });

            return true;
        } catch (error) {
            this.handleError(error);
            return false;
        }
    }

    handleError(error) {
        let message = 'Transaction failed. ';
        
        if (error.code === 4001) {
            message = 'Transaction rejected by user.';
        } else if (error.message.includes('insufficient funds')) {
            message = 'Insufficient funds for transaction.';
        }
        
        console.error(error);
        throw new Error(message);
    }

    showNetworkError() {
        const message = `Please switch to ${this.supportedNetworks[11155111]} network`;
        alert(message);
    }

    async purchaseContent(contentId, price) {
        if (!this.web3 || !this.contract) {
            await this.init();
        }

        try {
            const priceWei = this.web3.utils.toWei(price.toString(), 'ether');
            
            // Check if already purchased
            const purchased = await this.contract.methods.hasPurchased(this.account, contentId).call();
            if (purchased) {
                return { success: true, alreadyPurchased: true };
            }

            // Make purchase
            const result = await this.contract.methods.purchaseContent(contentId)
                .send({
                    from: this.account,
                    value: priceWei,
                    gas: 200000
                });

            return {
                success: true,
                transactionHash: result.transactionHash
            };
        } catch (error) {
            console.error('Purchase error:', error);
            throw error;
        }
    }
}