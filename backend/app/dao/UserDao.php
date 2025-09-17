<?php

require_once __DIR__ . '/../../config/config.php';

class UserDao{

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

    public function get_user_by_id($id){
        $query = "SELECT email,password FROM users WHERE id = :id";
        $stmt = $this->conn->prepare ($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_by_email_or_username($value) {
      $query = "SELECT * FROM users WHERE email = :value OR username = :value";
      $stmt = $this->conn->prepare($query);
      //$stmt->bindParam(':email', $email);
      //$stmt->bindParam(':username',$username);
      $stmt->bindParam(':value',$value);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function get_user_by_email_or_username_combined($email, $username) {
    $query = "SELECT * FROM users WHERE email = :email OR username = :username";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function get_user_by_email_or_username_single($value) {
      $query = "SELECT * FROM users WHERE email = :value OR username = :value";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':value', $value);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function get_users() {
      $query = "SELECT * FROM users";
      $stmt = $this->conn->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

 public function update_user($data) {
      $sql = "UPDATE users SET 
                  first_name = :first_name,
                  last_name = :last_name,
                  email = :email,
                  country_id = :country_id,
                  bio = :bio,
                  balance = :balance,
                  isAdmin = :isAdmin";

      if (isset($data['phone_number'])) {
          $sql .= ", phone_number = :phone_number";
      }

      $sql .= " WHERE id = :id";

      $stmt = $this->conn->prepare($sql);

      $params = [
          ':first_name' => $data['first_name'],
          ':last_name' => $data['last_name'],
          ':email' => $data['email'],
          ':country_id' => $data['country_id'],
          ':bio' => $data['bio'],
          ':balance' => $data['balance'],
          ':isAdmin' => $data['isAdmin'],
          ':id' => $data['id']
      ];

      if (isset($data['phone_number'])) {
          $params[':phone_number'] = $data['phone_number'];
      }

      $stmt->execute($params);
      return $this->get_user_by_id($data['id']);
  }




  


    public function add_user($user){
      return $this->insert('users',$user);
    }

    public function get_otp_secret_by_email($email) {
      $query = "SELECT otp_secret FROM users WHERE email = :email";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      return $stmt->fetchColumn(); // returns the single value directly
  }

    public function delete_user($id){
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, bio, phone_number, profile_image FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGigsByUserId($id) {
    $stmt = $this->conn->prepare("SELECT id, title, price, status, created_at, user_id FROM gigs WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



   public function updateBio($id, $bio) {
      $stmt = $this->conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
      $stmt->execute([$bio, $id]);

      // Return updated user (optional but useful)
      $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, bio FROM users WHERE id = ?");
      $stmt->execute([$id]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_user_by_email($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBalance($userId) {
        $stmt = $this->conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
      }

    public function increaseBalance($userId, $amount) {
        $stmt = $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        return $stmt->execute([$amount, $userId]);
      }

      public function update_phone($id, $phone_number) {
    $stmt = $this->conn->prepare("UPDATE users SET phone_number = :phone_number WHERE id = :id");
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, phone_number FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function insert_google_user($table, $entity)
    {
        $data = $entity; // $data = $entity->getData();


        // Only keep allowed fields for BalkanFreelance
        $allowedFields = [
          'first_name', 'last_name', 'email', 'password',
          'country_id', 'profile_image', 'bio', 'balance', 'isAdmin',
          'phone_number' 
        ];


        // Filter input data to include only allowed fields
        $filteredData = array_filter(
            $data,
            fn($key) => in_array($key, $allowedFields),
            ARRAY_FILTER_USE_KEY
        );

        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));

        $query = "INSERT INTO {$table} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($filteredData);

        $filteredData['id'] = $this->conn->lastInsertId();
        return $filteredData;
    }





}