# Kabaka Platform Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Blockchain Integration](#blockchain-integration)
4. [User Roles & Features](#user-roles--features)
5. [Database Schema](#database-schema)
6. [API Endpoints](#api-endpoints)
7. [Installation & Setup](#installation--setup)
8. [Configuration](#configuration)
9. [USSD Integration](#ussd-integration)
10. [Troubleshooting](#troubleshooting)

## System Overview

Kabaka is a content monetization platform that allows creators to earn money through views, tips, and engagement. The platform integrates with blockchain technology for secure payments and includes USSD functionality for mobile access.

### Key Features
- **Content Management**: Upload, moderate, and display multimedia content
- **Monetization**: Earn money through views, tips, and engagement
- **Blockchain Payments**: Secure USDT transactions via Polygon network
- **USSD Access**: Mobile balance checking via Africa's Talking
- **Admin Dashboard**: Comprehensive management tools
- **User Management**: Creator and viewer roles with different permissions

## Architecture

### Frontend
- **Bootstrap 5**: Responsive UI framework
- **Vanilla JavaScript**: Client-side functionality
- **CSS3**: Custom styling with glassmorphism effects
- **Bootstrap Icons**: Icon library

### Backend
- **PHP 8.0+**: Server-side logic
- **MySQL**: Database management
- **PDO**: Database abstraction layer
- **Session Management**: User authentication and authorization

### Blockchain Stack
- **Polygon Amoy Testnet**: Ethereum-compatible blockchain
- **Node.js**: Blockchain signer service
- **ethers.js**: Ethereum library for blockchain interactions
- **Smart Contracts**: Automated payment processing

## Blockchain Integration

### Overview
The platform uses Polygon Amoy testnet for blockchain transactions, providing fast and low-cost USDT transfers.

### Components

#### 1. Blockchain Signer Service (`blockchain/signer/`)
**Purpose**: Handles all blockchain transactions securely
**Technology**: Node.js with ethers.js
**Key Files**:
- `index.js`: Main signer service
- `.env`: Configuration (RPC URL, private key, contract address)

**How it works**:
1. Receives withdrawal requests from PHP backend
2. Validates transaction parameters
3. Signs and broadcasts transactions to Polygon network
4. Returns transaction hash and status
5. Updates local database with transaction details

#### 2. Smart Contract Integration
**Contract Address**: `0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F`
**Network**: Polygon Amoy Testnet
**Functionality**:
- USDT token transfers
- Transaction validation
- Gas optimization

#### 3. Transaction Flow
```
Creator Request → PHP Backend → Signer Service → Polygon Network → Database Update
```

**Detailed Process**:
1. **Creator initiates withdrawal** in dashboard
2. **PHP validates** eligibility and amount
3. **Signer service** receives request via HTTP
4. **Blockchain transaction** is created and signed
5. **Transaction broadcast** to Polygon network
6. **Receipt stored** in `blockchain_reciept` table
7. **User notified** of transaction status

#### 4. Database Tables
- **`wallets`**: User balance tracking (balance_cents, pending_cents)
- **`payments`**: Transaction history and status
- **`blockchain_reciept`**: On-chain transaction details
- **`users`**: USDT address storage

#### 5. Security Features
- **Private Key Management**: Stored securely in signer service
- **Transaction Validation**: Multiple checks before processing
- **Error Handling**: Comprehensive logging and rollback
- **Rate Limiting**: Prevents abuse

### USSD Integration

#### Africa's Talking USSD
**Purpose**: Mobile balance checking without internet
**Endpoint**: `/kabaka/public/api/ussd_wallet.php`
**Flow**:
1. User dials USSD code
2. Selects "Check balance" option
3. Enters USDT address
4. Receives balance information

**Security Considerations**:
- Public USDT address lookup (consider PIN protection for production)
- Rate limiting recommended
- Audit logging for suspicious activity

## User Roles & Features

### Admin
- **Dashboard**: Comprehensive management interface
- **User Management**: Create, update, ban, delete users
- **Content Moderation**: Approve, reject, flag content
- **Settings Management**: Configure platform requirements
- **Blockchain Monitoring**: View transaction receipts
- **Impact Analysis**: Analyze requirement changes on users

### Creator
- **Content Upload**: Multimedia content management
- **Earnings Dashboard**: View balance, pending amounts, transaction history
- **Withdrawal System**: Request USDT payments
- **Profile Management**: Update display name, USDT address
- **Eligibility Tracking**: Monitor verification and monetization status

### Viewer
- **Content Discovery**: Browse featured, trending, recent content
- **Engagement**: Like, comment, follow creators
- **Search Functionality**: Find specific content
- **Responsive Design**: Works on all device sizes

## Database Schema

### Core Tables

#### `users`
```sql
- id (PRIMARY KEY)
- email (UNIQUE)
- password_hash
- display_name
- role (viewer/creator/admin)
- usdt_address
- is_verified (0/1)
- monetization_enabled (0/1)
- status (active/banned/inactive/pending/suspended/rejected)
- created_at
```

#### `wallets`
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- balance_cents (BIGINT)
- pending_cents (BIGINT)
- updated_at
- created_at
```

#### `content`
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- title
- description
- file_path
- file_type
- category
- status (pending/approved/rejected/flagged/removed)
- created_at
```

#### `payments`
```sql
- id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- amount_cents (BIGINT)
- currency (USD/USDT)
- source (tip/payout)
- status (pending/completed/failed)
- tx_id (transaction hash)
- created_at
```

#### `blockchain_reciept`
```sql
- id (PRIMARY KEY)
- payment_id (FOREIGN KEY)
- payment_id_hash
- chain (polygon-amoy)
- contract_address
- tx_hash
- payer_address
- amount_wei
- onchain_status
- block_number
- created_at
```

### Configuration Tables
- `creator_requirements`: Verification criteria
- `payment_settings`: Withdrawal limits and fees
- `monetization_settings`: Earnings configuration
- `platform_settings`: General platform settings
- `moderation_settings`: Content moderation rules

## API Endpoints

### Authentication (`/api/auth.php`)
- `POST login`: User authentication
- `POST logout`: Session termination
- `POST register`: New user registration

### Payment (`/api/payment.php`)
- `GET wallet`: User wallet information
- `GET eligibility`: Verification status
- `GET transactions`: Payment history
- `POST withdrawal`: Request USDT withdrawal
- `POST verification`: Request verification
- `POST monetization`: Request monetization

### Admin (`/api/admin.php`)
- `GET settings`: Platform configuration
- `POST save_settings`: Update settings
- `GET all_users`: User management
- `POST update_user`: Modify user data
- `POST bulk_unverify`: Mass unverification
- `GET blockchain_receipts`: Transaction monitoring
- `GET requirement_impact`: Impact analysis

### USSD (`/api/ussd_wallet.php`)
- `POST`: Africa's Talking USSD callback
- Menu-driven balance checking
- USDT address lookup

MyPass1//mysecwallet123!!
MyPass2//mywallet123!!


## Installation & Setup

### Prerequisites
- **PHP 8.0+** with extensions:
  - PDO MySQL
  - JSON
  - cURL
  - OpenSSL
- **MySQL 8.0+**
- **Node.js 16+** (for blockchain signer)
- **Web Server** (Apache/Nginx)
- **Composer** (for dependencies)

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd kabaka
```

### Step 2: Database Setup
```sql
-- Create database
CREATE DATABASE kabaka_platform;

-- Import schema (if provided)
mysql -u username -p kabaka_platform < schema.sql
```

### Step 3: Environment Configuration
Create `.env` file in project root:
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

### Step 4: Blockchain Signer Setup
```bash
cd blockchain/signer
npm install
cp .env.example .env
# Edit .env with your configuration
node index.js
```

### Step 5: Web Server Configuration
**Apache (.htaccess)**:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 6: File Permissions
```bash
chmod 755 public/
chmod 644 public/*.php
chmod 755 uploads/
chmod 644 config/
```

## Configuration

### Platform Settings
Access via Admin Dashboard → Settings:

#### Creator Requirements
- Minimum content posts
- Minimum account age (days)
- Verification requirements

#### Payment Settings
- Minimum withdrawal amount
- Platform fee percentage
- Processing fees
- Auto-payouts toggle

#### Monetization Settings
- Payment per 1000 views
- Minimum followers for payment
- Minimum views for payment
- Monetization toggle

### Blockchain Configuration
**Signer Service** (`blockchain/signer/.env`):
```env
PORT=4001
RPC_URL=https://rpc-amoy.polygon.technology
PRIVATE_KEY=your_private_key_here
CONTRACT_ADDRESS=0x0E97b68A40Edf6200aeD77E2Bf999449F3E2c56F
AUTH_TOKEN=kabaka-secret-2024
```

**PHP Integration** (`config/env.php`):
- Database connection settings
- Session configuration
- Error reporting levels

## USSD Integration

### Africa's Talking Setup
1. **Create Account**: Register at Africa's Talking
2. **Create USSD Service**: Set up new USSD application
3. **Configure Callback**: 
   - URL: `https://your-domain.com/kabaka/public/api/ussd_wallet.php`
   - Method: POST
4. **Test**: Use AT simulator or real device

### USSD Flow
```
User dials *123# → AT sends POST to callback → PHP processes → Response sent to user
```

**Request Format**:
```
sessionId: unique_session_id
serviceCode: *123#
phoneNumber: +254XXXXXXXXX
text: user_input
```

**Response Format**:
```
CON message (continue session)
END message (end session)
```

### Security Considerations
- **Rate Limiting**: Implement per-phone limits
- **Input Validation**: Sanitize all user inputs
- **Error Handling**: Graceful failure responses
- **Logging**: Audit all USSD interactions

## Troubleshooting

### Common Issues

#### Blockchain Signer Not Starting
**Error**: `JsonRpcProvider failed to detect network`
**Solution**:
1. Check RPC URL in `.env`
2. Verify internet connection
3. Try alternative RPC endpoints:
   - `https://rpc-amoy.polygon.technology`
   - `https://polygon-amoy-bor-rpc.publicnode.com`

#### Database Connection Failed
**Error**: `PDO connection failed`
**Solution**:
1. Verify database credentials in `.env`
2. Check MySQL service status
3. Ensure database exists
4. Verify user permissions

#### USSD Not Responding
**Error**: No response from USSD endpoint
**Solution**:
1. Check ngrok tunnel status
2. Verify callback URL in AT dashboard
3. Test endpoint manually with curl
4. Check server logs for errors

#### Withdrawal Failures
**Error**: `Withdrawal amount exceeds available earnings`
**Solution**:
1. Check wallet balance calculation
2. Verify eligibility requirements
3. Check blockchain signer service
4. Review transaction logs

### Debug Mode
Enable debug logging in `config/env.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');
```

### Log Files
- **PHP Errors**: `logs/error.log`
- **Blockchain Transactions**: `blockchain/signer/logs/`
- **USSD Interactions**: Check server access logs

### Performance Optimization
- **Database Indexing**: Add indexes on frequently queried columns
- **Caching**: Implement Redis/Memcached for session data
- **CDN**: Use CDN for static assets
- **Database Optimization**: Regular maintenance and cleanup

## Security Best Practices

### Production Deployment
1. **HTTPS Only**: Force SSL/TLS encryption
2. **Environment Variables**: Never commit sensitive data
3. **Database Security**: Use strong passwords, limit access
4. **File Permissions**: Restrict access to sensitive files
5. **Regular Updates**: Keep all dependencies updated

### Blockchain Security
1. **Private Key Management**: Use hardware wallets for production
2. **Multi-signature**: Implement multi-sig for large transactions
3. **Rate Limiting**: Prevent transaction spam
4. **Audit Logging**: Track all blockchain interactions

### API Security
1. **Input Validation**: Sanitize all user inputs
2. **SQL Injection Prevention**: Use prepared statements
3. **CSRF Protection**: Implement CSRF tokens
4. **Rate Limiting**: Prevent API abuse

---

## Support & Maintenance

### Regular Maintenance Tasks
- **Database Cleanup**: Remove old logs and temporary data
- **Backup**: Regular database and file backups
- **Monitoring**: Check system health and performance
- **Updates**: Keep dependencies and security patches current

### Contact Information
For technical support or questions about this documentation, please refer to the project repository or contact the development team.

---

**Version**: 1.0  
**Last Updated**: December 2024  
**Compatibility**: PHP 8.0+, MySQL 8.0+, Node.js 16+
