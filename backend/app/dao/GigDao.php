<?php

require_once __DIR__ . '/../../config/config.php';

Class GigDao{

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

        public function get_gig_by_id($id){
            $query = "SELECT * FROM gigs WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function get_gigs(){
            $query = "SELECT * FROM gigs";
            $stmt = $this->conn->prepare ($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function add_gig($gig){
            return $this->insert('gigs',$gig);
        }

       
        public function delete_gig($id){
            $query = "DELETE FROM gigs WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }

        public function search_gigs($searchTerm){
          $query = "SELECT * FROM gigs WHERE title LIKE :search OR description LIKE :search";
          $stmt = $this->conn->prepare($query);
          $likeSearch = '%' . $searchTerm . '%';
          $stmt->bindParam(':search', $likeSearch);
          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function update_gig($data) {
    $sql = "UPDATE gigs SET
                title = :title,
                description = :description,
                tags = :tags,
                price = :price,
                status = :status
            WHERE id = :id";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':tags' => $data['tags'],
        ':price' => $data['price'],
        ':status' => $data['status'],
        ':id' => $data['id']
    ]);

    return $this->get_gig_by_id($data['id']);
}

}