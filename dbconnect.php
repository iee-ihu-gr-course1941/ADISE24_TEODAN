<?php
$username='iee2021233';
$password='mpla123';
$host='localhost';
$dbname = 'THE_DB';


$mysqli = new mysqli($host, $username, $password, $dbname,null,'/home/student/iee/2021/iee2021233/mysql/run/mysql.sock');
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . 
    $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
?>