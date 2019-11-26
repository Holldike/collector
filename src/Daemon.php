<?php
declare(ticks=1);

class Daemon {
    private $maxProcesses = 0;
    private $stopServer = false;
    private $currentJobs = array();
    private $additionalSignalHandlers = array();
    private $processLogic;

    public function __construct() {
        $childPid = pcntl_fork();
        if ($childPid) {
            exit();
        }
        posix_setsid();

        //fclose(STDIN);
        //fclose(STDOUT);
        //fclose(STDERR);
        pcntl_signal(SIGINT, array($this, 'signalHandler'));
        pcntl_signal(SIGTERM, array($this, 'signalHandler'));
        pcntl_signal(SIGCHLD, array($this, 'signalHandler'));
    }

    public function setMaxProcesses(int $amount) {
        $this->maxProcesses = $amount;
    }

    /*========================================================*/
    /* ['Signal Type' => 'Callback Function For This Signal'] */
    /*========================================================*/
    public function setAdditionalSignalHandlers(array $handlers) {
        foreach ($handlers as $signal => $handler) {
            if (!is_callable($handler)) {
                throw new Error('Argument is\'t callable function');
            }
        }
        $this->additionalSignalHandlers = array_change_key_case($handlers, CASE_UPPER);
    }

    public function setProcessLogic(callable $logic) {
        $this->processLogic = $logic;
    }

    public function run() {
        echo 'Running daemon controller' . PHP_EOL;

        while (!$this->stopServer) {
            while (count($this->currentJobs) >= $this->maxProcesses) {
                /*==============*/sleep(1);/*==============*/
            }
            $this->launchJob();
        }
    }

    protected function launchJob() {
        $pid = pcntl_fork();
        if ($pid == -1) {
            error_log('Could not launch new job, exiting');
            return false;
        } elseif ($pid) {
            //Parent code
            $this->currentJobs[$pid] = true;
        } else {
            //Child code
            call_user_func($this->processLogic);
            exit;
        }
        return true;
    }

    public function signalHandler($signo) {
        switch ($signo) {
            /*============================SIGTERM============================*/
            case SIGTERM:
                $this->stopServer = true;

                if (isset($this->additionalSignalHandlers['SIGTERM'])) {
                    call_user_func($this->additionalSignalHandlers['SIGTERM']);
                };
                break;
            /*============================SIGCHLD============================*/
            case SIGCHLD:
                if (!$signo['pid']) {
                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
                }
                // Пока есть завершенные дочерние процессы
                while ($pid > 0) {
                    if ($pid && isset($this->currentJobs[$pid])) {
                        //Delete child process from active list
                        unset($this->currentJobs[$pid]);
                    }
                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
                }
                if (isset($this->additionalSignalHandlers['SIGCHLD'])) {
                    call_user_func($this->additionalSignalHandlers['SIGCHLD']);
                };
                break;
        }
    }
}
