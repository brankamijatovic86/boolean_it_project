<?php
require 'database.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                return $this->getSupplier($conn, $_GET['id']);
            } else {
                $result = $conn->query("SELECT * FROM suppliers");
                $suppliers = [];
                while ($row = $result->fetch_assoc()) {
                    $suppliers[] = $row;
                }
                echo json_encode($suppliers);
            }
            break;

            case 'PUT':
                $name = $input['name'];
                $sql = "UPDATE suppliers SET name = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $input['name'], $_GET['id']);
                $stmt->execute();
                return $this->getSupplier($_GET['id']);
            break;

            case 'DELETE':
                $id = $_GET['id'];
                $sql = "DELETE FROM suppliers WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                echo json_encode(["message" => "Supplier deleted successfully"]);
            break;

            default:
                echo json_encode(["message" => "Invalid request method"]);
            break;
        }

    } catch(Exception $e) {
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }

    function getSupplier($conn, $id) {
        if (!isset($id) || !is_int($id)) {
            return json_encode(array("message" => "Invalid supplier ID"));
        }

        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            return json_encode($data);
        } else {
            return json_encode(array("message" => "Supplier not found"));
        }
    }

$conn->close();
?>