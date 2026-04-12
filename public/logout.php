<?php
/**
 * Script de Logout
 */
require_once __DIR__ . '/../config/config.php';

session_start();
session_destroy();

header("Location: " . BASE_URL . "login");
exit;
