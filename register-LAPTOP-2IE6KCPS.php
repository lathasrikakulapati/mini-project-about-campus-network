<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);

    if ($stmt->execute()) {
        echo "Registration successful.";
        header("Location: login.php");
    } else {
        echo "Registration failed.";
    }
}
?>
