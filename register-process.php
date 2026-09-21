<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $user_id = trim($_POST['user_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $department = trim($_POST['department']);

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, user_id, email, password, role, department) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $user_id, $email, $hashed_password, $role, $department);

    if ($stmt->execute()) {
        echo "<script>alert('Account created! You can now log in.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error: " . addslashes($stmt->error) . "'); history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>