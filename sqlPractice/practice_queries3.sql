-- CREATE DATABASE e_commerce_system;
-- USE e_commerce_system;

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(64) NOT NULL,
    last_name VARCHAR(64) NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    total_amount DECIMAL(12, 2) DEFAULT 0.00,
    
    CONSTRAINT fk_customer_order FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL, 
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10, 2) NOT NULL,
    
    CONSTRAINT fk_order_item FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- =========================================================================
-- 2. DATA INSERTION (Including Edge Cases & Dynamic Dates)
-- =========================================================================
INSERT INTO customers (first_name, last_name, email) VALUES 
('Alice', 'Smith', 'alice@example.com'),
('Bob', 'Jones', 'bob@example.com'),
('Carol', 'Williams', 'carol@example.com'),
('David', 'Brown', 'david@example.com'),
('Eve', 'Davis', 'eve@example.com'); -- Edge case: Customer with no orders

-- Using CURRENT_DATE() intervals so the sample data is always relevant
INSERT INTO orders (customer_id, order_date, total_amount) VALUES 
-- Alice: High spender, multiple recent orders
(1, CURRENT_DATE() - INTERVAL 5 DAY, 500.00),
(1, CURRENT_DATE() - INTERVAL 10 DAY, 300.00),

-- Bob: Low spender, recent order
(2, CURRENT_DATE() - INTERVAL 2 DAY, 50.00),

-- Carol: High spender, but order is OUTSIDE the 30-day window (Edge Case)
(3, CURRENT_DATE() - INTERVAL 45 DAY, 1000.00),

-- David: Average spender, recent orders, plus an edge case with NULL/0 amount
(4, CURRENT_DATE() - INTERVAL 15 DAY, 150.00),
(4, CURRENT_DATE() - INTERVAL 20 DAY, 0.00); -- Edge case: Free replacement / full credit

-- Populating order_items for database integrity
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 101, 1, 500.00),
(2, 102, 2, 150.00),
(3, 103, 1, 50.00),
(4, 104, 4, 250.00),
(5, 105, 1, 150.00),
(6, 106, 1, 0.00);

-- =========================================================================
-- 3. QUERY: CUSTOMERS ABOVE AVERAGE SPENDING (LAST 30 DAYS)
-- =========================================================================
WITH CustomerRecentTotals AS (
    -- ---------------------------------------------------------------------
    -- STEP 1: Aggregate spending per customer for the last 30 days
    -- ---------------------------------------------------------------------
    SELECT 
        customer_id,
        COUNT(order_id) AS purchase_count,
        -- COALESCE ensures we don't return NULL if an amount was missing
        SUM(COALESCE(total_amount, 0)) AS total_spending
    FROM 
        orders
    WHERE 
        order_date >= CURRENT_DATE() - INTERVAL 30 DAY
    GROUP BY 
        customer_id
),
GlobalAverage AS (
    -- ---------------------------------------------------------------------
    -- STEP 2: Use a window function to attach the overall average to every row
    -- ---------------------------------------------------------------------
    SELECT 
        customer_id,
        purchase_count,
        total_spending,
        -- Window function calculates the global average across ALL rows in this CTE
        AVG(total_spending) OVER () AS overall_avg_spending
    FROM 
        CustomerRecentTotals
)
-- -------------------------------------------------------------------------
-- FINAL SELECTION: Filter strictly above average and calculate the difference
-- -------------------------------------------------------------------------
SELECT 
    c.customer_id,
    -- MySQL uses CONCAT for string combination
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    ga.purchase_count,
    ga.total_spending,
    -- Round the average for cleaner output
    ROUND(ga.overall_avg_spending, 2) AS overall_avg_spending,
    -- Calculate how much more they spent compared to the average
    (ga.total_spending - ga.overall_avg_spending) AS amount_above_average
FROM 
    GlobalAverage ga
INNER JOIN 
    customers c ON ga.customer_id = c.customer_id
WHERE 
    -- Only include customers who spent more than the overall average
    ga.total_spending > ga.overall_avg_spending
ORDER BY 
    amount_above_average DESC;