<?php
header("Content-Type: application/json");
require 'connect.php'; // เชื่อมต่อฐานข้อมูล

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['seller_id'])) {
            $seller_id = intval($_GET['seller_id']);
            $sql = "SELECT * FROM sellers WHERE seller_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $seller_id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc() ?: ["error" => "Seller not found"]);
        } else {
            $sql = "SELECT * FROM sellers";
            $result = $conn->query($sql);
            $sellers = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($sellers);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['user_id'], $data['store_name'], $data['store_address'], $data['store_phone'], $data['store_description'])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }

        $user_id = intval($data['user_id']);
        $store_name = $data['store_name'];
        $store_address = $data['store_address'];
        $store_phone = $data['store_phone'];
        $store_description = $data['store_description'];

        $sql = "INSERT INTO sellers (user_id, store_name, store_address, store_phone, store_description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $store_name, $store_address, $store_phone, $store_description);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Seller created successfully", "seller_id" => $stmt->insert_id]);
        } else {
            echo json_encode(["error" => $conn->error]);
        }
        break;

    case "PUT":
        if (isset($_GET['seller_id'])) {
            $seller_id = intval($_GET['seller_id']);
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['store_name'], $data['store_address'])) {
                echo json_encode(["error" => "Missing required fields"]);
                exit;
            }

            $store_name = $data['store_name'];
            $store_address = $data['store_address'];

            $sql = "UPDATE sellers SET store_name = ?, store_address = ? WHERE seller_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $store_name, $store_address, $seller_id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Seller updated successfully"]);
            } else {
                echo json_encode(["error" => $conn->error]);
            }
        } else {
            echo json_encode(["error" => "Seller ID is required"]);
        }
        break;

    case "DELETE":
        if (isset($_GET['seller_id'])) {
            $seller_id = intval($_GET['seller_id']);
            $sql = "DELETE FROM sellers WHERE seller_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $seller_id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Seller deleted successfully"]);
            } else {
                echo json_encode(["error" => $conn->error]);
            }
        } else {
            echo json_encode(["error" => "Seller ID is required"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}
