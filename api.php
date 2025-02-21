<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'database.php';

header("Content-Type: application/json");

$supplierApi = new SupplierAPI($conn);

$supplierApi->handleRequest();

$conn->close();

/**
 * SupplierAPI class
 */
class SupplierAPI {

    private $conn;
    private $requestMethod = '';
    private $inputData = '';


    // Constructor to initialize the database connection
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Method to handle GET request
    public function handleRequest() {

        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->inputData = json_decode(file_get_contents('php://input'), true);
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;
    
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
                case 'exportSupplierProducts':
                    $this->exportSupplierProducts($id);
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
     * Updates data of supplier
     * @return string JSON encoded products data.
     */
    private function updateSupplier($id) {
        $sql = "UPDATE suppliers SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $inputData['name'], $id);
        $stmt->execute();
        return $this->getSupplierData($id);
    }

        /**
     * Updates data of product
     * @param id $id
     * @return string JSON encoded products data.
     */
    private function updateProduct($id) {

        $partNumber = $this->inputData['partNumber'];
        $supplierId = $this->inputData['supplierId'];
        $partDesc = $this->inputData['partDesc'];
        $price = $this->inputData['price'];
        $quantity = $this->inputData['quantity'];
        $priority = $this->inputData['priority'];
        $daysValid = $this->inputData['daysValid'];
        $conditionId = $this->inputData['conditionId'];
        $categoryId = $this->inputData['categoryId'];

        if (!$this->checkIfExistsSupplier($supplierId))
        {
            echo json_encode(array("message" => "Supplier not found"));
        } else if (!$this->checkIfExistsCondition($conditionId))
        {
            echo json_encode(array("message" => "Conditioin not found"));
        } else if (!$this->checkIfExistsCategory($categoryId))
        {
            echo json_encode(array("message" => "Category not found"));
        }else {
            $sql = "UPDATE parts SET partNumber = ?, supplierId = ?, partDesc = ?, price = ?, quantity = ?, priority = ?, daysValid = ?, conditionId = ?, categoryId = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sisdiiiiii", $partNumber, $supplierId, $partDesc, $price, $quantity, $priority, $daysValid, $conditionId, $categoryId, $id);
            $stmt->execute();
            return $this->getProductData($id);
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
                return $this->updateSupplier($id);
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
                return $this->updateProduct($productId);
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

        /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsSupplier($id) {
        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (null !== $result->fetch_assoc()) {
            return true;
        }else {
            return false;
        }
    }

     /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsCondition($id) {
        $sql = "SELECT * FROM conditions WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (null !== $result->fetch_assoc()) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsCategory($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if (null !== $result->fetch_assoc()) {
            return true;
        }else {
            return false;
        }
    }


    /** Export products of supplier
     */
    private function exportSupplierProducts($supplierId) {
        $sql = "SELECT partNumber, supplierId, partDesc, price, quantity, priority, daysValid, conditionId, categoryId FROM parts WHERE supplierId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $header = array('Part Number', 'Supplier', 'Part Desc', 'Price', 'Quantity', 'Priority', 'Days Valid', 'Condition', 'Category');

        return $this->exportToCSV($header, $products);
    }

    /**
     * Export to CSV file
     */
    private function exportToCSV($header, $data) {
        ob_start();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products.csv');

        ob_end_clean();

        $output = fopen( 'php://output', 'w' );

        fputcsv( $output, $header);

        foreach( $data as $key => $value){
            fputcsv($output, $value);
        }

        fclose($output);
        exit;
    }
}
?>