

-- System 1 (e.g., Main Warehouse Database)
CREATE TABLE system1_inventory (
    record_id SERIAL PRIMARY KEY,
    sku VARCHAR(64) NOT NULL,
    product_name VARCHAR(128) NOT NULL,
    stock_quantity INT NULL, -- NULL represents unknown/uncounted stock
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- System 2 (e.g., E-commerce Storefront Database)
CREATE TABLE system2_inventory (
    record_id SERIAL PRIMARY KEY,
    sku VARCHAR(64) NOT NULL,
    product_name VARCHAR(128) NOT NULL,
    stock_quantity INT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- System 1 Data
INSERT INTO system1_inventory (sku, product_name, stock_quantity, last_updated) VALUES 
('SKU-001', 'Laptop', 50, CURRENT_TIMESTAMP - INTERVAL '5 days'),
('SKU-002', 'Wireless Mouse', 100, CURRENT_TIMESTAMP - INTERVAL '2 days'),
('SKU-003', 'Keyboard', 30, CURRENT_TIMESTAMP - INTERVAL '1 day'), -- Missing in Sys2
('SKU-004', 'Monitor', 15, CURRENT_TIMESTAMP - INTERVAL '10 days'), -- Older duplicate
('SKU-004', 'Monitor', 20, CURRENT_TIMESTAMP - INTERVAL '1 hour');  -- Latest duplicate (should be used)

-- System 2 Data
INSERT INTO system2_inventory (sku, product_name, stock_quantity, last_updated) VALUES 
('SKU-001', 'Laptop', 50, CURRENT_TIMESTAMP - INTERVAL '4 days'),   -- Perfect Match
('SKU-002', 'Wireless Mouse', 95, CURRENT_TIMESTAMP - INTERVAL '1 day'), -- Mismatch (95 vs 100)
('SKU-004', 'Monitor', 20, CURRENT_TIMESTAMP - INTERVAL '1 hour'),  -- Match (against Sys1's latest)
('SKU-005', 'Standing Desk', 10, CURRENT_TIMESTAMP - INTERVAL '2 days'), -- Missing in Sys1
('SKU-006', 'Office Chair', NULL, CURRENT_TIMESTAMP - INTERVAL '1 day'); -- Edge Case: NULL quantity



WITH Sys1_Latest AS (
    
    SELECT sku, product_name, stock_quantity
    FROM (
        SELECT 
            sku, product_name, stock_quantity,
            ROW_NUMBER() OVER(PARTITION BY sku ORDER BY last_updated DESC) as rn
        FROM system1_inventory
    ) sub
    WHERE rn = 1
),
Sys2_Latest AS (
    
    SELECT sku, product_name, stock_quantity
    FROM (
        SELECT 
            sku, product_name, stock_quantity,
            ROW_NUMBER() OVER(PARTITION BY sku ORDER BY last_updated DESC) as rn
        FROM system2_inventory
    ) sub
    WHERE rn = 1
)

SELECT 
    COALESCE(s1.sku, s2.sku) AS sku,
    COALESCE(s1.product_name, s2.product_name) AS product_name,
    s1.stock_quantity AS sys1_quantity,
    s2.stock_quantity AS sys2_quantity,
    
    -- Evaluate the reconciliation status
    CASE 
        WHEN s1.sku IS NULL THEN 'Missing in System 1'
        WHEN s2.sku IS NULL THEN 'Missing in System 2'
        -- COALESCE handles the edge case where a quantity might be NULL in one system
        WHEN COALESCE(s1.stock_quantity, -1) <> COALESCE(s2.stock_quantity, -1) THEN 'Quantity Mismatch'
        ELSE 'Matched'
    END AS sync_status
FROM 
    Sys1_Latest s1
FULL OUTER JOIN 
    Sys2_Latest s2 ON s1.sku = s2.sku
ORDER BY 
    sync_status, sku;