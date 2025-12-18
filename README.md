# Kabaka Platform

Kabaka is a content monetization platform that allows creators to earn money through views, tips, and engagement. The platform integrates with blockchain technology for secure payments and includes USSD functionality for mobile access.

## Features
- Content Management: Upload, moderate, and display multimedia content
- Monetization: Earn money through views, tips, and engagement
- Blockchain Payments: Secure USDT transactions via Polygon network
- USSD Access: Mobile balance checking via Africa's Talking
- Admin Dashboard: Comprehensive management tools
- User Management: Creator and viewer roles with different permissions

## Prerequisites
- **PHP 8.0+** with extensions: PDO MySQL, JSON, cURL, OpenSSL
- **MySQL 8.0+**
- **Node.js 16+** (for blockchain signer)
- **Web Server** (Apache/Nginx recommended)
- **Composer** (for PHP dependencies, if any)
- **Git** (for cloning the repository)

## Installation

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd kabaka
```

### Step 2: Database Setup
1. Create a new MySQL database named `kabaka_platform`.
2. Import the database schema:
   ```bash
   mysql -u your_username -p kabaka_platform < database/kabaka.sql
   ```
   Replace `your_username` with your MySQL username.

### Step 3: Environment Configuration
Create a `.env` file in the project root with the following content:
```env
DB_HOST=localhost
DB_NAME=kabaka_platform
DB_USER=your_username
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4

# Blockchain Configuration
RPC_URL=https://rpc-amoy.polygon.technology
PRIVATE_KEY=your_private_key
CONTRACT_ADDRESS=0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F
AUTH_TOKEN=kabaka-secret-2024
```
- Replace placeholders with your actual values.
- Note: Keep the `.env` file secure and never commit it to version control.

### Step 4: Blockchain Signer Setup
1. Navigate to the blockchain signer directory:
   ```bash
   cd blockchain/signer
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Create and configure the `.env` file (similar to the root `.env` but specific to signer):
   ```bash
   cp .env.example .env
   # Edit .env with your RPC URL, private key, contract address, and auth token
   ```
4. Start the signer service:
   ```bash
   node index.js
   ```
   - This service should run continuously to handle blockchain transactions. Consider running it in the background or as a service.

### Step 5: Web Server Configuration
- **For Apache**: Ensure the `.htaccess` file in the `public/` directory is configured for URL rewriting.
- **For Nginx**: Add the following to your server block:
  ```nginx
  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }
  ```
- Place the project in your web server's document root (e.g., `htdocs` for XAMPP).
- Ensure the web server is running and pointing to the `public/` directory.

### Step 6: File Permissions
Set appropriate permissions for security:
```bash
chmod 755 public/
chmod 644 public/*.php
chmod 755 public/uploads/
chmod 644 config/
```

### Step 7: Install PHP Dependencies (if any)
If there are Composer dependencies, run:
```bash
composer install
```

## Running the Project

1. **Start the Web Server**: Ensure Apache/Nginx is running and serving the `public/` directory.
2. **Start the Blockchain Signer**: Run `node index.js` in the `blockchain/signer/` directory.
3. **Access the Application**:
   - Viewer Dashboard: `http://localhost/kabaka/public/viewer_dashboard/`
   - Creator Dashboard: `http://localhost/kabaka/public/creator_dashboard/`
   - Admin Dashboard: `http://localhost/kabaka/public/admin_dashboard/`
   - API Endpoints: Available under `http://localhost/kabaka/public/api/`

## Usage
- **Registration/Login**: Users can register and log in via the respective dashboards.
- **Content Upload**: Creators can upload content through the creator dashboard.
- **Monetization**: Creators can request withdrawals once eligible.
- **Admin Management**: Admins can manage users, content, and settings via the admin dashboard.
- **USSD**: Configure Africa's Talking for mobile balance checks.

## Default Credentials
For testing purposes, you can use the following example credentials (ensure these are set up in your database or register new users):

- **Admin User**:
  - Email: admin@kabaka.com
  - Password: admin123

- **Creator User**:
  - Email: creator@kabaka.com
  - Password: creator123

- **Viewer User**:
  - Email: viewer@kabaka.com
  - Password: viewer123

If these users do not exist, register new accounts through the respective dashboards. For production, always use strong, unique passwords.

## Blockchain Interaction with MetaMask and Polygon Amoy
The platform uses Polygon Amoy testnet for USDT transactions. To interact with the blockchain features (e.g., withdrawals, payments), set up MetaMask as follows:

### Step 1: Install MetaMask
1. Download and install the MetaMask browser extension from [metamask.io](https://metamask.io/).
2. Create or import a wallet. Use a test wallet for development.

### Step 2: Add Polygon Amoy Testnet
1. Open MetaMask and click on the network dropdown (top center).
2. Select "Add Network" > "Add a network manually".
3. Enter the following details:
   - **Network Name**: Polygon Amoy
   - **New RPC URL**: https://rpc-amoy.polygon.technology
   - **Chain ID**: 80002
   - **Currency Symbol**: MATIC
   - **Block Explorer URL**: https://amoy.polygonscan.com/
4. Click "Save".

### Step 3: Get Test USDT
1. Switch to the Polygon Amoy network in MetaMask.
2. Visit a faucet to get free test USDT (e.g., [Polygon Faucet](https://faucet.polygon.technology/) or other testnet faucets).
3. Request test MATIC (native token) and USDT to your MetaMask address.

### Step 4: Connect Wallet to the Platform
1. In the creator or viewer dashboard, look for a "Connect Wallet" button (typically in the profile or payment sections).
2. Click it and approve the connection in MetaMask.
3. Ensure your USDT address is linked to your user profile for withdrawals.

### Step 5: Testing Transactions
- **Withdrawals**: As a creator, request a withdrawal to your connected USDT address.
- **Payments**: Viewers can tip creators using USDT.
- Monitor transactions on [PolygonScan Amoy](https://amoy.polygonscan.com/) using the transaction hash provided.

**Note**: Always use testnet for development. For mainnet, switch to Polygon Mainnet and use real funds cautiously.

## Configuration
- Access the Admin Dashboard to configure platform settings, creator requirements, payment settings, and more.
- Refer to `DOCUMENTATION.md` for detailed configuration options.

## Troubleshooting
- If you encounter issues, check the `DOCUMENTATION.md` file for troubleshooting guides.
- Common issues include database connection failures, blockchain signer errors, and USSD integration problems.
- Enable debug mode in `config/env.php` for detailed error logging.

## Support
For more detailed information, refer to `DOCUMENTATION.md`. If you need further assistance, check the project repository or contact the development team.

---

**Version**: 1.0  
**Last Updated**: December 2024  
**Compatibility**: PHP 8.0+, MySQL 8.0+, Node.js 16+
