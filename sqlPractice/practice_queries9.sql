CREATE TABLE customers (
    customer_id SERIAL PRIMARY KEY,
    first_name VARCHAR(64) NOT NULL,
    last_name VARCHAR(64) NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL
);

CREATE TABLE products (
    product_id SERIAL PRIMARY KEY,
    product_name VARCHAR(128) NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(32) NOT NULL DEFAULT 'Processing',
    
    CONSTRAINT fk_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    -- Historical price snapshot 
    unit_price DECIMAL(10, 2) NOT NULL, 
    
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- =========================================================================
-- 2. DATA INSERTION
-- =========================================================================
INSERT INTO customers (first_name, last_name, email) VALUES 
('Alice', 'Smith', 'alice@example.com'),
('Bob', 'Jones', 'bob@example.com'),
('Charlie', 'Brown', 'charlie@example.com'); -- Edge Case: No orders, should be excluded

INSERT INTO products (product_name, price) VALUES 
('Laptop', 1200.00), ('Mouse', 50.00), ('Keyboard', 100.00);

-- Alice has two orders
INSERT INTO orders (customer_id, order_date) VALUES 
(1, CURRENT_TIMESTAMP - INTERVAL '2 days'),
(1, CURRENT_TIMESTAMP - INTERVAL '1 day');

-- Bob has one order
INSERT INTO orders (customer_id, order_date) VALUES 
(2, CURRENT_TIMESTAMP);

-- Insert items (Laptop and Mouse for Alice's first order)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES 
(1, 1, 1, 1200.00), 
(1, 2, 1, 50.00);

-- Insert items (Keyboard for Alice's second order)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES 
(2, 3, 1, 100.00);

-- Insert items (Laptop and Keyboard for Bob's order)
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES 
(3, 1, 2, 1200.00),
(3, 3, 1, 100.00);


-- =========================================================================
-- 3. QUERY: NESTED JSON GENERATION (Inside-Out Approach)
-- =========================================================================
WITH OrderItemsJson AS (
    -- ---------------------------------------------------------------------
    -- STEP 1: Build the deepest nest -> The Items Array (joined with Products)
    -- Group by order_id to get one array of items per order
    -- ---------------------------------------------------------------------
    SELECT 
        oi.order_id,
        jsonb_agg(
            jsonb_build_object(
                'product_id', p.product_id,
                'product_name', p.product_name,
                'quantity', oi.quantity,
                'unit_price', oi.unit_price,
                'line_total', (oi.quantity * oi.unit_price)
            )
        ) AS items_array
    FROM 
        order_items oi
    INNER JOIN 
        products p ON oi.product_id = p.product_id
    GROUP BY 
        oi.order_id
),
OrdersJson AS (
    -- ---------------------------------------------------------------------
    -- STEP 2: Build the middle nest -> The Orders Array
    -- Wrap order details and the items_array into a single JSON object,
    -- then group by customer_id to get one array of orders per customer
    -- ---------------------------------------------------------------------
    SELECT 
        o.customer_id,
        jsonb_agg(
            jsonb_build_object(
                'order_id', o.order_id,
                'order_date', o.order_date,
                'status', o.status,
                'items', i.items_array
            )
        ) AS orders_array
    FROM 
        orders o
    INNER JOIN 
        OrderItemsJson i ON o.order_id = i.order_id
    GROUP BY 
        o.customer_id
)
-- -------------------------------------------------------------------------
-- FINAL SELECTION: Build the top-level nest -> The Customer Object
-- -------------------------------------------------------------------------
-- We use jsonb_pretty() to format the output nicely. In a real application 
-- returning to a PHP/Node API, you would just use the raw JSON array.
SELECT 
    jsonb_pretty(
        jsonb_agg(
            jsonb_build_object(
                'customer_id', c.customer_id,
                'first_name', c.first_name,
                'last_name', c.last_name,
                'email', c.email,
                'orders', oj.orders_array
            )
        )
    ) AS api_response
FROM 
    customers c
-- INNER JOIN strictly filters out Charlie (customer_id 3) who has no orders
INNER JOIN 
    OrdersJson oj ON c.customer_id = oj.customer_id;