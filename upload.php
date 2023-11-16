<?php

require 'vendor/autoload.php';
require 'DatabaseConnection.php';
require 'ExcelDataImporter.php';

$servername = "localhost";
$username = "root";
$password = "0000";
$dbname = "HOTELI_CIJENE";

if (isset($_POST["submit"])) {
    $file = $_FILES["file"]["tmp_name"];

    try {
        $dbConnection = new DatabaseConnection($servername, $username, $password, $dbname);
        $importer = new ExcelDataImporter($dbConnection);
        $importer->importExcelData($file);
        $dbConnection->closeConnection();

        header("Location: success.php");
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
