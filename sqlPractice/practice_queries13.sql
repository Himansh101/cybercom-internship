

CREATE TABLE customers (
    customer_id SERIAL PRIMARY KEY,
    customer_name VARCHAR(128) NOT NULL,
    -- NULL region allowed 
    region VARCHAR(64) NULL 
);

CREATE TABLE products (
    product_id SERIAL PRIMARY KEY,
    product_name VARCHAR(128) NOT NULL
);

CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    
    CONSTRAINT fk_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);


INSERT INTO customers (customer_name, region) VALUES 
('Alice', 'North America'), 
('Bob', 'Europe'), 
('Charlie', NULL); 

INSERT INTO products (product_name) VALUES 
('Laptop'),        
('Desk'),         
('Office Chair'),  
('Mouse');         


INSERT INTO orders (customer_id) VALUES (1);
INSERT INTO order_items (order_id, product_id) VALUES (1, 1);


INSERT INTO orders (customer_id) VALUES (2);
INSERT INTO order_items (order_id, product_id) VALUES (2, 2), (2, 1);


INSERT INTO orders (customer_id) VALUES (3);
INSERT INTO order_items (order_id, product_id) VALUES (3, 4);

-- NOT EXIST approach

SELECT 
    p.product_id, 
    p.product_name,
    'NOT EXISTS' AS method_used
FROM 
    products p
WHERE 
    NOT EXISTS (
        SELECT 1
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.order_id
        INNER JOIN customers c ON o.customer_id = c.customer_id
        WHERE 
            -- Correlated link to outer query
            oi.product_id = p.product_id 
            AND c.region = 'North America'
    )
ORDER BY p.product_id;


-- LEFT JOIN approach
SELECT 
    p.product_id, 
    p.product_name,
    'LEFT JOIN' AS method_used
FROM 
    products p

LEFT JOIN (
    SELECT DISTINCT oi.product_id
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.order_id
    INNER JOIN customers c ON o.customer_id = c.customer_id
    WHERE c.region = 'North America'
) na_orders ON p.product_id = na_orders.product_id
WHERE 
    -- The Anti-Join filter: Only keep rows where the join failed
    na_orders.product_id IS NULL
ORDER BY p.product_id;