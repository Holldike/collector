<?php

class ProcessManager {
    public static function fork($function) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die('fork Error');
        }

        if (!$pid) {
            $function();
        }
    }
}