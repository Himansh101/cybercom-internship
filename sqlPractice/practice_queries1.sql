-- create database organization_schema;

-- use organization_schema;

CREATE TABLE employees(
	employee_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    title VARCHAR(32),
    manager_id INT NULL,
    
    CONSTRAINT fk_manager_employee FOREIGN KEY (manager_id) REFERENCES employees(employee_id) 
);

-- CEO - Top of hierarchy, no manager
INSERT INTO employees (name, title, manager_id) 
VALUES ('Alice', 'CEO', NULL);

-- Top-level managers reporting to CEO
INSERT INTO employees (name, title, manager_id) 
VALUES 
('Bob', 'CTO', 1),      
('Carol', 'CFO', 1);     

-- Mid-level employees reporting to managers
INSERT INTO employees (name, title, manager_id) 
VALUES 
('David', 'Senior Engineer', 2),  
('Eve', 'Engineer', 2),           
('Frank', 'Accountant', 3),       
('Grace', 'Finance Analyst', 3);  

-- Another manager reporting to CTO to show deeper hierarchy
INSERT INTO employees (name, title, manager_id) 
VALUES 
('Hank', 'Lead Engineer', 2),         
('Ivy', 'Junior Engineer', 8);     

-- Add an employee without a department to test NULL handling
INSERT INTO employees (name, title, manager_id) 
VALUES 
('Jack', 'Intern', 4);

WITH RECURSIVE OrgHierarchy AS (
    -- Select the root node (CEO)
    SELECT 
        employee_id,
        name,
        title,
        manager_id,
        1 AS depth_level,
        CAST(name AS CHAR(1024)) AS manager_chain_path,
        -- Edge Case Handling: Use a comma-separated string to track visited nodes
        CAST(employee_id AS CHAR(1024)) AS path_tracker 
    FROM employees
    WHERE manager_id IS NULL

    UNION ALL

    -- Recursive Step: Join to find direct reports
    SELECT 
        e.employee_id,
        e.name,
        e.title,
        e.manager_id,
        oh.depth_level + 1 AS depth_level,
        CONCAT(oh.manager_chain_path, ' -> ', e.name) AS manager_chain_path,
        CONCAT(oh.path_tracker, ',', e.employee_id) AS path_tracker
    FROM employees e
    INNER JOIN OrgHierarchy oh ON e.manager_id = oh.employee_id
    WHERE 
        -- Edge Case Handling: Stop recursion if ID is already in the tracker string to prevent cycles
        FIND_IN_SET(e.employee_id, oh.path_tracker) = 0
)
-- Final Selection with Window Function
SELECT 
    employee_id,
    name,
    title,
    manager_id,
    depth_level,
    manager_chain_path,
    -- Window function avoiding self-joins
    COUNT(*) OVER(PARTITION BY depth_level) AS total_employees_at_this_level
FROM OrgHierarchy
ORDER BY path_tracker;