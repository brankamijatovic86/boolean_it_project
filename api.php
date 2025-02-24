<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'database.php';
require_once 'csv_export_service.php';

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
            $methodMap = [
                'getSupplier' => 'getSupplier',
                'getSuppliers' => 'getSuppliers',
                'getProduct' => 'getProduct',
                'getProducts' => 'getProducts',
                'getSupplierProducts' => 'getSupplierProducts',
                'exportSupplierProducts' => 'exportSupplierProducts'
            ];

            if (isset($methodMap[$action])) {
                $this->{$methodMap[$action]}($id ?? $productId);
            } else {
                $this->sendError("Invalid request method");
            }
        } catch (Exception $e) {
            $this->sendError("Error: " . $e->getMessage());
        }
    }

    private function sendError($message) {
        echo json_encode(["message" => $message]);
    }

    /**
     * Updates data of supplier
     * @return string JSON encoded products data.
     */
    private function updateSupplier($id) {
        $sql = "UPDATE suppliers SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $this->inputData['name'], $id);
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
            $this->sendError("Supplier not found");
        } else if (!$this->checkIfExistsCondition($conditionId))
        {
            $this->sendError("Conditioin not found");
        } else if (!$this->checkIfExistsCategory($categoryId))
        {
            $this->sendError("Category not found");
        }else {
            $sql = "UPDATE parts SET partNumber = ?, supplierId = ?, partDesc = ?, price = ?, quantity = ?, priority = ?, daysValid = ?, conditionId = ?, categoryId = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sisdiiiiii", $partNumber, $supplierId, $partDesc, $price, $quantity, $priority, $daysValid, $conditionId, $categoryId, $id);
            $stmt->execute();
            return $this->getProductData($id);
        }

    }

       /**
     * Gets all table records
     * @return string JSON encoded table records
     */
    private function getList($table) {
        $sql = "SELECT * FROM {$table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        echo json_encode($list);
    }

   /**
     * Gets all suppliers
     * @return string JSON encoded suppliers data.
     */
    private function getSuppliers() {
        $this->getList('suppliers');
    }
    
    /**
     * Gets the supplier for passed id
     * @param int $id 
     * @return string JSON encoded supplier data or error message.
     */
    private function getSupplier($id) {
        if (!isset($id) || !is_int($id)) {
            
            return $this->sendError("Invalid supplier ID");
        }

        switch ($this->requestMethod) {
            case 'GET':
                $data = $this->getSupplierData($id);
                if ($data) {
                    echo json_encode($data);
                } else {
                    $this->sendError("Supplier not found");
                }
                break;
            case 'PUT':
                $data = $this->updateSupplier($id);
                if ($data) {
                    echo json_encode($data);
                } else {
                    $this->sendError("Supplier not found");
                }
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
            return $data;
        } else {
            return null;
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
            return $this->sendError("Product not found");
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
        $this->getList('parts');
    }

   /**
     * Deletes the supplier for passed id
     * @param int $id 
     * @return string JSON encoded
     */
    private function deleteSupplier($id) {
        $this->deleteRecord("suppliers", $id);
    }

     /**
     * Deletes the product for passed id
     * @param int $id 
     * @return string JSON encoded
     */
    private function deleteProduct($id) {
        $this->deleteRecord("parts", $id);
    }

    /**
     * Deletes the product for passed id
     * @param int $id 
     * @return string JSON encoded
     */
    private function deleteRecord($table, $id) {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "Record deleted successfully"]);
    }

     /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsSupplier($id) {
        return $this->checkIfExists('suppliers', $id);
    }

     /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsCondition($id) {
        return $this->checkIfExists('conditions', $id);
    }

    /**
     * @param int $id
     * @return boolean
     */
    private function checkIfExistsCategory($id) {
        return $this->checkIfExists('categories', $id);
    }

     /**
     * @param string $table
     * @param int $id
     * @return boolean
     */
    private function checkIfExists($table, $id) {
        $sql = "SELECT 1 FROM {$table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ? true : false;
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

        $fileName = '';
        $supplier = $this->getSupplierData($supplierId);

        if ($supplier === null) {
            $this->sendError("Supplier not found");
            return;
        } else {
            $csvService = new CsvExportService();
            $fileName = $csvService->createFilename($supplier['name']);

            return $csvService->exportToCSV($header, $products, $fileName);
        }
        
    }

}
?>