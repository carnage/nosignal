<?php

include __DIR__ . '/../../vendor/autoload.php';

/** @var \NoSignal\X3DH $alice */
$alice = unserialize(file_get_contents(__DIR__ . '/alice-state'));

$messageReceived = \NoSignal\Message::fromString($argv[1]);

echo $alice->receiveFrom('bob', $messageReceived);

file_put_contents(__DIR__ . '/alice-state', serialize($alice));