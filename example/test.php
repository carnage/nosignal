<?php

include '../vendor/autoload.php';

$alice = new \NoSignal\X3DH();
$bob = new \NoSignal\X3DH();

$handshake = $bob->createHandshakeMessage('alice');
$initial = $alice->createInitialMessage('bob', $handshake);
$decrypted = $bob->receiveInitialMessage('alice', $initial);

var_dump($decrypted);