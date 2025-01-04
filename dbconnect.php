<?php
$username='root';
$password='';
$host='localhost';
$dbname = 'ataxx_game';
$socket = '/home/student/iee/2021/iee2021233/mysql/run/mysql.sock';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;unix_socket=$socket", $username, $password);
    

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {

    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}