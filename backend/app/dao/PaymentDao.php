<?php

require_once __DIR__ . '/../../config/config.php';

class PaymentDao {

    private $conn;

    public function __construct() {
        $this->conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT,
            DB_USER,
            DB_PASSWORD
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function recordPayPalPayment($data) {
        $sql = "INSERT INTO transactions (sender_id, receiver_id, gig_id, amount, status, transaction_date, method)
                VALUES (:sender_id, :receiver_id, :gig_id, :amount, 'completed', NOW(), 'paypal')";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':sender_id' => $data['sender_id'],
            ':receiver_id' => $data['receiver_id'],
            ':gig_id' => $data['gig_id'],
            ':amount' => $data['amount']
        ]);
    }

    public function markApplicationAsPaid($gig_id, $user_id) {
        $sql = "UPDATE applications SET paid = 1 WHERE gig_id = :gig_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':gig_id' => $gig_id,
            ':user_id' => $user_id
        ]);
    }
}
