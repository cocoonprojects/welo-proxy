<?php

namespace AppBundle;

use Broadway\CommandHandling\SimpleCommandHandler;
use Monolog\Logger;

class FakeCommandHandler extends SimpleCommandHandler
{
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function handleFakeCommand(FakeCommand $command)
    {
        file_put_contents('/tmp/banana', 'ciaone');
    }
}