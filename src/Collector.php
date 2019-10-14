<?php

class Collector {
    private $linkParser;
    private $page = '';
    private $ips = [];

    public function __construct(LinkParser $linkParser) {
        $this->dom = new DOMDocument();
        $this->linkParser = $linkParser;
    }

    public function setPage(string $page) {
        $this->page = $page;
    }

    private function fetchAllUrl() {
        @$this->dom->loadHTML($this->page);
        $links = $this->dom->getElementsByTagName('a');

        $result = [];

        foreach ($links as $link) {
            $result[] = $link->getAttribute('href');
        }

        return $result;
    }

    public function process() {
        $links = $this->fetchAllUrl();

        $domains = $this->linkParser->fetchAllDomainsFromArray($links);

        echo 'In processing now ' . count($this->ips) . "\n";

        if (count($this->ips) >= 100) {
            ProcessManager::fork(function () {
                $this->addIps($this->ips);
            });
            $this->ips = [];
        }

        $ips = [];

        foreach ($domains as $domain) {
            $ip = $this->getDomainIp($domain);
            if (preg_match(
                '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',
                $ip)
            ) {
                $ips[] = $ip;
            }
        }

        foreach ($ips as $key => $ip) {
            if (!in_array($ip, $this->ips)) {
                $this->ips[] = $ip;
            }
        }
    }

    private function getDomainIp(string $domain) {
        return gethostbyname($domain);
    }

    public function addIps(array $ips) {
        $uniqueIps = $this->uniqueFilter($ips);

        foreach ($uniqueIps as $ip) {
            echo 'added ' . $ip . "\n";
            Db::getConnect()->query("INSERT INTO ips (ip) VALUE('$ip');");
        }

        echo 'NEW DUMP!!! SUM: ' . count($uniqueIps) . "\n";
        die;
    }

    private function uniqueFilter(array $ips) {
        foreach ($ips as $key => $ip) {
            $query = Db::getConnect()->query("SELECT COUNT(*) AS count FROM ips WHERE ip = '$ip';");

            if ($query->fetch_assoc()['count']) {
                unset($ips[$key]);
            }
        }

        return $ips;
    }
}