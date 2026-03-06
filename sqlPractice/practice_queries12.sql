
CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(64) NOT NULL
);

CREATE TABLE regions (
    region_id SERIAL PRIMARY KEY,
    region_name VARCHAR(64) NOT NULL
);

CREATE TABLE sales (
    sale_id SERIAL PRIMARY KEY,
    -- NULL allowed to test the edge case of uncategorized/unassigned sales
    category_id INT NULL, 
    region_id INT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    sale_date DATE NOT NULL DEFAULT CURRENT_DATE,
    
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT fk_region FOREIGN KEY (region_id) REFERENCES regions(region_id)
);


INSERT INTO categories (category_name) VALUES ('Electronics'), ('Furniture');
INSERT INTO regions (region_name) VALUES ('North'), ('South');

INSERT INTO sales (category_id, region_id, amount) VALUES 
-- Standard Sales
(1, 1, 500.00), (1, 1, 300.00), 
(1, 2, 400.00),               
(2, 1, 600.00),                 
(2, 2, 700.00), (2, 2, 200.00), 

-- Sale with no category 
(NULL, 1, 150.00), 

--  Sale with no region 
(1, NULL, 250.00); 


SELECT 
    
    CASE 
        WHEN GROUPING(c.category_name) = 1 THEN '--> ALL CATEGORIES (Total)'
        ELSE COALESCE(c.category_name, 'Uncategorized') 
    END AS category,
    
    CASE 
        WHEN GROUPING(r.region_name) = 1 THEN '--> ALL REGIONS (Total)'
        ELSE COALESCE(r.region_name, 'Unassigned Region') 
    END AS region,
    

    COUNT(s.sale_id) AS total_transactions,
    SUM(s.amount) AS total_revenue
    
FROM 
    sales s
LEFT JOIN 
    categories c ON s.category_id = c.category_id
LEFT JOIN 
    regions r ON s.region_id = r.region_id
    
-- FINAL selection
GROUP BY 
    GROUPING SETS (
        (c.category_name, r.region_name), 
        (c.category_name),               
        (r.region_name),                  
        ()                                
    )
ORDER BY 
    category, 
    region;