# EasyCart - Modern PHP E-commerce Platform

A premium, responsive e-commerce web application featuring a clean **Controller-View** architecture, dynamic filtering, and a seamless shopping experience.

## 🚀 Key Features
- **Product Discovery**: Advanced filtering by category, brand, and price range.
- **Stock Priority**: In-stock items are automatically prioritized at the top of the listings.
- **Premium Orders Page**: Real-time order persistence in `users.json` with a modern, glassmorphic UI overhaul.
- **Shopping Cart**: Real-time quantity adjustments and local/session persistence.
- **Dynamic Checkout**: Intelligent shipping calculations, coupon support, and field validations.
- **Secure Auth**: Dedicated login and signup modules with protected routes.

## 🛠️ Tech Stack
- **Frontend**: HTML5, Vanilla CSS3 (Custom Properties, Glassmorphism, Animations), Remix Icon, SweetAlert2.
- **Backend**: Native PHP 8.x (Modular Controller-View pattern).
- **Data**: JSON-based persistence for Users, Carts, and Order History.

## 📂 Project Structure

```text
easyCart/
├── assets/                 # Centralized Asset Store
│   ├── css/                # Refactored modular stylesheets
│   ├── js/                 # AJAX controllers and UI logic
│   └── images/             # Product catalog images
├── src/                    # Core Application Logic
│   ├── Controllers/        # Business logic & Route handlers
│   │   ├── *.controller.php # Page logic
│   │   └── *.handler.php    # AJAX action handlers
│   ├── Views/              # Clean HTML templates
│   ├── Partials/           # Reusable Header/Footer views
│   ├── Models/             # Data models (data.php)
│   ├── Utils/              # Utility classes (*.utils.php)
│   └── init.php            # Global Initialization (Sessions, DI)
├── index.php               # Clean Entry Points (Delegating to src/Controllers)
├── plp.php / pdp.php
├── cart.php / checkout.php
└── orders.php
```

## ⚙️ Setup Instructions
1. Clone the repository into your PHP environment (e.g., `xampp/htdocs`).
2. Ensure you have PHP 8.0+ enabled.
3. Access the project via `http://localhost/cybercom-internship/easyCart/`.

## 🧑‍💻 Refactoring Highlights
- **Clean Architecture**: Complete separation of business logic (`Controllers`) from presentation (`Views`).
- **Dot Notation**: Standardized naming for handlers (`cart.handler.php`) and utilities (`shipping.utils.php`).
- **AJAX Shims**: Uses root-level shims for AJAX stability while keeping logic safe in internal directories.
- **UI Excellence**: Transitioned to a premium design language using glassmorphism and modern CSS variables.

---
*Developed as part of the Cybercom Internship program.*
