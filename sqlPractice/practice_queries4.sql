-- CREATE DATABASE track_products;
-- USE track_products;
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(128) NOT NULL,
    category VARCHAR(64)
);

CREATE TABLE product_price_history (
    price_history_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    
    CONSTRAINT fk_product_price FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- =========================================================================
-- 2. DATA INSERTION
-- =========================================================================
INSERT INTO products (product_name, category) VALUES 
('Mechanical Keyboard', 'Electronics'),
('Gaming Mouse', 'Electronics'),
('Desk Mat', 'Accessories'),
('Monitor Stand', 'Accessories');

INSERT INTO product_price_history (product_id, price, effective_date) VALUES 
(1, 100.00, CURRENT_DATE() - INTERVAL 150 DAY),
(1, 120.00, CURRENT_DATE() - INTERVAL 100 DAY),
(1, 110.00, CURRENT_DATE() - INTERVAL 30 DAY),
(2, 0.00, CURRENT_DATE() - INTERVAL 120 DAY),
(2, 50.00, CURRENT_DATE() - INTERVAL 45 DAY),
(2, 45.00, CURRENT_DATE() - INTERVAL 10 DAY),
(3, 25.00, CURRENT_DATE() - INTERVAL 20 DAY),
(4, 40.00, CURRENT_DATE() - INTERVAL 200 DAY),
(4, 35.00, CURRENT_DATE() - INTERVAL 150 DAY);

-- =========================================================================
-- 3. QUERY: PRICE VOLATILITY AND PERCENTAGE CHANGE
-- =========================================================================
WITH PriceChanges AS (
    SELECT 
        h.product_id,
        p.product_name,
        h.price AS current_price,
        h.effective_date,
        LAG(h.price) OVER (
            PARTITION BY h.product_id 
            ORDER BY h.effective_date, h.price_history_id
        ) AS previous_price,
        LEAD(h.price) OVER (
            PARTITION BY h.product_id 
            ORDER BY h.effective_date, h.price_history_id
        ) AS next_price
    FROM product_price_history h
    INNER JOIN products p ON h.product_id = p.product_id
),
CalculatedVolatility AS (
    SELECT 
        product_id,
        product_name,
        effective_date,
        previous_price,
        current_price,
        next_price,
        CASE
            WHEN previous_price IS NULL THEN NULL 
            WHEN previous_price = 0 THEN NULL 
            ELSE ROUND(((current_price - previous_price) / previous_price) * 100, 2)
        END AS pct_change_from_previous
    FROM PriceChanges
),
RecentChangedProducts AS (
    SELECT DISTINCT product_id
    FROM product_price_history
    WHERE effective_date >= CURRENT_DATE() - INTERVAL 90 DAY
)
SELECT 
    v.product_id,
    v.product_name,
    v.effective_date,
    v.previous_price,
    v.current_price,
    v.next_price,
    v.pct_change_from_previous
FROM CalculatedVolatility v
INNER JOIN RecentChangedProducts rcp ON v.product_id = rcp.product_id
WHERE v.previous_price IS NOT NULL 
  AND v.current_price <> v.previous_price
ORDER BY v.product_id, v.effective_date DESC;