<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/dao/AuthDao.php';

class AuthTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(AuthDao::class);
        $this->service = new AuthService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('auth_dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testGetUserByEmail() {
        $expected = ['id' => 1, 'email' => 'test@test.com'];
        $this->mockDao->method('get_user_by_email')->willReturn($expected);

        $result = $this->service->get_user_by_email('test@test.com');

        $this->assertEquals($expected, $result);
    }

    public function testAddUser() {
        $user = ['email' => 'new@test.com'];
        $this->mockDao->expects($this->once())
                      ->method('add_user')
                      ->with($user)
                      ->willReturn(['id' => 2] + $user);

        $result = $this->service->add_user($user);
        $this->assertEquals(2, $result['id']);
    }
}
