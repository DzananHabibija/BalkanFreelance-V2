<?php

require_once __DIR__ . '/../../config/config.php';

Class BlogDao{

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

        public function get_blog_by_id($id){
            $query = "SELECT * FROM blogs WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function get_blogs(){
            $query = "SELECT * FROM blogs";
            $stmt = $this->conn->prepare ($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function add_blog($blog){
            return $this->insert('blogs',$blog);
        }

   
        public function delete_blog($id){
            $query = "DELETE FROM blogs WHERE id = :id";
            $stmt = $this->conn->prepare ($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        }


        public function update_blog($data) {
    $sql = "UPDATE blogs SET
                title = :title,
                content = :content,
                image_url = :image_url,
                published_at = :published_at
            WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':content' => $data['content'],
        ':image_url' => $data['image_url'],
        ':published_at' => $data['published_at'],
        ':id' => $data['id']
    ]);
    return $this->get_blog_by_id($data['id']);
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