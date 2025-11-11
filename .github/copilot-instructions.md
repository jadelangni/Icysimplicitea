# Icy's Simplicitea POS System - Copilot Instructions

This is a Laravel-based Point of Sale (POS) system for Icy's Simplicitea milk tea business with multiple branches in Oslob, Santander Poblacion, and Looc.

## Project Structure
- **Framework**: Laravel 10+ with PHP 8.1+
- **Frontend**: Tailwind CSS for styling
- **Database**: MySQL for data storage
- **Features**: Sales transactions, inventory management, reporting, multi-branch support

## Key Components
- Sales transaction processing with receipt generation
- Real-time inventory tracking across branches
- User role management (Admin and Employee)
- Automated business reporting (daily, weekly, monthly)
- Multi-branch coordination and management

## Development Guidelines
- Follow Laravel best practices and conventions
- Use Eloquent ORM for database operations
- Implement proper authentication and authorization
- Design responsive UI with Tailwind CSS
- Write clean, maintainable code with proper documentation
- Use proper validation for all forms and inputs
- Implement proper error handling and logging

## Database Structure
- Users (Admin and Employee)
- Branches (multiple locations)
- Products (milk tea and other items)
- Categories (product categorization)
- Inventory (stock tracking per branch)
- Sales (transaction records)
- Sales Items (individual items in transactions)
- Reports (generated business insights)

## Security Considerations
- Implement proper user authentication
- Role-based access control
- Input validation and sanitization
- Protection against common web vulnerabilities
- Secure handling of financial data