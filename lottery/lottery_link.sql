/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50711
Source Host           : localhost:3306
Source Database       : caiji

Target Server Type    : MYSQL
Target Server Version : 50711
File Encoding         : 65001

Date: 2016-12-06 08:56:23
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lottery_link
-- ----------------------------
DROP TABLE IF EXISTS `lottery_link`;
CREATE TABLE `lottery_link` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `href` char(160) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL COMMENT '0未采集 1 已采集',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uhref` (`href`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;
