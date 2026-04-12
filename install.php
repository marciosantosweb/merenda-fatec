<?php
/**
 * Script de Instalação Rápida - Merenda Fatec
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();

    // 1. Criar Tabelas
    $sql = "
    CREATE TABLE IF NOT EXISTS settings (
        config_key VARCHAR(50) PRIMARY KEY,
        config_value TEXT
    );

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

    CREATE TABLE IF NOT EXISTS menu (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

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

    CREATE TABLE IF NOT EXISTS blocked_days (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE UNIQUE,
        description VARCHAR(255)
    );
    ";

    $db->exec($sql);

    // 2. Inserir Configurações Iniciais se não existirem
    $db->prepare("INSERT IGNORE INTO settings (config_key, config_value) VALUES 
        ('login_expiration_days', '30'),
        ('reservation_start', '18:00:00'),
        ('reservation_end', '19:30:00'),
        ('notification_text', 'Lembre-se de confirmar sua janta hoje!'),
        ('notifications_per_day', '1')
    ")->execute();

    // 3. Criar Usuário Cozinha (Senha: fatec123)
    $password = password_hash('fatec123', PASSWORD_BCRYPT);
    $db->prepare("INSERT IGNORE INTO users (name, email, role, password) VALUES 
        ('Cozinha Principal', 'cozinha', 'cozinha', ?)
    ")->execute([$password]);

    echo "<h1>✅ Sistema Instalado com Sucesso!</h1>";
    echo "<p>Tabelas criadas e usuário 'cozinha' pronto.</p>";
    echo "<a href='index.php'>Ir para o Login</a>";

} catch (Exception $e) {
    echo "<h1>❌ Erro na Instalação</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
