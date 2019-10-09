<?php

class LinkParser {
    private $domainSymbols;

    public function __construct() {
        $this->domainSymbols = $this->generateDomainSymbols();
    }

    private function generateDomainSymbols() {
        $domainSymbols = array_merge(range('a', 'z'), range(1, 9));
        $domainSymbols[] = '.';
        $domainSymbols[] = '-';
        return $domainSymbols;
    }

    public function fetchAllDomains(array $links) {
        $result = [];

        foreach ($links as $link) {
            if ($this->fetchDomain($link)) {
                $result[] = $this->fetchDomain($link);
            }
        }

        return $result;
    }

    public function fetchDomain(string $link) {
        $explodedLink = explode('://', $link);

        if (isset($explodedLink[1]) && $explodedLink[1]) {
            $url = $explodedLink[1];

            for ($i = 0; $i < strlen($url); $i++) {
                if (!in_array($url[$i], $this->domainSymbols)) {
                    $domain = substr($url, 0, $i);
                    return $domain;
                }
            }
        }

        return '';
    }
}