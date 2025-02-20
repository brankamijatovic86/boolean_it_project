<?php

require "database.php";

if (($handle = fopen($file, "r")) !== FALSE) {
    fgetcsv($handle);

        $supplierQuery = "INSERT IGNORE INTO suppliers (name) VALUES (?)";
        $stmtSupplier = $conn->prepare($supplierQuery);

        $conditionQuery = "INSERT IGNORE INTO conditions (name) VALUES (?)";
        $stmtCondition = $conn->prepare($conditionQuery);

        $categoryQuery = "INSERT IGNORE INTO categories (name) VALUES (?)";
        $stmtCategory = $conn->prepare($categoryQuery);
        
        $queryParts = "INSERT INTO parts (partNumber, supplierId, partDesc, price, quantity, priority, daysValid, conditionId, categoryId) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtParts = $conn->prepare($queryParts);
        
        echo "Start of importing data...\n";

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $supplierName = $data[0];
            $daysValid = $data[1];
            $priority = $data[2];
            $partNumber = $data[3];
            $partDesc = $data[4];
            $quantity = $data[5];
            $price = $data[6];
            $condition = $data[7];
            $category = $data[8];

            if($partNumber == ''){
                break;
            } 
            
            $stmtSupplier->bind_param("s", $supplierName);
            if (!$stmtSupplier->execute()) {
                echo "An error occurred while inserting the supplier: " . $stmtSupplier->error;
            }
            
            $supplierId = $conn->insert_id;
            
            if ($supplierId == 0) {
                $supplierSql = "SELECT id FROM suppliers WHERE name LIKE ?";
                $stmtSupplierName = $conn->prepare($supplierSql);
                
                $supplierNameLike = "%" . $supplierName . "%";
                $stmtSupplierName->bind_param("s", $supplierNameLike);
                $stmtSupplierName->execute();
                $supplierResult = $stmtSupplierName->get_result();

                if ($supplierResult->num_rows > 0) {
                    $supplierRow = $supplierResult->fetch_assoc();
                    $supplierId = $supplierRow['id'];
                } else {
                    echo "Supplier not found!";
                }
            }
        
            $stmtCondition->bind_param("s", $condition);
            if (!$stmtCondition->execute()) {
                echo "An error occurred while inserting the condition: " . $stmtCondition->error;
            }
            
            $conditionId = $conn->insert_id;

            if ($conditionId == 0) {
                $sqlCondition = "SELECT id FROM conditions WHERE name LIKE ?";
                $stmtConditionName = $conn->prepare($sqlCondition);
                
                $conditionNameLike = "%" . $condition . "%";
                $stmtConditionName->bind_param("s", $conditionNameLike);
                $stmtConditionName->execute();
                $conditionResult = $stmtConditionName->get_result();
                
                if ($conditionResult->num_rows > 0) {
                    $conditionRow = $conditionResult->fetch_assoc();
                    $conditionId = $conditionRow['id'];
                } else {
                    echo "Condition not found!";
                }
            }
            
            $stmtCategory->bind_param("s", $category);
            if (!$stmtCategory->execute()) {
                echo "An error occurred while inserting the category: " . $stmtCategory->error;
            }
            $categoryId = $conn->insert_id;

            if ($categoryId == 0) {
                $categorySql = "SELECT id FROM categories WHERE name LIKE ?";
                $stmtCategoryName = $conn->prepare($categorySql);
                
                $categoryNameLike = "%" . $category . "%";
                $stmtCategoryName->bind_param("s", $categoryNameLike);
                $stmtCategoryName->execute();
                $categoryResult = $stmtCategoryName->get_result();
                
                if ($categoryResult->num_rows > 0) {
                    $categoryRow = $categoryResult->fetch_assoc();
                    $categoryId = $categoryRow['id'];
                } else {
                    echo "Condition not found!";
                }
            }

            $stmtParts->bind_param("sisdiiiii", $partNumber, $supplierId, $partDesc, $price, $quantity, $priority, $daysValid, $conditionId, $categoryId);
            if (!$stmtParts->execute()) {
                echo "An error occurred while inserting the part: " . $stmtParts->error . "\n";
            }
            $partId = $conn->insert_id;
        } 

        $stmtSupplier->close();
        $stmtCondition->close();
        $stmtCategory->close();
        $stmtParts->close();
        fclose($handle);
} else {
    echo "An error occurred while opening the CSV file!";
}

sleep(1);
echo "The data was successfully inserted. ";

$conn->close();

