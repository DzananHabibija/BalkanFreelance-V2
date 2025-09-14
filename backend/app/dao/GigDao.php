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
            $query = "SELECT id, user_id, title, category_id, 
                            COALESCE(tags, '') AS tags,   -- ✅ always return tags
                            price, status, created_at
                    FROM gigs";
            $stmt = $this->conn->prepare($query);
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

     public function getGigByIdWithUser($gigId)
        {
            $stmt = $this->conn->prepare("
                SELECT 
                    g.*, 
                    u.first_name AS user_first_name, 
                    u.last_name AS user_last_name,
                    u.email AS user_email,
                    u.phone_number AS user_phone,
                    c.name AS category_name
                FROM gigs g
                JOIN users u ON g.user_id = u.id
                LEFT JOIN categories c ON g.category_id = c.id
                WHERE g.id = ?
                LIMIT 1
            ");
            $stmt->execute([$gigId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }




    public function updateGig($id, $title, $price, $status, $gig_image_url = null) {
    $sql = "UPDATE gigs SET
                title = :title,
                price = :price,
                status = :status,
                gig_image_url = :gig_image_url
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':price' => $price,
        ':status' => $status,
        ':gig_image_url' => $gig_image_url,
        ':id' => $id
    ]);

    $stmt = $this->conn->prepare("SELECT * FROM gigs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    public function getAllWithFilters(array $f) {
        $sql = "SELECT id, title, description, price, category_id, created_at, user_id, gig_image_url
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

      public function apply_to_gig($gig_id, $user_id, $cover_letter) {
        $sql = "INSERT INTO applications (user_id, gig_id, cover_letter, status, applied_at)
                VALUES (:user_id, :gig_id, :cover_letter, 'pending', NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':gig_id' => $gig_id,
            ':cover_letter' => $cover_letter
        ]);
        
        return true;
     }

     public function get_applications_for_gig($gig_id) {
    $sql = "SELECT 
                a.id as application_id,
                a.user_id,
                a.cover_letter,
                a.status,
                a.paid,
                a.applied_at,
                u.first_name AS user_first_name,
                u.last_name AS user_last_name,
                u.email AS user_email,
                u.phone_number AS user_phone
            FROM applications a
            JOIN users u ON a.user_id = u.id
            WHERE a.gig_id = :gig_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':gig_id' => $gig_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function approve_applicant($gig_id, $user_id) {
        
        $sql = "UPDATE applications
                SET status = CASE
                    WHEN user_id = :user_id THEN 'approved'
                    ELSE 'rejected'
                END
                WHERE gig_id = :gig_id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':gig_id' => $gig_id
        ]);
    }

    public function updateApplicationStatus($gig_id, $user_id, $status) {
        $stmt = $this->conn->prepare("UPDATE applications SET status = :status WHERE gig_id = :gig_id AND user_id = :user_id");
        return $stmt->execute([
            ':status' => $status,
            ':gig_id' => $gig_id,
            ':user_id' => $user_id
        ]);
    }


    public function get_application_status($gig_id, $user_id) {
        $sql = "SELECT status
                FROM applications
                WHERE gig_id = :gig_id AND user_id = :user_id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':gig_id' => $gig_id,
            ':user_id' => $user_id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_balance($user_id) {
        $sql = "SELECT balance FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? floatval($result['balance']) : 0.00;
    }

    public function update_user_balance($user_id, $new_balance) {
        $sql = "UPDATE users SET balance = :balance WHERE id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':balance' => $new_balance,
            ':user_id' => $user_id
        ]);
    }

    public function mark_application_paid($gig_id, $user_id) {
        $sql = "UPDATE applications 
                SET paid = 1 
                WHERE gig_id = :gig_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':gig_id' => $gig_id,
            ':user_id' => $user_id
        ]);
    }

    public function is_application_paid($gig_id, $user_id) {
        $sql = "SELECT paid FROM applications 
                WHERE gig_id = :gig_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':gig_id' => $gig_id,
            ':user_id' => $user_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function record_transaction($data) {
        $sql = "INSERT INTO transactions (sender_id, receiver_id, gig_id, amount, status, transaction_date)
                VALUES (:sender_id, :receiver_id, :gig_id, :amount, :status, :transaction_date)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':sender_id' => $data['sender_id'],
            ':receiver_id' => $data['receiver_id'],
            ':gig_id' => $data['gig_id'],
            ':amount' => $data['amount'],
            ':status' => $data['status'],
            ':transaction_date' => $data['transaction_date']
        ]);
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
                status = :status,
                gig_image_url = :gig_image_url
            WHERE id = :id";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':title' => $data['title'],
        ':description' => $data['description'],
        ':tags' => $data['tags'],
        ':price' => $data['price'],
        ':status' => $data['status'],
        ':gig_image_url' => $data['gig_image_url'] ?? null,
        ':id' => $data['id']
    ]);

    return $this->get_gig_by_id($data['id']);
}


}