<?php
require_once 'Helper.php';

/**
 * ExcelDataImporter Class
 *
 * This class is responsible for importing data from an Excel file
 * containing hotel prices and details, and storing it in a MySQL database.
 */
class ExcelDataImporter {
    private $dbConnection;

    /**
     * Constructor
     *
     * Initializes the ExcelDataImporter with a DatabaseConnection object.
     *
     * @param DatabaseConnection $dbConnection The database connection instance.
     */
    public function __construct(DatabaseConnection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Import Excel Data
     *
     * Reads the data from an Excel file, processes it, and inserts it into
     * the corresponding database tables.
     *
     * @param string $file The path to the Excel file to import.
     */
    public function importExcelData($file) {
        try {
            $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();

            $currentCityId = -1;
            $currentHotels = array();

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $rowData = array();
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if (!empty($rowData)) {
                    if (!Helper::isDate($rowData[0])) { # Row contains city and hotel names
                        $cityCode = substr($rowData[0], 0, 3);
                        $currentCityId = $this->dbConnection->maybeInsertCity($cityCode);
                        
                        for ($i = 1; $i < count($rowData); $i++) {
                            $hotelInfo = Helper::extractHotelAndDays($rowData[$i]);
                            $hotelId = $this->dbConnection->maybeInsertHotel($hotelInfo['code']);
                            $currentHotels[$i] = ['id' => $hotelId, 'days' => $hotelInfo['days']];
                        }
                    } else { # Row contains date and prices
                        $departureDate = date("Y-m-d H:i:s", strtotime(str_replace(".", "-", $rowData[0])));
                        
                        for ($i = 1; $i < count($rowData); $i++) {
                            if (!isset($currentHotels[$i]) || $currentCityId === -1) continue;

                            if (!$currentPrice = $rowData[$i]) continue;
                            
                            $currentHotelId = $currentHotels[$i]['id'];
                            $currentDuration = $currentHotels[$i]['days'];

                            $this->dbConnection->maybeInsertPrice(
                                $currentHotelId, 
                                $currentCityId, 
                                $departureDate, 
                                $currentDuration, 
                                $currentPrice
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>