<?php
header("Content-Type: application/json");
require 'connect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['product_id'])) {
            $product_id = intval($_GET['product_id']);
            $sql = "SELECT * FROM products WHERE product_id = $product_id";
            $result = $conn->query($sql);
            echo json_encode($result->fetch_assoc() ?: ["error" => "Product not found"]);
        } else {
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        $seller_id = intval($data['seller_id']);
        $product_name = $conn->real_escape_string($data['product_name']);
        $product_description = $conn->real_escape_string($data['product_description']);
        $price = floatval($data['price']);
        $category = $conn->real_escape_string($data['category']);
        $sql = "INSERT INTO products (seller_id, product_name, product_description, price, category) 
                VALUES ($seller_id, '$product_name', '$product_description', $price, '$category')";
        echo $conn->query($sql) ? json_encode(["message" => "Product created successfully"]) : json_encode(["error" => $conn->error]);
        break;

    case "PUT":
        if (isset($_GET['product_id'])) {
            $product_id = intval($_GET['product_id']);
            $data = json_decode(file_get_contents("php://input"), true);
            $product_name = $conn->real_escape_string($data['product_name']);
            $product_description = $conn->real_escape_string($data['product_description']);
            $price = floatval($data['price']);
            $sql = "UPDATE products SET product_name='$product_name', product_description='$product_description', price=$price WHERE product_id=$product_id";
            echo $conn->query($sql) ? json_encode(["message" => "Product updated successfully"]) : json_encode(["error" => $conn->error]);
        } else {
            echo json_encode(["error" => "Product ID is required"]);
        }
        break;

    case "DELETE":
        if (isset($_GET['product_id'])) {
            $product_id = intval($_GET['product_id']);
            $sql = "DELETE FROM products WHERE product_id = $product_id";
            echo $conn->query($sql) ? json_encode(["message" => "Product deleted successfully"]) : json_encode(["error" => $conn->error]);
        } else {
            echo json_encode(["error" => "Product ID is required"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
}
