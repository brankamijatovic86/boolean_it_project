<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'database.php';

header("Content-Type: application/json");

$supplierApi = new SupplierAPI($conn);

echo $supplierApi->handleRequest();

$conn->close();

/**
 * SupplierAPI class
 */
class SupplierAPI {

    private $conn;

    // Constructor to initialize the database connection
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Method to handle GET request
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);
    
        try {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $id = (int)$_GET['id'];
                        return $this->getSupplier($id);
                    } else {
                        $result = $this->conn->query("SELECT * FROM suppliers");
                        $suppliers = [];
                        while ($row = $result->fetch_assoc()) {
                            $suppliers[] = $row;
                        }
                        echo json_encode($suppliers);
                    }
                    break;
                    case 'PUT':
                        $name = $input['name'];
                        $id = (int)$_GET['id'];
                        $sql = "UPDATE suppliers SET name = ? WHERE id = ?";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bind_param("si", $input['name'], $id);
                        $stmt->execute();
                        return $this->getSupplier($id);
                    break;
                    case 'DELETE':
                        $id = (int)$_GET['id'];
                        $sql = "DELETE FROM suppliers WHERE id = ?";
                        $stmt = $this->conn->prepare($sql);
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
    }
    
    /**
     * Gets the supplier for passed id
     * @param int $id 
     * @return string JSON encoded supplier data or error message.
     */
    private function getSupplier($id) {
        if (!isset($id) || !is_int($id)) {
            return json_encode(array("message" => "Invalid supplier ID"));
        }

        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(array("message" => "Supplier not found"));
        }
    }

}
?>