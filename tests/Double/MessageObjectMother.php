<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest\Double;

use Soa\Clock\Clock;
use Soa\MessageStore\Message;

class MessageObjectMother
{
    public static function create(): Message
    {
        return new Message(
            'a type',
            (new \DateTimeImmutable())->format(Clock::MICROSECONDS_FORMAT),
            [],
            'a stream id',
            'a correlation id',
            'a causation id',
            'a reply to',
            'an id',
            'a recipient',
            'a process id'
        );
    }
}
