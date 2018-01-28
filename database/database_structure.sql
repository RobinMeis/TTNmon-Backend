-- phpMyAdmin SQL Dump
-- version 4.0.10.15
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 29, 2018 at 12:29 AM
-- Server version: 5.1.73-log
-- PHP Version: 5.6.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `smrtnoob_ttnmon`
--

-- --------------------------------------------------------

--
-- Table structure for table `authorizations`
--

CREATE TABLE IF NOT EXISTS `authorizations` (
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `authorization` (`authorization`),
  KEY `administrator` (`administrator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE IF NOT EXISTS `devices` (
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `deveui` binary(8) NOT NULL,
  `app_id` text COLLATE utf8_unicode_ci,
  `dev_id` text COLLATE utf8_unicode_ci,
  `pseudonym` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `last_seen` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`pseudonym`),
  UNIQUE KEY `deveui` (`deveui`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `gateways`
--

CREATE TABLE IF NOT EXISTS `gateways` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  KEY `packet_id` (`packet_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=507960 ;

-- --------------------------------------------------------

--
-- Table structure for table `packets`
--

CREATE TABLE IF NOT EXISTS `packets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `altitude` decimal(7,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dev_pseudonym` (`dev_pseudonym`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=255183 ;

-- --------------------------------------------------------

--
-- Table structure for table `preprocessed_gateway-list`
--

CREATE TABLE IF NOT EXISTS `preprocessed_gateway-list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `channels` int(11) NOT NULL,
  `packets` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `altitude` decimal(7,2) DEFAULT NULL,
  `first_seen` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=128 ;

-- --------------------------------------------------------

--
-- Table structure for table `preprocessed_links`
--

CREATE TABLE IF NOT EXISTS `preprocessed_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dev_pseudonym` int(11) NOT NULL,
  `gtw_id` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `snr` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=256 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
