<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/CategoryService.php';
require_once __DIR__ . '/../app/dao/CategoryDao.php';

class CategoryTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(CategoryDao::class);
        $this->service = new CategoryService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testAddCategory() {
        $category = ['name' => 'IT'];
        $this->mockDao->expects($this->once())
                      ->method('add_category')
                      ->with($category)
                      ->willReturn(['id' => 1] + $category);

        $result = $this->service->add_category($category);
        $this->assertEquals(1, $result['id']);
    }
}
