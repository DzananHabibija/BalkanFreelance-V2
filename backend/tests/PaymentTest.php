<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/PaymentService.php';
require_once __DIR__ . '/../app/dao/PaymentDao.php';
require_once __DIR__ . '/../app/dao/GigDao.php';

class PaymentTest extends TestCase {
    private $service;
    private $mockPaymentDao;

    protected function setUp(): void {
        $this->mockPaymentDao = $this->createMock(PaymentDao::class);
        $mockGigDao = $this->createMock(GigDao::class);

        $this->service = new PaymentService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('paymentDao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockPaymentDao);
    }

    public function testHandlePayPalPayment() {
        $data = [
            'sender_id' => 1,
            'receiver_id' => 2,
            'gig_id' => 3,
            'amount' => 50
        ];

        $this->mockPaymentDao->expects($this->once())
                             ->method('recordPayPalPayment')
                             ->with($data);
        $this->mockPaymentDao->expects($this->once())
                             ->method('markApplicationAsPaid')
                             ->with(3, 2);

        $this->service->handlePayPalPayment($data);
    }
}
