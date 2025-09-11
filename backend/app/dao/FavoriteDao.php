<?php

require_once __DIR__ . '/../../config/config.php';

Class FavoriteDao{

    private $conn;

    public function __construct() {
        try {
            $servername = DB_HOST;
            $username = DB_USER;
            $password = DB_PASSWORD;
            $schema = DB_NAME;
            $port = DB_PORT;

            $this->conn = new PDO(
                "mysql:host=$servername;dbname=$schema;port=$port",
                $username,
                $password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
    }

            public function add_favorite($user_id, $gig_id) {
                $data = ['user_id' => $user_id, 'gig_id'  => $gig_id];
                return $this->insert('favorites', $data);
            }

            public function remove_favorite($user_id, $gig_id) {
                $query = "DELETE FROM favorites WHERE user_id = :user_id AND gig_id = :gig_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":gig_id", $gig_id);
                return $stmt->execute();
            }

            public function get_favorites($user_id) {
                $query = "SELECT gig_id FROM favorites WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            public function insert($table, $entity) {
                $query = "INSERT INTO {$table} (";
                foreach ($entity as $column => $value) {
                    $query .= $column . ", ";
                }
                $query = substr($query, 0, -2);
                $query .= ") VALUES (";
                foreach ($entity as $column => $value) {
                    $query .= ":" . $column . ", ";
                }
                $query = substr($query, 0, -2);
                $query .= ")";
            
                $stmt = $this->conn->prepare($query);
                $stmt->execute($entity);
                $entity['id'] = $this->conn->lastInsertId();
                return $entity;
            }
}