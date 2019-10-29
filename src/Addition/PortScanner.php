<?php

namespace Addition;
use \Db;
use \SimpleXMLElement;

class PortScanner {
    private $allIpRows;
    private $ipsInProgress = [];

    public function __construct() {
        $res = Db::getConnect()->query(
            "SELECT ip, id, ports.id_ip FROM ips 
                    LEFT JOIN ports ON ips.id = ports.id_ip 
                    WHERE ports.id_ip IS NULL"
        );

        $this->allIpRows = $res->fetch_all(MYSQLI_ASSOC);
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
//        for ($i = AMOUNT_PROCESS; $i > 0; $i--) {
//            \ProcessManager::fork(function () {
                foreach ($this->allIpRows as $key => $ipRow) {
                    $ip = $ipRow['ip'];
                    $id = $ipRow['id'];

                    if ($this->uniqueFilter((int)$id)) {
                        $this->ipsInProgress[] = $id;

                        exec('nmap -F ' . $ip . ' -oX nmap_output.xml');

                        $scanData = simplexml_load_file('nmap_output.xml');
                        $portData = $this->fetchPortsDataFromScanData($scanData);

                        $this->addPorts($id, $portData);
                        //Delete from progress list and from main list ips
                        unset($this->allIpRows[$key]);
                        unset($this->ipsInProgress[$id]);

                        echo 'yet: ' . count($this->allIpRows) . " pid: " . getmypid() . "\n";
                    }
                }
          //  });
//        }
    }

    private function uniqueFilter(int $id) {
        $isIpInProgressScanned = in_array($id, $this->ipsInProgress);
        //If ip don't handling now and return TRUE
        return (!$isIpInProgressScanned) ? true : false;
    }
}
