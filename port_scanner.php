<?php

require 'bootstrap.php';

const AMOUNT_PROCESS = 3;

$portScanner = new Addition\PortScanner();
$portScanner->process();