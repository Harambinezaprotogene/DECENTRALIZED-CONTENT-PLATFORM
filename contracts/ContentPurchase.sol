pragma solidity ^0.8.19;

contract ContentPurchase {
    address payable public owner;
    mapping(address => mapping(uint256 => bool)) public purchases;
    mapping(uint256 => uint256) public contentPrices;
    
    event ContentPurchased(address buyer, uint256 contentId, uint256 price);
    
    constructor() {
        owner = payable(msg.sender);
    }
    
    function setContentPrice(uint256 contentId, uint256 price) public {
        require(msg.sender == owner, "Only owner can set prices");
        contentPrices[contentId] = price;
    }
    
    function purchaseContent(uint256 contentId) public payable {
        require(contentPrices[contentId] > 0, "Content not for sale");
        require(msg.value >= contentPrices[contentId], "Insufficient payment");
        require(!purchases[msg.sender][contentId], "Already purchased");
        
        purchases[msg.sender][contentId] = true;
        owner.transfer(msg.value);
        
        emit ContentPurchased(msg.sender, contentId, msg.value);
    }
    
    function hasPurchased(address buyer, uint256 contentId) public view returns (bool) {
        return purchases[buyer][contentId];
    }
}