<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ataxx_game";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['playerName'])) {
    $playerName = $conn->real_escape_string($_POST['playerName']);

    $sql = "INSERT INTO Players (name) VALUES ('$playerName')";
    if ($conn->query($sql) === TRUE) {

        $lastId = $conn->insert_id;
        $result = $conn->query("SELECT name FROM Players WHERE id = $lastId");
        $row = $result->fetch_assoc();

        echo "Player name: " . htmlspecialchars($row['name']);
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
