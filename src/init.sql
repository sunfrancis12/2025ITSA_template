-- 醫檢系統資料庫初始化檔案
-- 設定字符集為 UTF-8
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- 建立資料庫
CREATE DATABASE IF NOT EXISTS medical_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medical_system;

-- 1. 人員資料表（包含醫檢員與受檢者）
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) UNIQUE NOT NULL COMMENT '人員編號',
    name VARCHAR(100) NOT NULL COMMENT '姓名',
    username VARCHAR(50) UNIQUE NOT NULL COMMENT '帳號',
    password VARCHAR(255) NOT NULL COMMENT '密碼',
    role ENUM('technician', 'patient') NOT NULL COMMENT '角色（醫檢員/受檢者）',
    first_login BOOLEAN DEFAULT TRUE COMMENT '是否首次登入',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. 醫檢項目資料表
CREATE TABLE medical_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id VARCHAR(20) UNIQUE NOT NULL COMMENT '醫檢項目編號',
    name VARCHAR(100) NOT NULL COMMENT '項目名稱',
    description TEXT COMMENT '項目說明',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. 檢查結果資料表
CREATE TABLE test_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) NOT NULL COMMENT '受檢者編號',
    item_id VARCHAR(20) NOT NULL COMMENT '醫檢項目編號',
    score INT NOT NULL COMMENT '度量分數(1-10)' CHECK (score >= 1 AND score <= 10),
    created_by VARCHAR(20) NOT NULL COMMENT '建立人員編號',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (item_id) REFERENCES medical_items(item_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_patient_item (patient_id, item_id) COMMENT '同一受檢者同一項目不可重複'
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入測試資料
-- 插入醫檢員資料（3位）
INSERT INTO users (user_id, name, username, password, role, first_login) VALUES
('T001', '張醫檢師', 'tech001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', FALSE),
('T002', '李醫檢師', 'tech002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', FALSE),
('T003', '王醫檢師', 'tech003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', FALSE);

-- 插入受檢者資料（5位）
INSERT INTO users (user_id, name, username, password, role, first_login) VALUES
('P001', '陳小明', 'patient001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('P002', '林小華', 'patient002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('P003', '黃小美', 'patient003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('P004', '劉小強', 'patient004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('P005', '趙小玲', 'patient005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE);

-- 插入醫檢項目資料（5項）
INSERT INTO medical_items (item_id, name, description) VALUES
('MI001', '血液常規檢查', '包含白血球、紅血球、血小板等基本血液指標檢查'),
('MI002', '肝功能檢查', '檢測肝臟功能相關指標，包含ALT、AST等'),
('MI003', '腎功能檢查', '檢測腎臟功能相關指標，包含肌酸酐、尿素氮等'),
('MI004', '血糖檢查', '檢測空腹血糖、糖化血色素等糖尿病相關指標'),
('MI005', '膽固醇檢查', '檢測總膽固醇、HDL、LDL等血脂相關指標');

-- 插入檢查結果資料（模擬各種分數）
INSERT INTO test_results (patient_id, item_id, score, created_by) VALUES
-- 陳小明的檢查結果
('P001', 'MI001', 8, 'T001'),
('P001', 'MI002', 7, 'T001'),
('P001', 'MI003', 9, 'T002'),
('P001', 'MI004', 6, 'T002'),
('P001', 'MI005', 5, 'T003'),

-- 林小華的檢查結果
('P002', 'MI001', 9, 'T001'),
('P002', 'MI002', 8, 'T001'),
('P002', 'MI003', 7, 'T002'),
('P002', 'MI004', 8, 'T003'),
('P002', 'MI005', 9, 'T003'),

-- 黃小美的檢查結果
('P003', 'MI001', 6, 'T002'),
('P003', 'MI002', 5, 'T002'),
('P003', 'MI003', 8, 'T001'),
('P003', 'MI004', 7, 'T003'),
('P003', 'MI005', 6, 'T001'),

-- 劉小強的檢查結果
('P004', 'MI001', 10, 'T003'),
('P004', 'MI002', 9, 'T003'),
('P004', 'MI003', 8, 'T001'),
('P004', 'MI004', 9, 'T002'),
('P004', 'MI005', 10, 'T002'),

-- 趙小玲的檢查結果
('P005', 'MI001', 7, 'T001'),
('P005', 'MI002', 6, 'T002'),
('P005', 'MI003', 5, 'T003'),
('P005', 'MI004', 8, 'T001'),
('P005', 'MI005', 7, 'T002');

-- 建立索引以提升查詢效能
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_test_results_patient ON test_results(patient_id);
CREATE INDEX idx_test_results_item ON test_results(item_id);
CREATE INDEX idx_test_results_created_by ON test_results(created_by);