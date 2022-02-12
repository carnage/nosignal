<?php

include __DIR__ . '/../../vendor/autoload.php';

/** @var \NoSignal\X3DH $bob */
$bob = unserialize(file_get_contents(__DIR__ . '/bob-state'));

$messageReceived = \NoSignal\Message::fromString($argv[1]);

echo $bob->receiveFrom('alice', $messageReceived);

file_put_contents(__DIR__ . '/bob-state', serialize($bob));