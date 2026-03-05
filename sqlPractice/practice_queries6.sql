-- CREATE DATABASE sales;
-- USE sales;

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(64) NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(128) NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    status VARCHAR(32) NOT NULL CHECK (status IN ('Completed', 'Pending', 'Cancelled'))
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10, 2) NOT NULL,
    
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- =========================================================================
-- 2. DATA INSERTION
-- =========================================================================
INSERT INTO categories (category_name) VALUES ('Electronics'), ('Home Goods'), ('Books');

INSERT INTO products (category_id, product_name, base_price) VALUES 
(1, 'Laptop', 1000.00), (1, 'Headphones', 200.00),
(2, 'Coffee Maker', 150.00), (2, 'Blender', 100.00),
(3, 'SQL Guide', 50.00);

INSERT INTO orders (order_date, status) VALUES 
('2025-01-15', 'Completed'),
('2025-02-20', 'Pending'),   
('2025-03-10', 'Cancelled'), 
('2025-04-05', 'Completed'), 
('2025-05-12', 'Completed'), 
('2025-06-25', 'Pending');   

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 1000.00), (1, 3, 2, 150.00),
(2, 2, 3, 200.00),                    
(3, 5, 4, 50.00),                     
(4, 1, 2, 1000.00),                   
(5, 4, 1, 100.00),                    
(6, 5, 2, 50.00);                     

-- =========================================================================
-- 3. QUERY: MULTI-DIMENSIONAL PIVOT REPORT BY CATEGORY & QUARTER
-- =========================================================================
WITH BaseSalesData AS (
    SELECT 
        c.category_name,
        YEAR(o.order_date) AS sales_year,
        QUARTER(o.order_date) AS sales_quarter,
        o.status,
        o.order_id,
        (oi.quantity * oi.unit_price) AS line_total
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id
),
QuarterlyAggregations AS (
    SELECT 
        category_name,
        sales_year,
        sales_quarter,
        COUNT(DISTINCT CASE WHEN status = 'Completed' THEN order_id END) AS completed_orders,
        COALESCE(SUM(CASE WHEN status = 'Completed' THEN line_total END), 0) AS completed_revenue,
        COUNT(DISTINCT CASE WHEN status = 'Pending' THEN order_id END) AS pending_orders,
        COALESCE(SUM(CASE WHEN status = 'Pending' THEN line_total END), 0) AS pending_revenue,
        COUNT(DISTINCT CASE WHEN status = 'Cancelled' THEN order_id END) AS cancelled_orders,
        COALESCE(SUM(CASE WHEN status = 'Cancelled' THEN line_total END), 0) AS cancelled_revenue,
        COALESCE(SUM(line_total), 0) AS total_gross_revenue
    FROM BaseSalesData
    WHERE sales_year IS NOT NULL 
    GROUP BY category_name, sales_year, sales_quarter
)
SELECT 
    category_name,
    sales_year,
    sales_quarter,
    completed_orders,
    completed_revenue,
    pending_orders,
    pending_revenue,
    cancelled_orders,
    cancelled_revenue,
    total_gross_revenue,
    -- Window function works natively in MySQL 8.0+
    SUM(total_gross_revenue) OVER (
        PARTITION BY category_name, sales_year 
        ORDER BY sales_quarter
    ) AS ytd_category_revenue
FROM QuarterlyAggregations
ORDER BY category_name, sales_year, sales_quarter;