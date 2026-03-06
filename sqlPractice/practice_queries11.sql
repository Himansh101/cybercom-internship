

CREATE TABLE customers (
    customer_id SERIAL PRIMARY KEY,
    customer_name VARCHAR(128) NOT NULL
);

CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    
    CONSTRAINT fk_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- CRITICAL FOR LATERAL JOIN PERFORMANCE: 
-- A composite index on the foreign key and the sort column
CREATE INDEX idx_orders_customer_date ON orders(customer_id, order_date DESC);


INSERT INTO customers (customer_name) VALUES 
('Alice'),   
('Bob'),     
('Charlie'); 


INSERT INTO orders (customer_id, order_date, total_amount) VALUES
(1, CURRENT_TIMESTAMP - INTERVAL '10 days', 100.00),
(1, CURRENT_TIMESTAMP - INTERVAL '9 days', 150.00),
(1, CURRENT_TIMESTAMP - INTERVAL '8 days', 200.00),
(1, CURRENT_TIMESTAMP - INTERVAL '7 days', 250.00),
(1, CURRENT_TIMESTAMP - INTERVAL '6 days', 300.00), -- Top 5 cutoff
(1, CURRENT_TIMESTAMP - INTERVAL '2 days', 350.00),
(1, CURRENT_TIMESTAMP - INTERVAL '1 day', 400.00);

-- Bob's Orders (Only 2 orders)
INSERT INTO orders (customer_id, order_date, total_amount) VALUES
(2, CURRENT_TIMESTAMP - INTERVAL '5 days', 50.00),
(2, CURRENT_TIMESTAMP - INTERVAL '1 day', 75.00);



SELECT 
    c.customer_id,
    c.customer_name,
    recent_orders.rn AS order_rank,
    recent_orders.order_id,
    recent_orders.order_date,
    recent_orders.total_amount
FROM 
    customers c

LEFT JOIN LATERAL (
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        -- Generate the row number dynamically within the restricted subset
        ROW_NUMBER() OVER(ORDER BY o.order_date DESC) as rn
    FROM 
        orders o
    WHERE 
        o.customer_id = c.customer_id
    ORDER BY 
        o.order_date DESC
    LIMIT 5 
) recent_orders ON true 
ORDER BY 
    c.customer_id, 
    recent_orders.rn;