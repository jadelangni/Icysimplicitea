# Icy's Simplicitea POS System

A Point of Sale (POS) system built with Laravel and Tailwind CSS for Icy's Simplicitea milk tea business with multiple branches in Oslob, Santander Poblacion, and Looc.

## Features

### Core Functionality
- **Sales Transaction Processing**: Complete POS interface for processing customer orders
- **Real-time Inventory Management**: Track stock levels across all branches
- **Multi-branch Support**: Manage operations across multiple locations
- **User Role Management**: Two user roles - Admin and Cashier
- **Receipt Generation**: Automatic receipt printing for all transactions
- **Business Reporting**: Daily, weekly, and monthly sales and inventory reports

### User Roles & Permissions
- **Admin**: Full system access including all management features, products, inventory, reports, and user management
- **Cashier**: Access to POS system and basic transaction processing

### Branch Management
- **Oslob Main**: Main branch location
- **Santander Poblacion**: Secondary branch
- **Looc Branch**: Third branch location

## Technology Stack

- **Backend**: Laravel 12.x with PHP 8.1+
- **Frontend**: Tailwind CSS for responsive design
- **Database**: SQLite (development) / MySQL (production)
- **Authentication**: Laravel Breeze
- **Build Tool**: Vite

## Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and npm
- SQLite or MySQL database

### Quick Start

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Build Assets**
   ```bash
   npm run build
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

### Default Login Credentials

#### Admin Account
- **Email**: admin@simplicitea.com
- **Password**: password
- **Role**: Admin
- **Branch**: Oslob Main

#### Cashier Account
- **Email**: cashier1@simplicitea.com
- **Password**: password
- **Role**: Cashier
- **Branch**: Oslob Main

## Development

### Available Commands

```bash
# Development server
php artisan serve

# Database operations
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Asset compilation
npm run dev    # Development with hot reload
npm run build  # Production build
```

## License

This project is proprietary software developed specifically for Icy's Simplicitea business operations.
