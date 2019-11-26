<?php

use Addition\PortScanner\Scanner;

const AMOUNT_PROCESS = 2;
const TMP_DIR = __DIR__ . '/tmp';

$daemon = new Daemon();
$daemon->setMaxProcesses(AMOUNT_PROCESS);

$portScanner = new Scanner();

$daemon->setAdditionalSignalHandlers([
        'SIGTERM' => function () use ($portScanner) {
            $portScanner->clearTmp();
        }
    ]
);

$daemon->setProcessLogic(function () use ($portScanner) {
    $portScanner->process();
});

$daemon->run();


