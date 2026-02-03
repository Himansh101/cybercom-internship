# EasyCart - Modern PHP E-commerce Platform

A premium, responsive e-commerce web application featuring a clean architecture, dynamic filtering, and a seamless shopping experience.

## 🚀 Features
- **Product Discovery**: Advanced filtering by category, brand, and price range with AJAX updates.
- **Detailed Product Views**: High-quality imagery, stock status, and shipping information.
- **Shopping Cart**: Real-time quantity adjustments and persistence.
- **Seamless Checkout**: Address validation, dynamic shipping calculation, and coupon support.
- **User Authentication**: Secure login and signup with session persistence.
- **Order Tracking**: View recent order history.

## 🛠️ Tech Stack
- **Frontend**: Vanilla HTML5, Vanilla CSS3 (Custom Properties, Flexbox, Grid), Remix Icon.
- **Backend**: Native PHP 8.x (Separated Logic & Views).
- **Data Management**: JSON-based user/cart persistence & hardcoded product catalog.

## 📂 Project Structure

The project follows a clean **Controller-View** pattern for better maintainability.

```text
easyCart/
├── assets/                 # Publicly accessible assets
│   ├── images/             # Product and UI images
│   ├── styles/             # Modular CSS files
│   └── js/                 # Client-side logic (AJAX, Validations)
├── src/                    # Core source code
│   ├── Controllers/        # Business logic & Request handling
│   │   ├── *.controller.php # Page-specific logic
│   │   └── *.handler.php    # AJAX/Action handlers
│   ├── Views/              # HTML Templates (Views)
│   ├── Partials/           # Reusable UI components (Header, Footer)
│   ├── Utils/              # Helper functions (Shipping, Coupons)
│   └── init.php            # Core initialization (Session, Data loading)
├── data.php                # Product catalog (Model-like data)
├── index.php               # Entry points (Routing to Controllers)
├── plp.php
├── pdp.php
├── cart.php
├── checkout.php
└── ...
```

## ⚙️ Setup Instructions
1. Clone the repository into your web server directory (e.g., `xampp/htdocs`).
2. Ensure you have a PHP environment (PHP 8.0+ recommended).
3. Access the project via `http://localhost/cybercom-internship/easyCart/`.

## 🧑‍💻 Refactoring Highlights
- **Separation of Concerns**: Logic and View are separated into `controller.php` and `view.php`.
- **Naming Conventions**: Adopts dot notation for handler files (e.g., `cart.handler.php`).
- **Asset Organization**: Centralized all CSS, JS, and Images under the `assets/` directory.
- **Shared Infrastructure**: Introduced `init.php` to handle global requirements like sessions and data loading.

---
*Created as part of the Cybercom Internship program.*
