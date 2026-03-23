-- Reporting System Database Initialisation
-- Run automatically by MySQL Docker entrypoint

CREATE DATABASE IF NOT EXISTS reporting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reporting_db;

-- ─── Users ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100)  NOT NULL,
  email         VARCHAR(150)  UNIQUE NOT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Saved Views ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS saved_views (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT          NOT NULL,
  report_id   VARCHAR(100) NOT NULL DEFAULT 'sales_report',
  name        VARCHAR(200) NOT NULL,
  config      JSON         NOT NULL,
  is_default  TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_report (user_id, report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Column Config (per-user column widths / order) ───────────────────────────
CREATE TABLE IF NOT EXISTS column_config (
  user_id       INT          NOT NULL,
  report_id     VARCHAR(100) NOT NULL,
  column_config JSON         NOT NULL,
  updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, report_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Seed: Demo users ─────────────────────────────────────────────────────────
-- Password: admin123   (bcrypt)
INSERT INTO users (name, email, password_hash, role) VALUES
  ('Admin User',   'admin@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
  ('Demo Viewer',  'viewer@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ─── Seed: Default saved view for admin ───────────────────────────────────────
INSERT INTO saved_views (user_id, report_id, name, config, is_default)
SELECT
  u.id,
  'sales_report',
  'Default View',
  JSON_OBJECT(
    'columns',       JSON_ARRAY('id','name','category','price','quantity','region','created_at'),
    'filters',       JSON_ARRAY(),
    'sorting',       JSON_OBJECT('field','created_at','direction','desc'),
    'column_widths', JSON_OBJECT('name',200,'category',150,'price',100,'region',120),
    'date_range',    JSON_OBJECT('start','','end','')
  ),
  1
FROM users u WHERE u.email = 'admin@demo.com'
ON DUPLICATE KEY UPDATE is_default = 1;
