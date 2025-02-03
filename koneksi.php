<?php
$host = 'localhost';  
$dbname = 'sipadu';   
$username = 'root';   
$password = '';       
try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Jika gagal koneksi
    echo 'Connection failed: ' . $e->getMessage();
}
?>
