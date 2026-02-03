# EasyCart - Modern PHP E-commerce Platform

A professional, responsive e-commerce web application featuring a modular **Controller-View** architecture, **PostgreSQL** persistence, and a seamless shopping experience.

## 🚀 Key Features
- **Product Discovery**: Advanced filtering by category, brand, price range, and stock status.
- **Stock Synchronization**: Real-time stock decrementing and automatic status/badge updates.
- **Persistent Cart & Checkout**: Carts, shipping choices, and coupon codes are persisted in the database, ensuring a consistent experience across sessions.
- **Clean URLs**: Professional, extension-less routing (e.g., `/cart`) with secure `.php` blockage via Apache `.htaccess`.
- **Premium UI**: Modern, glassmorphic design language with real-time feedback and state-of-the-art CSS animations.
- **Secure Auth**: Modular login/signup system with guest-to-user cart merging.

## 🛠️ Tech Stack
- **Frontend**: HTML5, Vanilla CSS3 (Custom Properties, Glassmorphism, Animations), Remix Icon, SweetAlert2.
- **Backend**: Native PHP 8.x (Modular Controller-View pattern, PDO).
- **Database**: PostgreSQL (Relational schema for Catalog, Customers, Carts, and Orders).

## 📂 Project Structure

```text
easyCart/
├── assets/                 # Frontend Assets
│   ├── styles/             # Modular CSS (plp.css, pdp.css, etc.)
│   ├── js/                 # Client-side logic & AJAX controllers
│   └── images/             # Product catalog images
├── src/                    # Core Application Logic
│   ├── Controllers/        # Business logic & Route handlers
│   ├── Views/              # Clean HTML/PHP templates
│   ├── Partials/           # Reusable Header/Footer components
│   ├── Models/             # DB Schema and Migration tools
│   ├── Utils/              # Domain utilities (shipping, coupon, cartsync)
│   ├── config/             # Database configuration
│   └── init.php            # Central system initialization
├── .htaccess               # URL Rewrite and Security rules
├── index / plp / pdp       # Clean Entry Points (Routed via .htaccess)
├── cart / checkout / orders
└── logout / login / signup
```

## ⚙️ Setup Instructions
1. Clone the repository and place it in your Apache/PHP server (e.g., `xampp/htdocs`).
2. **PostgreSQL Setup**:
   - Create a database named `easyCart`.
   - Run the migration script in `src/models/migrate.php` to populate the schema and initial data.
3. **Database Config**: Update `src/config/database.php` with your PostgreSQL credentials.
4. **Apache Config**: Ensure `mod_rewrite` is enabled to support Clean URLs.
5. Access via `http://localhost/cybercom-internship/easyCart/`.

## 🧑‍💻 Architectural Excellence
- **Database Persistence**: Moved from volatile JSON files to a robust PostgreSQL relational database.
- **Metadata Management**: Dedicated `sales_cart_metadata` table for persisting checkout state without bloating the session.
- **Fail-safe Logic**: Dual-layer stock checking (numeric `stock_count` + `in_stock` attribute) for 100% accurate listings.
- **Controller-View Pattern**: 100% separation of business logic from presentation.

---
*Developed as part of the Cybercom Internship program.*
