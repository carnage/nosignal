<?php

include __DIR__ . '/../../vendor/autoload.php';

/** @var \NoSignal\X3DH $alice */
$alice = unserialize(file_get_contents(__DIR__ . '/alice-state'));

echo $alice->sendTo('bob', $argv[1])->toString();

file_put_contents(__DIR__ . '/alice-state', serialize($alice));