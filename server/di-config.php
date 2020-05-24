<?php
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

return [
    Cache::class => function (): Cache {
        return new Cache(new FileStorage(__DIR__ . '/cache'));
    }
];
