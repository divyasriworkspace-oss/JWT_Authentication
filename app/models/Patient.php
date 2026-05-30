<?php

// Data-access layer for the patients table.
class Patient
{
    // Database connection instance.
    private $conn;

    // Backing table name.
    private $table = "patients";

    // Inject active database connection.
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fetch all patient rows owned by a specific user.
    public function allByUser($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id=? ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Insert a new patient row.
    public function create($data,$userId)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id,name,age,gender,phone,address)
                VALUES (?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sisss",
            $userId,
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'],
            $data['address']
        );

        return $stmt->execute();
    }
    // Return true when a patient belongs to the given user.
    public function existsByIdAndUser($id, $userId)
    {
        $sql = "SELECT id FROM {$this->table} WHERE id=? AND user_id=? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // Update an existing patient row by id for the owner only.
    public function updateByUser($id, $data, $userId)
    {
        $sql = "UPDATE {$this->table}
                SET name=?, age=?, gender=?, phone=?, address=?
                WHERE id=? AND user_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sisssii",
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'],
            $data['address'],
            $id,
            $userId
        );

        return $stmt->execute();
    }

    // Delete a patient row by id for the owner only.
    public function deleteByUser($id, $userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE id=? AND user_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ii", $id, $userId);

        return $stmt->execute();
    }
}
    
