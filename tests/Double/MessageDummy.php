<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest\Double;

use Soa\MessageStore\Message;

class MessageDummy extends Message
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }
}
