# PharmaPilot 💊

A comprehensive pharmacy/parapharmacy management system built with **Laravel 9** and **MySQL**. This application provides a complete solution for managing products, sales, customers, and inventory in a pharmacy setting.

## Features

- **Dashboard** — Overview with key metrics and statistics
- **Product & Inventory Management** — Full CRUD with barcode support, batch tracking, and low-stock alerts
- **Point of Sale (POS)** — Complete POS system with barcode scanning, cart management, and receipt generation
- **Sales Management** — Track sales, generate receipts, and view sales history
- **Customer Management** — Customer profiles with loyalty points program
- **Promotions & Discounts** — Create and manage promotional offers and discount codes
- **Supplier Management** — Track suppliers and purchase orders
- **User Management** — Role-based access control (Admin/User), activity logging
- **Notifications** — In-app notification system
- **Reports** — Generate business reports and analytics
- **Multi-language Support** — Available in English, French, and Arabic

## Tech Stack

- **Backend:** PHP 8.0+ / Laravel 9
- **Database:** MySQL (via XAMPP/phpMyAdmin)
- **Frontend:** Blade templates, Bootstrap 5, Chart.js
- **Authentication:** Laravel UI

## Requirements

- PHP >= 8.0
- Composer
- MySQL >= 5.7
- XAMPP (or equivalent local server)
- Node.js & npm (for frontend assets, optional)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/pharma-pilot.git
   cd pharma-pilot
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Create the database**
   
   Open phpMyAdmin and create a database named `parapharma`, or run:
   ```sql
   CREATE DATABASE parapharma;
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

7. **Access the application**
   
   Open your browser and navigate to `http://localhost:8000`

## Default Credentials

After running seeders, you can log in with:

| Role  | Email             | Password   |
|-------|-------------------|------------|
| Admin | admin@pharma.com  | password   |

> ⚠️ **Note:** Change default passwords after first login.

## Project Structure

```
pharma-pilot/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Exceptions/       # Exception handlers
│   ├── Http/
│   │   ├── Controllers/  # Request handlers
│   │   └── Middleware/   # HTTP middleware
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization policies
│   ├── Providers/        # Service providers
│   └── Services/         # Business logic services
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── lang/                 # Language files (en, fr, ar)
├── public/               # Public assets (CSS, JS)
├── resources/
│   ├── css/              # Source CSS
│   ├── js/               # Source JS
│   ├── lang/             # Additional language files
│   └── views/            # Blade templates
├── routes/               # Route definitions
├── storage/              # Logs, cache, uploads
└── tests/                # Test files
```

## License

This project was developed as part of an internship. All rights reserved.
