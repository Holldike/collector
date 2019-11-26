<?php

namespace Addition\PortScanner;

use \Db;
use \SimpleXMLElement;

class Scanner {
    private $allIpRows;
    private $ipsInProgress = [];

    public function __construct() {
        $this->clearTmp();
        $res = Db::getConnect()->query(
            "SELECT ip, id, ports.id_ip FROM ips 
                    LEFT JOIN ports ON ips.id = ports.id_ip 
                    WHERE ports.id_ip IS NULL"
        );
        $this->allIpRows = $res->fetch_all(MYSQLI_ASSOC);
    }

    public function clearTmp() {
        $files = glob(TMP_DIR . '/*');
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
    }

    private function fetchPortsDataFromNmapOutput(SimpleXMLElement $scanData): array {
        $result = [];

        for ($i = 0; ($host = $scanData->host[$i]); $i++) {
            $ports = $host->ports;

            for ($i = 0; ($port = $ports->port[$i]); $i++) {
                $data = [
                    'port' => (int)$port['portid'],
                    'state' => (string)$port->state['state'],
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
            '" . $portData['service'] . "', '" . $portData['state'] . "')"
            );
        }
    }

    public function process() {
        $nmapOutputFile = TMP_DIR . '/' . getmypid() . '.xml';

        foreach ($this->allIpRows as $key => $ipRow) {
            $ip = $ipRow['ip'];
            $id = $ipRow['id'];

            if (in_array($id, $this->ipsInProgress)) continue;

            $this->ipsInProgress[] = $id;
            unset($this->allIpRows[$key]);
            exec('nmap -F ' . $ip . ' -oX ' . $nmapOutputFile);
            $scanData = simplexml_load_file($nmapOutputFile);
            $portData = $this->fetchPortsDataFromNmapOutput($scanData);
            $this->addPorts($id, $portData);

            //Delete from progress list and from main list ips
            unset($this->ipsInProgress[$id]);
        }
    }

}
