<?php

class ItWorksTest extends \Codeception\TestCase\WPTestCase
{
    // Tests
    public function test_it_works()
    {
        $post = static::factory()->post->create_and_get();
        
        $this->assertInstanceOf(\WP_Post::class, $post);
    }
}
