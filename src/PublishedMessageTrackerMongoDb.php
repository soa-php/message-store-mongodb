<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDb;

use MongoDB\Collection;
use Soa\MessageStore\Publisher\PublishedMessageTracker;

class PublishedMessageTrackerMongoDb implements PublishedMessageTracker
{
    /**
     * @var Collection
     */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function lastMessagePublishedBy(string $publisherName): int
    {
        $result = $this->collection->findOne(['_id' => $publisherName]);

        return empty($result['offset']) ? 0 : $result['offset'];
    }

    public function trackLastMessagePublishedBy(int $offset, string $publisherName): void
    {
        $this->collection->replaceOne(['_id' => $publisherName], ['_id' => $publisherName, 'offset' => $offset], ['upsert' => true]);
    }
}
