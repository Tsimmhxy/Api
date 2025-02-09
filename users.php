<?php
header("Content-Type: application/json");
require 'connect.php'; // เชื่อมต่อกับฐานข้อมูล

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc() ?: ["error" => "User not found"]);
        } else {
            $sql = "SELECT * FROM users";
            $result = $conn->query($sql);
            $users = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($users);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['username'], $data['email'], $data['password'])) {
            echo json_encode(["error" => "Missing required fields: username, email, password"]);
            exit;
        }
        $username = $conn->real_escape_string($data['username']);
        $email = $conn->real_escape_string($data['email']);
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password_hash);
        if ($stmt->execute()) {
            echo json_encode(["message" => "User created successfully", "user_id" => $stmt->insert_id]);
        } else {
            echo json_encode(["error" => $conn->error]);
        }
        break;

    case "PUT":
        if (isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['username'], $data['email'])) {
                echo json_encode(["error" => "Missing required fields: username, email"]);
                exit;
            }

            $username = $conn->real_escape_string($data['username']);
            $email = $conn->real_escape_string($data['email']);

            $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $email, $user_id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "User updated successfully"]);
            } else {
                echo json_encode(["error" => $conn->error]);
            }
        } else {
            echo json_encode(["error" => "User ID is required"]);
        }
        break;

    case "DELETE":
        if (isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $sql = "DELETE FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "User deleted successfully"]);
            } else {
                echo json_encode(["error" => $conn->error]);
            }
        } else {
            echo json_encode(["error" => "User ID is required"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}
