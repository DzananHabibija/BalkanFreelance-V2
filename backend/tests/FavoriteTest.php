<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/FavoriteService.php';
require_once __DIR__ . '/../app/dao/FavoriteDao.php';

class FavoriteTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(FavoriteDao::class);
        $this->service = new FavoriteService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testAddFavorite() {
        $this->mockDao->expects($this->once())
                      ->method('add_favorite')
                      ->with(1, 10)
                      ->willReturn(true);

        $this->assertTrue($this->service->add_favorite(1, 10));
    }
}
