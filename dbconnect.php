<?php
$username='root';
$password='';
$host='localhost';
$dbname = 'ataxx_game';


$mysqli = new mysqli($host, $username, $password, $dbname,null,'/run/mysqld/mysqld.sock');
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . 
    $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>