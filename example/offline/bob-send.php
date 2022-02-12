<?php

include __DIR__ . '/../../vendor/autoload.php';

/** @var \NoSignal\X3DH $bob */
$bob = unserialize(file_get_contents(__DIR__ . '/bob-state'));

echo $bob->sendTo('alice', $argv[1])->toString();

file_put_contents(__DIR__ . '/bob-state', serialize($bob));