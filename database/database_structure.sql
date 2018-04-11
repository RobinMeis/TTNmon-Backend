-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 11, 2018 at 08:55 PM
-- Server version: 10.1.26-MariaDB-0+deb9u1
-- PHP Version: 7.0.27-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ttnmon`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorizations`
--

CREATE TABLE `authorizations` (
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `comment` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `gateways`
--

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
  `distance` double DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gateway_list`
--

CREATE TABLE `gateway_list` (
  `id` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `channels` int(11) NOT NULL DEFAULT '0',
  `packets` int(11) NOT NULL DEFAULT '1',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `first_seen` datetime NOT NULL,
  `last_seen` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_list`
--

CREATE TABLE `link_list` (
  `id` int(11) NOT NULL,
  `dev_pseudonym` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `snr` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packets`
--

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authorizations`
--
ALTER TABLE `authorizations`
  ADD UNIQUE KEY `authorization` (`authorization`),
  ADD KEY `administrator` (`administrator`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`pseudonym`),
  ADD UNIQUE KEY `deveui` (`deveui`);

--
-- Indexes for table `gateways`
--
ALTER TABLE `gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packet_id` (`packet_id`);
ALTER TABLE `gateways` ADD FULLTEXT KEY `gtw_id` (`gtw_id`);

--
-- Indexes for table `gateway_list`
--
ALTER TABLE `gateway_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `link_list`
--
ALTER TABLE `link_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packets`
--
ALTER TABLE `packets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dev_pseudonym` (`dev_pseudonym`),
  ADD KEY `time` (`time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `pseudonym` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gateways`
--
ALTER TABLE `gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gateway_list`
--
ALTER TABLE `gateway_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `link_list`
--
ALTER TABLE `link_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `packets`
--
ALTER TABLE `packets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
