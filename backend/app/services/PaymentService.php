<?php

require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/../dao/GigDao.php';

class PaymentService {

    private $paymentDao;
    private $gigDao;

    public function __construct() {
        $this->paymentDao = new PaymentDao();
        $this->gigDao = new GigDao();
    }

    public function handlePayPalPayment($data) {
        // Insert payment record
        $this->paymentDao->recordPayPalPayment([
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'gig_id' => $data['gig_id'],
            'amount' => $data['amount']
        ]);

        // Mark the application as paid
        $this->paymentDao->markApplicationAsPaid($data['gig_id'], $data['receiver_id']);
    }
}
