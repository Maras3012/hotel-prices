<?php

/**
 * DatabaseConnection Class
 */
class DatabaseConnection {
    private $conn;

     /**
     * Constructor
     *
     * Initializes a database connection using the provided credentials.
     *
     * @param string $servername The MySQL server name or host.
     * @param string $username   The MySQL username.
     * @param string $password   The MySQL password.
     * @param string $dbname     The name of the MySQL database to connect to.
     *
     * @throws Exception If the database connection fails.
     */
    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            throw new Exception("Database connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Insert or retrieve hotel ID
     *
     * @param string $hotelCode The code of the hotel to insert or retrieve.
     *
     * @return int The ID of the hotel.
     */
    public function maybeInsertHotel($hotelCode) {
        $check_sql = "SELECT ID FROM HOTELI WHERE sifraHotel = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("s", $hotelCode);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            
            return $row['ID'];
        } else {
            $insert_sql = "INSERT INTO HOTELI (sifraHotel) VALUES (?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("s", $hotelCode);
            $insert_stmt->execute();
            
            return $this->conn->insert_id;
        }
    }

    /**
     * Insert or retrieve departure place ID
     *
     * @param string $departureCity The name of the departure place to insert or retrieve.
     *
     * @return int The ID of the departure place.
     */
    public function maybeInsertCity($departureCity) {
        $check_sql = "SELECT ID FROM MJESTAPOLASKA WHERE mjestoPolaska = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("s", $departureCity);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            return $row['ID'];
        } else {
            $insert_sql = "INSERT INTO MJESTAPOLASKA (mjestoPolaska) VALUES (?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("s", $departureCity);
            $insert_stmt->execute();

            return $this->conn->insert_id;
        }
    }

    /**
     * Insert or update price
     *
     * @param int    $hotelId          The ID of the hotel.
     * @param int    $departureCityId  The ID of the departure place.
     * @param string $date             The departure date.
     * @param int    $duration         The duration of the stay.
     * @param float  $price            The price of the accommodation.
     *
     * @return int The ID of the inserted or updated price record.
     */    
    public function maybeInsertPrice($hotelId, $departureCityId, $date, $duration, $price) {
        $check_sql = "SELECT ID FROM CIJENE WHERE HotelID = ? AND mjestoPolaskaID = ? AND datumPolaska = ? AND trajanjeSmjestaja = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("iiss", $hotelId, $departureCityId, $date, $duration);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
    
        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $existing_price_id = $row['ID'];
    
            $update_sql = "UPDATE CIJENE SET cijena = ? WHERE ID = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("di", $price, $existing_price_id);
            $update_stmt->execute();
    
            return $existing_price_id;
        } else {
            $insert_sql = "INSERT INTO CIJENE (HotelID, mjestoPolaskaID, datumPolaska, trajanjeSmjestaja, cijena) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("iissd", $hotelId, $departureCityId, $date, $duration, $price);
            $insert_stmt->execute();
    
            return $this->conn->insert_id;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

?>