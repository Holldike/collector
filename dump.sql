CREATE TABLE `ips` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(232) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
CREATE TABLE `ports` (
  `id_ip` int(11) NOT NULL,
  `port` int(5) NOT NULL,
  `service` varchar(32) DEFAULT NULL,
  `state` varchar(32) DEFAULT NULL
);