-- SQL de criação do Banco de Dados Merenda Fatec
CREATE DATABASE IF NOT EXISTS merenda_fatec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE merenda_fatec;

-- Tabela de Configurações
CREATE TABLE IF NOT EXISTS settings (
    config_key VARCHAR(50) PRIMARY KEY,
    config_value TEXT
);

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'cozinha', 'aluno') DEFAULT 'aluno',
    status ENUM('active', 'inactive') DEFAULT 'active',
    microsoft_id VARCHAR(255),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cardápio
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Reservas
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date DATE,
    repetitions INT DEFAULT 0,
    modifications INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, date),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabela de Dias Bloqueados
CREATE TABLE IF NOT EXISTS blocked_days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE UNIQUE,
    description VARCHAR(255)
);

-- Dados Iniciais
INSERT IGNORE INTO settings (config_key, config_value) VALUES 
('login_expiration_days', '30'),
('reservation_start', '18:00:00'),
('reservation_end', '19:30:00'),
('notification_text', 'Lembre-se de confirmar sua janta hoje!'),
('notifications_per_day', '1');

INSERT IGNORE INTO users (name, email, role, password) VALUES 
('Cozinha Principal', 'cozinha', 'cozinha', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- senha: password
