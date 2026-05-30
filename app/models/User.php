<?php

// Data-access layer for user account records.
class User
{
    // Database connection instance.
    private $conn;

    // Backing table name.
    private $table = "users";

    // Inject active database connection.
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Retrieve a single user by email address.
    public function findByEmail($email)
    {
        // Include id because it is used in the JWT payload during login.
        $sql = "SELECT id, name, email, password FROM {$this->table} WHERE email=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
     // Retrieve a single user by id.
    public function findById($id)
    {
        $sql = "SELECT id, name, email, password FROM {$this->table} WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // Insert a new user record.
    public function create($name, $email, $password)
    {
        $sql = "INSERT INTO {$this->table}
                (name,email,password)
                VALUES (?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $password
        );

        return $stmt->execute();
    }
}
