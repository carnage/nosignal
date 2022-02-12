<?php

include __DIR__ . '/../../vendor/autoload.php';

$alice = new \NoSignal\X3DH();

$handshakeMessage = \NoSignal\PreKeyHandshakeMessage::fromString($argv[1]);

echo $alice->createInitialMessage('bob', $handshakeMessage)->toString();

file_put_contents(__DIR__ . '/alice-state', serialize($alice));