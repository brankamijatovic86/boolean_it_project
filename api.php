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
    private $requestMethod = '';


    // Constructor to initialize the database connection
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Method to handle GET request
    public function handleRequest() {

        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;

        // $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);
    
        try {
            switch ($action) {
                case 'getSupplier':
                    return $this->getSupplier($id);
                break;
                case 'getSuppliers':
                   return $this->getSuppliers();
                break;
                case 'getProduct':
                    return $this->getProduct($productId);
                break;
                case 'getProducts':
                     return $this->getProducts();
                break;
                case 'getSupplierProducts':
                     return $this->getSupplierProducts($id);
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
                default:
                    echo json_encode(["message" => "Invalid request method"]);
                break;
                }
            } catch(Exception $e) {
                echo json_encode(array("message" => "Error: " . $e->getMessage()));
            }
    }

   /**
     * Gets all suppliers
     * @return string JSON encoded products data.
     */
    private function getSuppliers() {
        $stmt = $this->conn->prepare("SELECT * FROM suppliers");
        $stmt->execute();
        $result = $stmt->get_result();
        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
        echo json_encode($suppliers);
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

        switch ($this->requestMethod) {
            case 'GET':
                return $this->getSupplierData($id);
                break;
            case 'PUT':
                break;
            case 'DELETE':
                return $this->deleteSupplier($id);
                break;

            default:
                break;
        }
    }

     /**
     * Gets data of supplier
     * @param int $id 
     * @return string JSON encoded data of supplier
     */
     private function getSupplierData($id) {
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
    
     /**
     * Gets the product for passed id
     * @param int $id 
     * @return string JSON encoded product data or error message.
     */
    private function getProduct($productId) {
        switch ($this->requestMethod) {
            case 'GET':
                return $this->getProductData($productId);
                break;
            case 'PUT':
                break;
            case 'DELETE':
                return $this->deleteProduct($productId);
                break;
            default:
                break;
        }
    }

     /**
     * Gets date of product
     * @param int $productId 
     * @return string JSON encoded data of product
     */
    private function getProductData($productId) {
        $sql = "SELECT * FROM parts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(array("message" => "Product not found"));
        }
    }

     /**
     * Gets the products of the supplier
     * @param int $supplierId 
     * @return string JSON encoded products data.
     */
    private function getSupplierProducts($supplierId) {
        $sql = "SELECT * FROM parts WHERE supplierId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    }

    /**
     * Gets all products
     * @return string JSON encoded products data.
     */
    private function getProducts() {
        $stmt = $this->conn->prepare("SELECT * FROM parts");
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    }

   /**
     * Deletes the supplier for passed id
     * @param int $id 
     * @return string JSON encoded
     */
    private function deleteSupplier($id) {
        $sql = "DELETE FROM suppliers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "Supplier deleted successfully"]);
    }

     /**
     * Deletes the product for passed id
     * @param int $id 
     * @return string JSON encoded
     */
    private function deleteProduct($id) {
        $sql = "DELETE FROM parts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "Product deleted successfully"]);
    }
}
?>