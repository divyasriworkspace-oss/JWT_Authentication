<?php

class RefreshToken
{
    private $conn;
    private $table = "refresh_tokens";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function store($userId, $token, $expiresAt)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, token, expires_at)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "iss",
            $userId,
            $token,
            $expiresAt
        );

        return $stmt->execute();
    }
    public function updateToken($userId,$token,$expiresAt) 
    {
        $sql = "UPDATE {$this->table}
            SET token = ?,
                expires_at = ?,
                is_revoked = 0
            WHERE user_id = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssi",
            $token,
            $expiresAt,
            $userId
        );

        return $stmt->execute();
    }
    public function findValidToken($token)
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE token = ?
                AND is_revoked = 0
                AND expires_at > NOW()
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $token);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
    public function getUserToken($userId)
    {
        $sql = "SELECT *
            FROM {$this->table}
            WHERE user_id = ?
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function revoke($token)
    {
        $sql = "UPDATE {$this->table}
                SET is_revoked = 1
                WHERE token = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $token);

        return $stmt->execute();
    }
}
