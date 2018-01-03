-- phpMyAdmin SQL Dump
-- version 4.0.10.15
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 03, 2018 at 09:43 PM
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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorization` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authorization_2` (`authorization`),
  KEY `authorization` (`authorization`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorization_id` int(11) NOT NULL,
  `deveui` binary(8) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `authorization_id` (`authorization_id`),
  KEY `deveui` (`deveui`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

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
  `snr` int(11) NOT NULL,
  `rf_chain` int(11) NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `altitude` double DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `packet_id` (`packet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1356 ;

-- --------------------------------------------------------

--
-- Table structure for table `packets`
--

CREATE TABLE IF NOT EXISTS `packets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deveui` binary(8) NOT NULL,
  `time` datetime NOT NULL,
  `frequency` double NOT NULL,
  `modulation` enum('LORA','FSK') COLLATE utf8_unicode_ci NOT NULL,
  `SF` int(11) DEFAULT NULL,
  `BW` int(11) DEFAULT NULL,
  `CR_k` int(11) NOT NULL,
  `CR_n` int(11) NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `altitude` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1356 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;