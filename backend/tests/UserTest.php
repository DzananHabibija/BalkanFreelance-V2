<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/UserService.php';
require_once __DIR__ . '/../app/dao/UserDao.php';

class UserTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(UserDao::class);
        $this->service = new UserService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testIncreaseBalance() {
        $this->mockDao->expects($this->once())
                      ->method('increaseBalance')
                      ->with(1, 100)
                      ->willReturn(true);

        $this->assertTrue($this->service->increaseBalance(1, 100));
    }
}
