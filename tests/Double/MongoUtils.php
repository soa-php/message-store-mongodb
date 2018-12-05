<?php

declare(strict_types=1);

namespace Soa\MessageStoreMongoDbTest\Double;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class MongoUtils
{
    public static function server(): string
    {
        return 'mongodb://mongo:27017';
    }

    public static function client(): Client
    {
        return new Client(MongoUtils::server());
    }

    public static function database(): Database
    {
        return self::client()->selectDatabase('test')->withOptions(['typeMap' =>['document' => 'array', 'root' => 'array']]);
    }

    public static function collection(): Collection
    {
        return self::database()->selectCollection('test');
    }

    public static function clean()
    {
        $collections = self::database()->listCollections();
        foreach ($collections as $collection) {
            self::database()->selectCollection($collection->getName())->drop();
        }
    }
}
