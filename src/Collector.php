<?php

class Collector {
    private $linkParser;
    private $page = '';
    private $ips = [];

    public function __construct(LinkParser $linkParser, Db $db) {
        $this->dom = new DOMDocument();
        $this->db = $db;
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

        $domains = $this->linkParser->fetchAllDomains($links);

        echo 'In processing now ' . count($this->ips) . "\n";

        if (count($this->ips) >= 100) {
            ProcessManager::fork(function () {
                $this->db->connect();
                $this->db->addIps($this->ips);
            });
            $this->ips = [];
        }

        $ips = [];

        foreach ($domains as $domain) {
            $ips[] = $this->getDomainIp($domain);
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
}