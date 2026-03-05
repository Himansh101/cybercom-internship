-- CREATE DATABASE question_7;
-- USE question_7;


CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(128) NOT NULL
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(64) NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(128) NOT NULL,
    
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    
    CONSTRAINT fk_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- =========================================================================
-- 2. DATA INSERTION
-- =========================================================================
INSERT INTO categories (category_name) VALUES ('Electronics'), ('Clothing'), ('Groceries');
INSERT INTO products (category_id, product_name) VALUES (1, 'Smartphone'), (2, 'T-Shirt'), (3, 'Apples');

INSERT INTO customers (customer_name) VALUES ('Alice'), ('Bob'), ('Charlie');

INSERT INTO orders (customer_id) VALUES (1), (1), (1);
INSERT INTO order_items (order_id, product_id) VALUES (1, 1), (2, 2), (3, 3); 

INSERT INTO orders (customer_id) VALUES (2), (2);
INSERT INTO order_items (order_id, product_id) VALUES (4, 1), (5, 2);

-- =========================================================================
-- 3. QUERY: DOUBLE NOT EXISTS (Relational Division)
-- =========================================================================
SELECT 
    c.customer_id, 
    c.customer_name
FROM 
    customers c
WHERE 
    NOT EXISTS (
        SELECT 1 
        FROM categories cat
        WHERE 
            NOT EXISTS (
                SELECT 1
                FROM orders o
                INNER JOIN order_items oi ON o.order_id = oi.order_id
                INNER JOIN products p ON oi.product_id = p.product_id
                WHERE o.customer_id = c.customer_id 
                  AND p.category_id = cat.category_id
            )
    );