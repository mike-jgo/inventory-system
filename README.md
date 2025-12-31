# Inventory Management System

A modern, full-featured inventory management system built with **Laravel 12**, **Vue 3**, **Inertia.js**, and **TailwindCSS**. This application provides comprehensive inventory tracking, order management (POS), user roles and permissions, and detailed activity logging.

## Features

### Core Functionality

- **Item Management** - Create, read, update, and delete inventory items with categories and pricing
- **Inventory Tracking** - Real-time inventory levels with automated quantity updates
- **Point of Sale (POS)** - Complete order management system with order creation and tracking
- **Activity Log** - Comprehensive audit trail of all inventory actions with user tracking
- **Category Management** - Organize items with customizable categories
- **User Management** - Multi-user support with role-based access control

### Advanced Features

- **Role-Based Permissions** - Spatie Laravel Permission integration with superadmin and user roles
- **Order Management** - Complete order lifecycle with cancellation options (wastage vs. restocking)
- **Dashboard Analytics** - Recent orders and inventory insights
- **Activity Remarks** - Add notes and comments to activity log entries
- **Flash Messages** - User-friendly notifications for all CRUD operations
- **Search & Filter** - Powerful search and filtering capabilities across all modules

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite (or MySQL/PostgreSQL if preferred)

## Installation

### Quick Setup

Run the automated setup script:

```bash
composer run setup
```

This will:

1. Install PHP dependencies
2. Copy `.env.example` to `.env`
3. Generate application key
4. Run database migrations
5. Install npm dependencies
6. Build frontend assets

### Manual Setup

If you prefer manual installation:

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd inventory-system
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Set up environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Configure database**

    The default configuration uses SQLite. The database file will be created automatically at `database/database.sqlite`.

    To use MySQL/PostgreSQL, update `.env`:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=inventory_system
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Run migrations**

    ```bash
    php artisan migrate
    ```

6. **Seed initial data (optional)**

    ```bash
    php artisan db:seed
    ```

7. **Install frontend dependencies**

    ```bash
    npm install
    ```

8. **Build assets**
    ```bash
    npm run build
    ```

## Running the Application

### Production Build

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Project Structure

```
inventory-system/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── ActivityLogController.php    # Activity log management
│   │       ├── CategoryController.php       # Category CRUD
│   │       ├── DashboardController.php      # Dashboard data
│   │       ├── InventoryController.php      # Inventory management
│   │       ├── ItemController.php           # Item CRUD
│   │       ├── OrderController.php          # Order/POS management
│   │       ├── UserController.php           # User management
│   │       └── Auth/                        # Authentication controllers
│   └── Models/                               # Eloquent models
├── database/
│   ├── migrations/                           # Database migrations
│   └── seeders/                              # Database seeders
├── resources/
│   ├── js/
│   │   ├── components/                       # Reusable Vue components
│   │   │   ├── DataTable.vue                # Generic data table
│   │   │   ├── NavDropdown.vue              # Navigation dropdown
│   │   │   └── ViewDetailsModal.vue         # Details modal
│   │   ├── pages/                            # Inertia page components
│   │   │   ├── ActivityLog/
│   │   │   ├── Auth/
│   │   │   ├── Categories/
│   │   │   ├── Dashboard/
│   │   │   ├── Inventory/
│   │   │   ├── ItemList/
│   │   │   ├── Orders/
│   │   │   └── Users/
│   │   └── layouts/                          # Layout components
│   └── css/                                  # Stylesheets
├── routes/
│   └── web.php                               # Application routes
└── tests/                                    # Pest test files
```

## Key Features Explained

### Item Management

- Create items with name, category, quantity, and price
- Update item details and quantities
- Delete items (with activity logging)
- Categorize items for better organization

### Order System (POS)

- Create new orders with multiple items
- Track order status (pending, completed, cancelled)
- Cancel orders with two options:
    - **Wastage**: Item quantities are NOT restored
    - **Restocking**: Item quantities are restored to inventory
- Complete orders to finalize transactions
- Superadmins can edit cancelled orders

### Inventory Tracking

- Centralized inventory management (superadmin only)
- Real-time quantity updates
- Category-based organization
- Audit trail for all changes

### Activity Log

- Comprehensive logging of all actions
- User attribution for every change
- View details of what changed (old vs. new values)
- Add remarks/notes to activities
- Filter by date, user, and action type

### User & Permission Management

- Spatie Permission integration
- Role-based access control (superadmin vs. regular users)
- User creation, editing, and deletion (superadmin only)
- Role and permission assignment

## UI Components

The application uses a custom component library built with:

- **Reka UI** - Accessible, unstyled components
- **Class Variance Authority** - Type-safe component variants
- **TailwindCSS** - Utility-first styling
- **Lucide Icons** - Beautiful, consistent icons

## Configuration

### Database Configuration

The application supports SQLite, MySQL (default), and PostgreSQL. Update the database section in `.env` to switch databases.

### Key Steps:

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build`
4. Set up cron for Laravel scheduler
5. Configure queue worker as a supervisor process
6. Set proper file permissions on `storage/` and `bootstrap/cache/`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
