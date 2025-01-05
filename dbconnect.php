<?php
$username='root';
$password='000000';
$host='localhost';
$dbname = 'ataxx_game';


$socket = '/run/mysqld/mysqld.sock';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;socket=$socket", $username, $password);
    

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {

    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}