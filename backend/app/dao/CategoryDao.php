<?php

require_once __DIR__ . '/../../config/config.php';

Class CategoryDao{

    private $conn;

    public function __construct()
  {
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
        
         // echo "Connected successfully";
        } catch (PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
        }
        }

        public function get_category_by_id($id){
            $query = "SELECT * FROM categories WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function get_categories(){
            $query = "SELECT * FROM categories";
            $stmt = $this->conn->prepare ($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function add_category($category){
            return $this->insert('categories',$category);
        }


        public function delete_category($id){
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }

        public function update_category($data) {
          $sql = "UPDATE categories SET name = :name WHERE id = :id";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute([
              ':name' => $data['name'],
              ':id' => $data['id']
          ]);
          return $this->get_category_by_id($data['id']);
          }
  

        public function insert($table, $entity)
    {
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
      $stmt->execute($entity); // SQL injection prevention
      //$stmt->execute($entity->getData()); // This converts the Collection to an array
      $entity['id'] = $this->conn->lastInsertId();
      return $entity;
    }

}