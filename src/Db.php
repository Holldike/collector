<?php

class Db {
    private $connect;
    private $db;
    private $host;
    private $user;
    private $pass;

    public function __construct (string $user, string $pass, string $host, string $db) {
        $this->user = $user;
        $this->pass = $pass;
        $this->host = $host;
        $this->db = $db;
    }

    public function connect() {

        $this->connect = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->connect->connect_errno) echo $this->connect->connect_error;
    }

    public function addIps(array $ips) {
        $uniqueIps = $this->uniqueFilter($ips);

        foreach ($uniqueIps as $ip) {
            echo 'added ' . $ip . "\n";
            $this->connect->query("INSERT INTO ips (ip) VALUE('$ip');");
        }

        echo 'NEW DUMP!!! SUM: ' . count($uniqueIps) . "\n";
        die;
    }

    private function uniqueFilter(array $ips) {
        foreach ($ips as $key => $ip) {
            $query = $this->connect->query("SELECT COUNT(*) AS count FROM ips WHERE ip = '$ip';");

            if ($query->fetch_assoc()['count']) {
                unset($ips[$key]);
            }
        }
        return $ips;
    }
}