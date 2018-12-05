<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest;

use PHPUnit\Framework\TestCase;
use Soa\Clock\Clock;
use Soa\Clock\ClockFake;
use Soa\MessageStoreMongoDb\ErrorMessageTimeoutTrackerMongoDb;
use Soa\MessageStoreMongoDbTest\Double\MessageDummy;
use Soa\MessageStoreMongoDbTest\Double\MongoUtils;

class ErrorMessageTimeoutTrackerMongoDbTest extends TestCase
{
    public function setUp()
    {
        MongoUtils::clean();
    }

    /**
     * @test
     */
    public function shouldTrackMessage()
    {
        $timestamp    = '2019-01-01 00:00:00.000000';
        $collection   = MongoUtils::collection();
        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $id = 'some id';
        $errorTracker->track(new MessageDummy($id));

        $this->assertEquals(['_id' => $id, 'timestamp' => $timestamp], $collection->findOne(['_id' => $id]));
    }

    /**
     * @test
     */
    public function shouldGetTrackingTimeOfTrackedMessage()
    {
        $timestamp = '2019-01-01 00:00:00.000000';
        $id        = 'some id';

        $collection = MongoUtils::collection();
        $collection->insertOne(['_id' => $id, 'timestamp' => $timestamp]);

        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $this->assertEquals(\DateTimeImmutable::createFromFormat(Clock::MICROSECONDS_FORMAT, $timestamp), $errorTracker->trackedAt(new MessageDummy($id)));
    }

    /**
     * @test
     */
    public function shouldReturnNullTrackingInfoIfMessageWasNotTrackedBefore()
    {
        $timestamp = '2019-01-01 00:00:00.000000';
        $id        = 'some id';

        $collection = MongoUtils::collection();

        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $this->assertNull($errorTracker->trackedAt(new MessageDummy($id)));
    }

    /**
     * @test
     */
    public function shouldNotFailUntrackingMessageNotTrackedBefore()
    {
        $timestamp = '2019-01-01 00:00:00.000000';
        $id        = 'some id';

        $collection = MongoUtils::collection();

        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $errorTracker->untrack(new MessageDummy($id));

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function shouldUntrackMessage()
    {
        $timestamp = '2019-01-01 00:00:00.000000';
        $id        = 'some id';

        $collection = MongoUtils::collection();
        $collection->insertOne(['_id' => $id, 'timestamp' => $timestamp]);

        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $errorTracker->untrack(new MessageDummy($id));

        $this->assertEmpty($collection->findOne(['_id' => $id]));
    }

    /**
     * @test
     */
    public function shouldUpdateTrackingIfMessageAlreadyTracked()
    {
        $timestamp    = '2019-01-01 00:00:00.000000';
        $collection   = MongoUtils::collection();
        $errorTracker = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($timestamp)
        );

        $id = 'some id';
        $errorTracker->track(new MessageDummy($id));

        $newTimestamp    = '2020-01-01 00:00:00.000000';
        $collection      = MongoUtils::collection();
        $errorTracker    = new ErrorMessageTimeoutTrackerMongoDb(
            $collection,
            new ClockFake($newTimestamp)
        );

        $id = 'some id';
        $errorTracker->track(new MessageDummy($id));

        $this->assertEquals(['_id' => $id, 'timestamp' => $newTimestamp], $collection->findOne(['_id' => $id]));
    }
}
