<?php
require_once __DIR__ . '/../../config/config.php';

Class AuthDao{
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

public function get_user_by_email($email){
    $query = "SELECT id,email,password, phone_number, first_name, last_name FROM users WHERE email = :email";
    $stmt = $this->conn->prepare ($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// public function get_user_by_email_or_username($value) {
//   $query = "SELECT * FROM users WHERE email = :value OR username = :value";
//   $stmt = $this->conn->prepare($query);
//   //$stmt->bindParam(':email', $email);
//   //$stmt->bindParam(':username',$username);
//   $stmt->bindParam(':value',$value);
//   $stmt->execute();
//   return $stmt->fetch(PDO::FETCH_ASSOC);
// }

public function add_user($user) {
  return $this->insert('users',$user);
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