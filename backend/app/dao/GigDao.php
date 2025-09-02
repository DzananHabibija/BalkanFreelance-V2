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

      public function getAll($excludeUserId = null) {
        if ($excludeUserId) {
            $stmt = $this->conn->prepare("SELECT * FROM gigs WHERE user_id != ?");
            $stmt->execute([$excludeUserId]);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM gigs");
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }


     public function updateGig($id, $title, $price, $status) {
        $stmt = $this->conn->prepare("UPDATE gigs SET title = ?, price = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $price, $status, $id]);

        $stmt = $this->conn->prepare("SELECT * FROM gigs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllWithFilters(array $f) {
        $sql = "SELECT id, title, description, price, category_id, created_at, user_id
                FROM gigs
                WHERE 1=1";
        $params = [];

        if (!empty($f['exclude_user'])) {
            $sql .= " AND user_id <> :exclude_user";
            $params[':exclude_user'] = $f['exclude_user'];
        }
        if ($f['min_price'] !== null) {
            $sql .= " AND price >= :min_price";
            $params[':min_price'] = $f['min_price'];
        }
        if ($f['max_price'] !== null) {
            $sql .= " AND price <= :max_price";
            $params[':max_price'] = $f['max_price'];
        }
        if (!empty($f['category_id'])) {
            $sql .= " AND category_id = :category_id";
            $params[':category_id'] = $f['category_id'];
        }
        if (!empty($f['posted_within'])) {
            if ($f['posted_within'] === 'week') {
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($f['posted_within'] === 'month') {
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            } elseif ($f['posted_within'] === 'year') {
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            }
        }
        if (!empty($f['q'])) {
            $sql .= " AND (title LIKE :q OR description LIKE :q)";
            $params[':q'] = '%'.$f['q'].'%';
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
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