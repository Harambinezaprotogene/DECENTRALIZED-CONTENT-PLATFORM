const ContentPrices = {
    DEFAULT_PRICE: 0.01,
    prices: new Map(),

    setPrice(contentId, price) {
        this.prices.set(contentId, price);
    },

    getPrice(contentId) {
        return this.prices.get(contentId) || this.DEFAULT_PRICE;
    }
};