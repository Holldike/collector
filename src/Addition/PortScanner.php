<?php

namespace Addition;
use \Db;
use \SimpleXMLElement;

class PortScanner {
    private $allIpRows;
    private $ipsSum;
    private $endpoint;

    public function __construct() {
        Db::getConnect()->query("SET @n = 0");
        $res = Db::getConnect()->query("SELECT (@n := @n + 1) AS POSITION, ip, id FROM ips");

        $this->endpoint = (int)file_get_contents('.endpoint');
        $this->allIpRows = $res->fetch_all(MYSQLI_ASSOC);
        $this->ipsSum = $res->num_rows;
    }

    private function refreshEndpointFile(int $position) {
        file_put_contents('.endpoint', $position);
    }

    private function fetchPortsDataFromScanData(SimpleXMLElement $scanData): array {
        $result = [];

        for ($i = 0; ($host = $scanData->host[$i]); $i++) {
            $ports = $host->ports;

            for ($i = 0; ($port = $ports->port[$i]); $i++) {
                $data = [
                    'port' => (int)$port['portid'],
                    'state' =>(string)$port->state['state'],
                    'service' => (string)$port->service['name'],
                ];
                $result[] = $data;
            }
        }

        return $result;
    }

    private function addPorts(int $ipId, array $portsData) {
        foreach ($portsData as $portData) {
            Db::getConnect()->query("
            INSERT INTO ports (id_ip, port, service, state) VALUE(
            '" . $ipId . "', '" . $portData['port'] . "',
            '" . $portData['service'] . "', '" . $portData['state'] . "')");
        }
    }

    public function process() {
        foreach ($this->allIpRows as $ipRow) {
            $ip = $ipRow['ip'];
            $id = $ipRow['id'];
            $position = $ipRow['POSITION'];

            if ($position > $this->endpoint) {
                $this->refreshEndpointFile((int)$position);

                exec('nmap ' . $ip . ' -oX nmap_output.xml');

                $scanData = simplexml_load_file('nmap_output.xml');
                $portData = $this->fetchPortsDataFromScanData($scanData);

                $this->addPorts($id, $portData);
                echo 'yet: ' . ($this->ipsSum - $position) . "\n";
            }
        }
    }
}
