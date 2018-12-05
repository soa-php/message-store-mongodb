<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDb;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Soa\IdentifierGenerator\IdentifierGenerator;
use Soa\IdentifierGeneratorMongoDb\IdentifierGeneratorAutoIncrementMongoDb;
use Soa\MessageStore\Message;
use Soa\MessageStore\MessageStore;
use Soa\MessageStore\Publisher\StoredMessage;

class MessageStoreMongoDb implements MessageStore
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var IdentifierGenerator
     */
    private $identifierGenerator;

    public function __construct(Collection $collection, IdentifierGeneratorAutoIncrementMongoDb $identifierGenerator)
    {
        $this->collection          = $collection;
        $this->identifierGenerator = $identifierGenerator;
    }

    public function appendMessages(Message ...$messages): void
    {
        foreach ($messages as $message) {
            $this->collection->insertOne(
                [
                    '_id'           => (int) $this->identifierGenerator->nextIdentity('outgoing_messages'),
                    'typeName'      => $message->type(),
                    'occurredOn'    => $message->occurredOn(),
                    'timestamp'     => new UTCDateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $message->occurredOn())->getTimestamp() * 1000),
                    'body'          => $message->body(),
                    'streamId'      => $message->streamId(),
                    'correlationId' => $message->correlationId(),
                    'causationId'   => $message->causationId(),
                    'replyTo'       => $message->replyTo(),
                    'messageId'     => $message->id(),
                    'recipient'     => $message->recipient(),
                    'processId'     => $message->processId(),
                ]
            );
        }
    }

    public function messagesSince(int $offset): array
    {
        $cursor = $this->collection->find([], [
            'skip'  => $offset,
            'sort'  => ['_id' => 1],
            'limit' => 500,
        ]);

        return array_map(
            function ($data) {
                return new StoredMessage(
                    $data['typeName'],
                    new \DateTimeImmutable($data['occurredOn']),
                    $data['body'],
                    $data['streamId'],
                    $data['correlationId'],
                    $data['causationId'],
                    $data['replyTo'],
                    $data['messageId'],
                    $data['_id'],
                    $data['recipient'],
                    $data['processId']
                );
            },
            $cursor->toArray()
        );
    }
}
