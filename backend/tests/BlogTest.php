<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/BlogService.php';
require_once __DIR__ . '/../app/dao/BlogDao.php';

class BlogTest extends TestCase {
    private $service;
    private $mockDao;

    protected function setUp(): void {
        $this->mockDao = $this->createMock(BlogDao::class);
        $this->service = new BlogService();

        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mockDao);
    }

    public function testAddBlogSetsPublishedAt() {
        $blog = ['title' => 'Test'];
        $this->mockDao->expects($this->once())
                      ->method('add_blog')
                      ->with($this->callback(function($data) {
                          return isset($data['published_at']);
                      }))
                      ->willReturn(true);

        $this->service->add_blog($blog);
    }
}
