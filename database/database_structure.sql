SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `authorizations` (
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `comment` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `devices` (
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `deveui` binary(8) NOT NULL,
  `app_id` text COLLATE utf8_unicode_ci,
  `dev_id` text COLLATE utf8_unicode_ci,
  `pseudonym` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `last_seen` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `gateways` (
  `id` int(11) NOT NULL,
  `packet_id` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `channel` int(11) NOT NULL,
  `rssi` int(11) NOT NULL,
  `snr` float NOT NULL,
  `rf_chain` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `distance` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `gateway_list` (
  `id` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `channels` int(11) NOT NULL DEFAULT '0',
  `packets` int(11) NOT NULL DEFAULT '1',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `first_seen` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  `ttn_description` text COLLATE utf8_unicode_ci,
  `ttn_brand` text COLLATE utf8_unicode_ci,
  `ttn_model` text COLLATE utf8_unicode_ci,
  `ttn_username` text COLLATE utf8_unicode_ci,
  `ttn_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `gateway_packets` (
  `id` int(11) NOT NULL,
  `type` enum('UPLINK','JOIN') COLLATE utf8_unicode_ci NOT NULL,
  `frequency` float NOT NULL,
  `modulation` enum('LORA','FSK') COLLATE utf8_unicode_ci NOT NULL,
  `SF` int(11) NOT NULL,
  `BW` int(11) NOT NULL,
  `CR_k` int(11) NOT NULL,
  `CR_n` int(11) NOT NULL,
  `airtime` float NOT NULL,
  `count` int(11) NOT NULL,
  `devaddr` binary(4) NOT NULL,
  `payload_size` int(11) NOT NULL,
  `RSSI` int(11) NOT NULL,
  `SNR` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `mapped_device` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `link_list` (
  `id` int(11) NOT NULL,
  `dev_pseudonym` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `snr` double NOT NULL,
  `rssi` int(11) DEFAULT NULL,
  `gtw_lat` decimal(10,8) DEFAULT NULL,
  `gtw_lon` decimal(11,8) DEFAULT NULL,
  `node_lat` decimal(10,8) DEFAULT NULL,
  `node_lon` decimal(11,8) DEFAULT NULL,
  `distance` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `networks` (
  `prefix` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `netid` varchar(8) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `packets` (
  `id` int(11) NOT NULL,
  `dev_pseudonym` int(11) NOT NULL,
  `packet_count` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `frequency` float NOT NULL,
  `modulation` enum('LORA','FSK') COLLATE utf8_unicode_ci NOT NULL,
  `SF` int(11) DEFAULT NULL,
  `BW` int(11) DEFAULT NULL,
  `CR_k` int(11) NOT NULL,
  `CR_n` int(11) NOT NULL,
  `gateway_count` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `stats_sf` (
  `id` int(11) NOT NULL,
  `timestamp_start` datetime NOT NULL,
  `timestamp_end` datetime NOT NULL,
  `SF` enum('7','8','9','10','11','12') COLLATE utf8_unicode_ci NOT NULL,
  `packets` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `authorizations`
  ADD UNIQUE KEY `authorization` (`authorization`),
  ADD KEY `administrator` (`administrator`);

ALTER TABLE `devices`
  ADD PRIMARY KEY (`pseudonym`),
  ADD UNIQUE KEY `deveui` (`deveui`);

ALTER TABLE `gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packet_id` (`packet_id`),
  ADD KEY `distance` (`distance`);
ALTER TABLE `gateways` ADD FULLTEXT KEY `gtw_id` (`gtw_id`);

ALTER TABLE `gateway_list`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `gateway_packets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `link_list`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `link_list` ADD FULLTEXT KEY `gtw_id` (`gtw_id`);

ALTER TABLE `networks`
  ADD PRIMARY KEY (`prefix`),
  ADD KEY `netid` (`netid`);

ALTER TABLE `packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dev_pseudonym` (`dev_pseudonym`),
  ADD KEY `time` (`time`);

ALTER TABLE `stats_sf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timestamp_start` (`timestamp_start`),
  ADD KEY `timestamp_end` (`timestamp_end`);


ALTER TABLE `devices`
  MODIFY `pseudonym` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gateway_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gateway_packets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `link_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `packets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stats_sf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
