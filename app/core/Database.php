<?php

/*1. connect() call 
2. mysqli error mode enable
3. .env values read 
4. DB connection open 
5. charset set 
6. connection return  */

// Manages MySQL database connection setup.
class Database
{
    // Active mysqli connection instance.
    private $conn;

    // Create and return a configured mysqli connection.
    public function connect()
    {  
        // Promote mysqli warnings to exceptions for centralized error handling.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try{
            $this->conn = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME']
         );

         // Use UTF-8 for full Unicode support.
         $this->conn->set_charset("utf8mb4");

            return $this->conn;

        } catch (Exception $e) {

            // Hide internal DB details from clients.
            Response::json(500, "Database connection failed");
        }
        
    }
}