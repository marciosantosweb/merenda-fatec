<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    echo "Loading menu.php<br>";
    require 'menu.php';
    echo "Successfully loaded menu.php<br>";
} catch (Throwable $e) {
    echo "Caught Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
