<?php
header("Content-Type: application/json");
require 'connect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['customer_id'])) {
            $customer_id = intval($_GET['customer_id']);
            $sql = "SELECT * FROM customers WHERE customer_id = $customer_id";
            $result = $conn->query($sql);
            echo json_encode($result->fetch_assoc() ?: ["error" => "Customer not found"]);
        } else {
            $sql = "SELECT * FROM customers";
            $result = $conn->query($sql);
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = intval($data['user_id']);
        $phone_number = $conn->real_escape_string($data['phone_number']);
        $shipping_address = $conn->real_escape_string($data['shipping_address']);

        // ตรวจสอบว่า user_id มีอยู่ใน customers หรือไม่
        $check_sql = "SELECT * FROM customers WHERE user_id = $user_id";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            echo json_encode(["error" => "Customer already exists for this user_id"]);
        } else {
            // เพิ่มข้อมูลใหม่
            $sql = "INSERT INTO customers (user_id, phone_number, shipping_address) VALUES ($user_id, '$phone_number', '$shipping_address')";
            echo $conn->query($sql) ? json_encode(["message" => "Customer created successfully"]) : json_encode(["error" => $conn->error]);
        }
        break;

    case "PUT":
        if (isset($_GET['customer_id'])) {
            $customer_id = intval($_GET['customer_id']);
            $data = json_decode(file_get_contents("php://input"), true);
            $phone_number = $conn->real_escape_string($data['phone_number']);
            $shipping_address = $conn->real_escape_string($data['shipping_address']);
            $sql = "UPDATE customers SET phone_number='$phone_number', shipping_address='$shipping_address' WHERE customer_id=$customer_id";
            echo $conn->query($sql) ? json_encode(["message" => "Customer updated successfully"]) : json_encode(["error" => $conn->error]);
        } else {
            echo json_encode(["error" => "Customer ID is required"]);
        }
        break;

    case "DELETE":
        if (isset($_GET['customer_id'])) {
            $customer_id = intval($_GET['customer_id']);
            $sql = "DELETE FROM customers WHERE customer_id = $customer_id";
            echo $conn->query($sql) ? json_encode(["message" => "Customer deleted successfully"]) : json_encode(["error" => $conn->error]);
        } else {
            echo json_encode(["error" => "Customer ID is required"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}
