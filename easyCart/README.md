# EasyCart - Developer Documentation

**EasyCart** is a modern, full-stack e-commerce platform built with native PHP and PostgreSQL. This documentation provides a complete technical overview, describing every function, workflow, and database element to help developers understand, maintain, and extend the system.

---

## 📚 Table of Contents
1.  [Application Workflows](#application-workflows)
2.  [Database Schema Reference](#database-schema-reference)
3.  [Project Architecture](#project-architecture)
4.  [Setup & Configuration](#setup--configuration)

---

## <a id="application-workflows"></a>1. Application Workflows

### 🛍️ The Shopping Journey
1.  **Product Discovery (PLP)**:
    - Users browse `/plp` (Product Listing Page).
    - **Filtering**: Sidebar filters (Category, Brand, Price) trigger AJAX requests to `plp.controller.php`, which returns a filtered HTML fragment.
    - **Stock Logic**: Out-of-stock items are visually dimmed and sorted to the bottom.
2.  **Product Details (PDP)**:
    - `/pdp?id=X` fetches full product metadata, images, and attributes.
    - **Add to Cart**: Clicking "Add to Cart" sends an AJAX POST to `cart.handler.php`, creating a cart session if one doesn't exist.

### 🛒 Cart & Checkout System
1.  **Cart Management**:
    - **Guest vs User**: Carts are stored in the `sales_cart` table. If a guest logs in (`login.controller.php`), their guest cart items are merged into their persistent user cart.
    - **Persistence**: Cart IDs are stored in `$_SESSION`. The database acts as the single source of truth.
2.  **Checkout Process**:
    - **Validation**: `/checkout` access is restricted to logged-in users with non-empty carts (`checkout.controller.php`).
    - **Address Entry**:
        - **Shipping**: User enters shipping details.
        - **Billing**: A "Same as shipping" toggle controls the visibility of a separate Billing Address form.
    - **Shipping Calculation**:
        - Logic in `shipping.utils.php` checks for "Freight" items (heavy goods) or high cart value (> ₹300).
        - **Freight**: Forces "White Glove" shipping.
        - **Standard**: Offers "Standard" or "Express".
    - **Payment**:
        - **Stripe**: Uses `stripe.js` to mount a secure card element. A `PaymentIntent` is created on the server (`checkout.handler.php`) via the Stripe API.
        - **COD**: Simple flag setting.
3.  **Order Placement**:
    - On confirmation, `checkout.handler.php` performs a transaction:
        1.  Verifies Stock one last time.
        2.  Creates `sales_order` record.
        3.  Moves items from `sales_cart_product` to `sales_order_item`.
        4.  Decrements `catalog_product_entity.stock_count`.
        5.  Deactivates the cart (`is_active = FALSE`).

### � Order Management
-   **Dashboard**: `/dashboard` visualizes spending using Chart.js, fetching aggregated data via `dashboard.handler.php`.
-   **Order History**: `/orders` lists all past purchases. Clicking "View Details" opens an AJAX modal with line-item specifics.

---

## <a id="database-schema-reference"></a>2. Database Schema Reference

The database consists of 4 main domains: **Catalog**, **Sales (Cart/Order)**, **Customers**, and **Configuration**.

### 👤 Customer Domain
**`customer_entity`**
Stores registered user accounts.
| Column | Type | Description |
| :--- | :--- | :--- |
| `entity_id` | SERIAL (PK) | Unique User ID |
| `name` | VARCHAR(255) | Full Name |
| `email` | VARCHAR(255) | Unique Email Address |
| `mobile` | VARCHAR(20) | Contact Number |
| `password` | VARCHAR(255) | Hashed Password (bcrypt) |
| `created_at` | TIMESTAMP | Registration Date |

### 📦 Catalog Domain
**`catalog_product_entity`**
The core product table.
| Column | Type | Description |
| :--- | :--- | :--- |
| `entity_id` | SERIAL (PK) | Unique Product ID |
| `sku` | VARCHAR(100) | Stock Keeping Unit (Unique) |
| `name` | VARCHAR(255) | Product Name |
| `price` | DECIMAL(12,2) | Unit Price |
| `stock_count` | INT | Current Physical Stock |

**`catalog_product_attribute`**
EAV table for flexible product data (Color, Size, Description, Shipping Type).
| Column | Type | Description |
| :--- | :--- | :--- |
| `entity_id` | INT (FK) | Links to product |
| `attribute_key` | VARCHAR | e.g., 'shipping_type', 'in_stock' |
| `attribute_value` | TEXT | Value of the attribute |

**`catalog_category_entity`** & **`catalog_brand_entity`**
Standard lookup tables for Categories and Brands.

### 🛒 Sales Domain (Cart)
**`sales_cart`**
Persistent cart sessions.
| Column | Type | Description |
| :--- | :--- | :--- |
| `cart_id` | SERIAL (PK) | Unique Cart ID |
| `user_id` | INT (FK) | Owner User ID (Null for guests) |
| `is_active` | BOOLEAN | `TRUE` = Open, `FALSE` = Converted to Order |

**`sales_cart_product`**
Items currently in a cart.
| Column | Type | Description |
| :--- | :--- | :--- |
| `cart_id` | INT (FK) | Links to Cart |
| `product_id` | INT (FK) | Links to Product |
| `quantity` | INT | Quantity selected |

### 📄 Sales Domain (Orders)
**`sales_order`**
Confirmed orders.
| Column | Type | Description |
| :--- | :--- | :--- |
| `order_id` | SERIAL (PK) | Internal ID |
| `order_number` | VARCHAR | Customer-facing ID (e.g., ORD-X8A2) |
| `user_id` | INT (FK) | Customer link |
| `final_amount` | DECIMAL | Total paid (Subtotal - Discount + Ship + Tax) |
| `status` | VARCHAR | 'placed', 'shipped', 'delivered' |
| `payment_method` | VARCHAR | 'stripe' or 'cod' |
| `transaction_id`| VARCHAR | Stripe Payment Intent ID |
| `payment_status`| VARCHAR | 'paid', 'pending' |

**`sales_order_address`**
Snapshots of addresses used for an order.
| Column | Type | Description |
| :--- | :--- | :--- |
| `order_id` | INT (FK) | Links to Order |
| `address_type` | VARCHAR | 'shipping' or 'billing' |
| `full_name` | VARCHAR | Recipient Name |
| `street_address`| TEXT | Full address text |
| `city` | VARCHAR | City |
| `pincode` | VARCHAR | Postal Code |

### ⚙️ Configuration Domain
**`sales_shipping_method`**
Dynamic shipping rules.
| Column | Type | Description |
| :--- | :--- | :--- |
| `code` | VARCHAR (PK) | 'standard', 'express', 'freight' |
| `type` | VARCHAR | Calculation logic ('flat', 'percentage') |
| `base_cost` | DECIMAL | Base fee |
| `is_active` | BOOLEAN | Enabled/Disabled status |

**`sales_coupon`**
Discount codes.
| Column | Type | Description |
| :--- | :--- | :--- |
| `code` | VARCHAR (PK) | The coupon code (e.g., 'SAVE10') |
| `discount_percent`| DECIMAL | Percentage off |
| `is_active` | BOOLEAN | Enabled/Disabled status |

---

## <a id="project-architecture"></a>3. Project Architecture

The project follows a strict **MVC (Model-View-Controller)** pattern without using a framework.

-   **`src/Controllers/`**: Handle incoming requests, perform business logic, and verify permissions.
-   **`src/Views/`**: Pure HTML/PHP templates that render data passed by controllers.
-   **`src/Utils/`**: Helper functions (`cartsync`, `shipping`, `stripe`) to keep controllers thin.
-   **`src/init.php`**: The bootstrap file. It starts the session, connects to the DB, and loads Environment Variables (`.env`).

---

## <a id="setup--configuration"></a>4. Setup & Configuration

1.  **Dependencies**: Run `composer install` to load `vlucas/phpdotenv`.
2.  **Environment**: Create a `.env` file in the root:
    ```ini
    STRIPE_SECRET_KEY=sk_test_...
    STRIPE_PUBLISHABLE_KEY=pk_test_...
    ```
3.  **Database**:
    - Update `src/config/database.php` with your PostgreSQL credentials.
    - Import the schema from `src/models/schema.sql`.

---
*Maintained by the EasyCart Development Team.*
