<?php

const AMOUNT_PROCESS = 20;
const TMP_DIR = __DIR__ . '/tmp';

//declare(ticks = 1);
//pcntl_signal(SIGTERM, "sigHandler");
//pcntl_signal(SIGINT, "sigHandler");

//function sigHandler() {
//    $files = glob(TMP_DIR . '/*');
//    foreach($files as $file) {
//        if (is_file($file)) {
//            unlink($file);
//        }
//    }
//}

$portScanner = new Addition\PortScanner();
$portScanner->process();
while (true);