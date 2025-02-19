<?php
require 'database.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = $conn->query("SELECT * FROM suppliers WHERE id=$id");
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            $result = $conn->query("SELECT * FROM suppliers");
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            echo json_encode($users);
        }
        break;

        default:
            echo json_encode(["message" => "Invalid request method"]);
        break;
    }

echo "API request works";

$conn->close();
?>