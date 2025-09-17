<?php 
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/services/ReviewService.php';

class ReviewTest extends TestCase {
    private $reviewService;
    private $mockDao;

    protected function setUp(): void {
        // Mock DAO umjesto prave baze
        $this->mockDao = $this->createMock(ReviewDao::class);
        $this->reviewService = new ReviewService();

        // Ručno zamijeni dao sa mockom
        $reflection = new ReflectionClass($this->reviewService);
        $property = $reflection->getProperty('dao');
        $property->setAccessible(true);
        $property->setValue($this->reviewService, $this->mockDao);
    }

    public function testAddReviewSuccess() {
        $data = [
            'reviewer_id' => 1,
            'reviewed_id' => 2,
            'gig_id' => 10,
            'rating' => 5,
            'review_comment' => 'Odličan posao!'
        ];

        $this->mockDao->expects($this->once())
                      ->method('insert_review')
                      ->with($data)
                      ->willReturn(['id' => 1] + $data);

        $result = $this->reviewService->add_review($data);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals(5, $result['rating']);
    }

    public function testAddReviewFailsOnSameUser() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("You cannot review yourself.");

        $data = [
            'reviewer_id' => 1,
            'reviewed_id' => 1,
            'gig_id' => 10,
            'rating' => 4
        ];

        $this->reviewService->add_review($data);
    }

    public function testAddReviewFailsOnInvalidRating() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Rating must be between 1 and 5.");

        $data = [
            'reviewer_id' => 1,
            'reviewed_id' => 2,
            'gig_id' => 10,
            'rating' => 10
        ];

        $this->reviewService->add_review($data);
    }
}
