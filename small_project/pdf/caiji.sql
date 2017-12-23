/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : caiji

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2015-10-11 21:56:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cp_list
-- ----------------------------
DROP TABLE IF EXISTS `cp_list`;
CREATE TABLE `cp_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kdate` char(40) NOT NULL,
  `red` char(100) NOT NULL,
  `blue` char(10) NOT NULL,
  `zhu` char(20) NOT NULL,
  `sale` char(50) NOT NULL,
  `pool` char(50) NOT NULL,
  `more` char(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cp_page
-- ----------------------------
DROP TABLE IF EXISTS `cp_page`;
CREATE TABLE `cp_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hj_list
-- ----------------------------
DROP TABLE IF EXISTS `hj_list`;
CREATE TABLE `hj_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `list` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hj_page
-- ----------------------------
DROP TABLE IF EXISTS `hj_page`;
CREATE TABLE `hj_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
