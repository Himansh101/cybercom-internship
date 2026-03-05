-- create database products;
-- use products;

-- Normalized Category Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY,
    category_name VARCHAR(64) NOT NULL
);

-- Products Table with Foreign Key to Categories
CREATE TABLE products (
    product_id INT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(128) NOT NULL,
    -- Using DECIMAL for currency to prevent floating point inaccuracies
    revenue DECIMAL(12, 2) NULL, 
    
    CONSTRAINT fk_category_product FOREIGN KEY (category_id) REFERENCES categories(category_id)
);


INSERT INTO categories (category_id, category_name) VALUES 
(1, 'Electronics'),
(2, 'Apparel'),
(3, 'Books');

INSERT INTO products (product_id, category_id, product_name, revenue) VALUES 
-- Electronics: Has clear top 3, plus a tie, plus a NULL revenue edge case
(101, 1, 'Laptop Pro', 2500.00),
(102, 1, 'Smartphone X', 1200.00),
(103, 1, 'Tablet S', 1200.00),        
(104, 1, 'Wireless Earbuds', 800.00),   
(105, 1, 'Smartwatch', 300.00),         
(106, 1, 'VR Headset', NULL),           -- Edge case: NULL revenue 

-- Apparel: Has exactly 2 products (fewer than 3 edge case)
(201, 2, 'Leather Jacket', 350.00),
(202, 2, 'Running Shoes', 120.00),

-- Books: Has exact same revenue for multiple items
(301, 3, 'SQL Mastery', 50.00),
(302, 3, 'Python Basics', 50.00),
(303, 3, 'Data Science 101', 40.00),
(304, 3, 'Algorithms', 30.00);


-- PRODUCTS RANKING BASED ON REVENUE

WITH RankedProducts AS (
    SELECT 
        p.product_id,
        c.category_name,
        p.product_name,
        p.revenue,
        -- DENSE_RANK assigns the same rank to duplicate values without skipping subsequent ranks.
        -- COALESCE handles the NULL edge case, treating missing revenue as 0 so it ranks last.
        DENSE_RANK() OVER (
            PARTITION BY p.category_id 
            ORDER BY COALESCE(p.revenue, 0) DESC
        ) AS category_rank
    FROM 
        products p
    INNER JOIN 
        categories c ON p.category_id = c.category_id
)

SELECT 
    product_id,
    category_name,
    product_name,
    revenue,
    category_rank
FROM 
    RankedProducts
WHERE 
    -- Filtering outside the window function context 
    category_rank <= 3
ORDER BY 
    category_name, 
    category_rank, 
    product_name;