<?php

include __DIR__ . '/../../vendor/autoload.php';

/** @var \NoSignal\X3DH $bob */
$bob = unserialize(file_get_contents(__DIR__ . '/bob-state'));

$initial = \NoSignal\InitialMessage::fromString($argv[1]);

echo $bob->receiveInitialMessage('alice', $initial);

file_put_contents(__DIR__ . '/bob-state', serialize($bob));