<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest;

use Soa\Clock\Clock;
use Soa\MessageStoreMongoDb\MessageStoreMongoDb;
use PHPUnit\Framework\TestCase;
use Soa\IdentifierGeneratorMongoDb\IdentifierGeneratorAutoIncrementMongoDb;
use Soa\MessageStoreMongoDbTest\Double\MessageObjectMother;
use Soa\MessageStoreMongoDbTest\Double\MongoUtils;
use Soa\MessageStoreMongoDbTest\Double\StoredMessageObjectMother;

class MessageStoreMongoDbTest extends TestCase
{
    public function setUp()
    {
        MongoUtils::clean();
    }

    /**
     * @test
     */
    public function shouldReturnMessagesSinceGivenOffset()
    {
        $collection = MongoUtils::collection();
        $store      = new MessageStoreMongoDb($collection, new IdentifierGeneratorAutoIncrementMongoDb(MongoUtils::collection('test_ids')));

        $message  = MessageObjectMother::create();
        $messages = [
            $message->withId('1'),
            $message->withId('2'),
            $message->withId('3'),
            $message->withId('4'),
            $message->withId('5'),
        ];

        $storedMessage  = StoredMessageObjectMother::create()->withOccurredOn(\DateTimeImmutable::createFromFormat(Clock::MICROSECONDS_FORMAT, $message->occurredOn()));
        $storedMessages = [
            $storedMessage->withId('1')->withOffset(1),
            $storedMessage->withId('2')->withOffset(2),
            $storedMessage->withId('3')->withOffset(3),
            $storedMessage->withId('4')->withOffset(4),
            $storedMessage->withId('5')->withOffset(5),
        ];

        $store->appendMessages(...$messages);

        $offset = 3;
        $this->assertEquals(array_slice($storedMessages, $offset), $store->messagesSince($offset));
    }

    /**
     * @test
     */
    public function shouldAppendMessages()
    {
        $collection = MongoUtils::collection();
        $store      = new MessageStoreMongoDb($collection, new IdentifierGeneratorAutoIncrementMongoDb(MongoUtils::collection('test_ids')));

        $message  = MessageObjectMother::create();
        $messages = [
            $message->withId('1'),
            $message->withId('2'),
            $message->withId('3'),
            $message->withId('4'),
            $message->withId('5'),
        ];

        $storedMessage  = StoredMessageObjectMother::create()->withOccurredOn(\DateTimeImmutable::createFromFormat(Clock::MICROSECONDS_FORMAT, $message->occurredOn()));
        $storedMessages = [
            $storedMessage->withId('1')->withOffset(1),
            $storedMessage->withId('2')->withOffset(2),
            $storedMessage->withId('3')->withOffset(3),
            $storedMessage->withId('4')->withOffset(4),
            $storedMessage->withId('5')->withOffset(5),
        ];

        $store->appendMessages(...$messages);

        $this->assertEquals($storedMessages, $store->messagesSince(0));
    }
}
