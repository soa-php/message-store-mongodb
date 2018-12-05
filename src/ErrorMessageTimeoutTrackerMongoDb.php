<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDb;

use MongoDB\Collection;
use Soa\Clock\Clock;
use Soa\MessageStore\Message;
use Soa\MessageStore\Subscriber\Error\ErrorMessageTimeoutTracker;

class ErrorMessageTimeoutTrackerMongoDb implements ErrorMessageTimeoutTracker
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(Collection $collection, Clock $clock)
    {
        $this->collection = $collection;
        $this->clock      = $clock;
    }

    public function track(Message $message): void
    {
        $this->collection->insertOne([
            '_id'       => $message->id(),
            'timestamp' => $this->clock->now()->format(Clock::MICROSECONDS_FORMAT),
        ]);
    }

    public function trackedAt(Message $message): \DateTimeImmutable
    {
        $result = $this->collection->findOne(['_id' => $message->id()]);

        return \DateTimeImmutable::createFromFormat(Clock::MICROSECONDS_FORMAT, $result['timestamp']);
    }

    public function untrack(Message $message): void
    {
        $this->collection->deleteOne(['_id' => $message->id()]);
    }
}
