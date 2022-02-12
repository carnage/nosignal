<?php
include __DIR__ . '/../../vendor/autoload.php';

$bob = new \NoSignal\X3DH();

echo $bob->createHandshakeMessage('alice')->toString();

file_put_contents(__DIR__ . '/bob-state', serialize($bob));