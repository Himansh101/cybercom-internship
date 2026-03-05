-- CREATE DATABASE e_commerce;
-- USE e_commerce;

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(128) NOT NULL
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL DEFAULT (CURRENT_DATE)
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
INSERT INTO products (product_name) VALUES 
('Laptop'), ('Wireless Mouse'), ('Mechanical Keyboard'), ('Monitor');

INSERT INTO orders (order_date) VALUES 
(CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE),
(CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE),
(CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE), (CURRENT_DATE);

INSERT INTO order_items (order_id, product_id) VALUES
(1, 1), (1, 2), (2, 1), (2, 2), (3, 1), (3, 2), (4, 1), (4, 2),
(5, 1), (5, 2), (6, 1), (6, 2), (7, 1), (7, 2), (8, 1), (8, 2),
(9, 1), (9, 2), (10, 1), (10, 2), (11, 1), (11, 2), (12, 1), (12, 2),
(13, 1), (13, 3),
(14, 4),
(15, 2), (15, 3), (15, 2);

-- =========================================================================
-- 3. QUERY: FREQUENTLY PURCHASED TOGETHER (> 10 TIMES)
-- =========================================================================
WITH DistinctOrderItems AS (
    SELECT DISTINCT order_id, product_id
    FROM order_items
),
PairFrequencies AS (
    SELECT 
        d1.product_id AS p1_id,
        d2.product_id AS p2_id,
        COUNT(d1.order_id) AS times_bought_together
    FROM DistinctOrderItems d1
    INNER JOIN DistinctOrderItems d2 
        ON d1.order_id = d2.order_id AND d1.product_id < d2.product_id
    GROUP BY d1.product_id, d2.product_id
),
TotalOrders AS (
    SELECT COUNT(order_id) AS total_orders_count FROM orders
)
SELECT 
    p1.product_name AS product_1,
    p2.product_name AS product_2,
    pf.times_bought_together,
    -- MySQL implicit conversion handles the division cleanly
    ROUND((pf.times_bought_together / t.total_orders_count) * 100, 2) AS pct_of_total_orders
FROM PairFrequencies pf
CROSS JOIN TotalOrders t
INNER JOIN products p1 ON pf.p1_id = p1.product_id
INNER JOIN products p2 ON pf.p2_id = p2.product_id
WHERE pf.times_bought_together > 10
ORDER BY pf.times_bought_together DESC;

