<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest;

use Soa\MessageStoreMongoDb\PublishedMessageTrackerMongoDb;
use PHPUnit\Framework\TestCase;
use Soa\MessageStoreMongoDbTest\Double\MongoUtils;

class PublishedMessageTrackerMongoDbTest extends TestCase
{
    public function setUp()
    {
        MongoUtils::clean();
    }

    /**
     * @test
     */
    public function shouldTrackLastMessagePublishedBy()
    {
        $collection = MongoUtils::collection();
        $tracker    = new PublishedMessageTrackerMongoDb($collection);

        $offset        = 10;
        $publisherName = 'publisher';
        $tracker->trackLastMessagePublishedBy($offset, $publisherName);

        $this->assertNotEmpty($collection->findOne(['_id' => $publisherName, 'offset' => $offset]));
    }

    /**
     * @test
     */
    public function shouldReturnLastMessagePublishedOffset()
    {
        $collection = MongoUtils::collection();
        $tracker    = new PublishedMessageTrackerMongoDb($collection);

        $offset        = 10;
        $publisherName = 'publisher';
        $tracker->trackLastMessagePublishedBy($offset, $publisherName);

        $this->assertEquals($offset, $tracker->lastMessagePublishedBy($publisherName));
    }
}
