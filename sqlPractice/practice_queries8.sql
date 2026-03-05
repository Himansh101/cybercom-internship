-- CREATE DATABASE transaction_tracking;
-- USE transaction_tracking;

CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_date DATETIME NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'Completed' CHECK (status IN ('Completed', 'Refunded', 'Failed'))
);

-- =========================================================================
-- 2. DATA INSERTION
-- =========================================================================
INSERT INTO transactions (transaction_date, amount, status) VALUES 
(CURRENT_DATE() - INTERVAL 2 MONTH, 5000.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 2 MONTH, 1500.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 3 MONTH, 7000.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 4 MONTH, 4500.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 12 MONTH, 10000.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 13 MONTH, 8000.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 20 MONTH, 6000.00, 'Completed'),
(CURRENT_DATE() - INTERVAL 2 MONTH, -500.00, 'Refunded'),
(CURRENT_DATE() - INTERVAL 1 MONTH, 2000.00, 'Failed');

-- =========================================================================
-- 3. QUERY: TIME SERIES AGGREGATION (24 MONTHS)
-- =========================================================================
WITH RECURSIVE MonthSeries AS (
    -- Anchor: 23 months ago, truncated to the 1st of the month
    SELECT CAST(DATE_FORMAT(CURRENT_DATE() - INTERVAL 23 MONTH, '%Y-%m-01') AS DATE) AS report_month
    
    UNION ALL
    
    -- Recursive step: Add 1 month until we reach the current month
    SELECT CAST(report_month + INTERVAL 1 MONTH AS DATE)
    FROM MonthSeries
    WHERE report_month < CAST(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') AS DATE)
),
MonthlyAggregates AS (
    SELECT 
        CAST(DATE_FORMAT(transaction_date, '%Y-%m-01') AS DATE) AS txn_month,
        SUM(amount) AS actual_revenue
    FROM transactions
    WHERE status IN ('Completed', 'Refunded') 
      AND transaction_date >= DATE_FORMAT(CURRENT_DATE() - INTERVAL 23 MONTH, '%Y-%m-01')
    GROUP BY CAST(DATE_FORMAT(transaction_date, '%Y-%m-01') AS DATE)
),
ZeroFilledData AS (
    SELECT 
        YEAR(ms.report_month) AS sales_year,
        MONTH(ms.report_month) AS sales_month,
        COALESCE(ma.actual_revenue, 0) AS monthly_revenue
    FROM MonthSeries ms
    LEFT JOIN MonthlyAggregates ma ON ms.report_month = ma.txn_month
)
SELECT 
    sales_year,
    sales_month,
    monthly_revenue,
    LAG(monthly_revenue) OVER (ORDER BY sales_year, sales_month) AS prev_month_revenue,
    SUM(monthly_revenue) OVER (PARTITION BY sales_year ORDER BY sales_month) AS ytd_revenue,
    SUM(monthly_revenue) OVER (ORDER BY sales_year, sales_month) AS overall_running_total
FROM ZeroFilledData
ORDER BY sales_year DESC, sales_month DESC;