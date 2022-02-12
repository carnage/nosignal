<?php

include '../vendor/autoload.php';

$alice = new \NoSignal\X3DH();
$bob = new \NoSignal\X3DH();

$handshake = $bob->createHandshakeMessage('alice');
var_dump($handshake->toString());
$initial = $alice->createInitialMessage('bob', $handshake);
var_dump($initial->toString());
$decrypted = $bob->receiveInitialMessage('alice', $initial);

var_dump($decrypted);