<?php
use PHPUnit\Framework\TestCase;
use Flight\Exception;

require_once __DIR__ . '/../app/services/GigService.php';
require_once __DIR__ . '/../app/dao/GigDao.php';

class GigTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(GigDao::class);
        $this->service = new GigService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testPayFreelancerFailsOnLowBalance() {
        $this->mockDao->method('get_gig_by_id')->willReturn(['price' => 100]);
        $this->mockDao->method('get_user_balance')->willReturnOnConsecutiveCalls(50, 0);

        $result = $this->service->pay_freelancer(1, 1, 2);

        $this->assertFalse($result['success']);
        $this->assertEquals('Insufficient funds', $result['error']);
    }
}
