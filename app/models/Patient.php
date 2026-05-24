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

    // Fetch all patient rows.
    public function all()
    {
        $result = $this->conn->query(
            "SELECT * FROM {$this->table}"
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Insert a new patient row.
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (name,age,gender,phone,address)
                VALUES (?,?,?,?,?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sisss",
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'],
            $data['address']
        );

        return $stmt->execute();
    }

    // Update an existing patient row by id.
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET name=?, age=?, gender=?, phone=?, address=?
                WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sisssi",
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'],
            $data['address'],
            $id
        );

        return $stmt->execute();
    }

    // Delete a patient row by id.
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}