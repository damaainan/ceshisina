/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50711
Source Host           : localhost:3306
Source Database       : caiji

Target Server Type    : MYSQL
Target Server Version : 50711
File Encoding         : 65001

Date: 2016-12-06 08:56:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lottery_data
-- ----------------------------
DROP TABLE IF EXISTS `lottery_data`;
CREATE TABLE `lottery_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qihao` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL,
  `r1` tinyint(3) unsigned NOT NULL,
  `r2` tinyint(3) unsigned NOT NULL,
  `r3` tinyint(3) unsigned NOT NULL,
  `r4` tinyint(3) unsigned NOT NULL,
  `r5` tinyint(3) unsigned NOT NULL,
  `r6` tinyint(3) unsigned NOT NULL,
  `blue` tinyint(3) unsigned NOT NULL,
  `rc1` tinyint(3) unsigned NOT NULL,
  `rc2` tinyint(3) unsigned NOT NULL,
  `rc3` tinyint(3) unsigned NOT NULL,
  `rc4` tinyint(3) unsigned NOT NULL,
  `rc5` tinyint(3) unsigned NOT NULL,
  `rc6` tinyint(3) unsigned NOT NULL,
  `p1` int(11) unsigned NOT NULL,
  `p1n` int(11) unsigned NOT NULL,
  `p2` int(11) unsigned NOT NULL,
  `p2n` int(11) unsigned NOT NULL,
  `p3` int(11) unsigned NOT NULL,
  `p3n` int(11) unsigned NOT NULL,
  `p4` int(11) unsigned NOT NULL,
  `p4n` int(11) unsigned NOT NULL,
  `p5` int(11) unsigned NOT NULL,
  `p5n` int(11) unsigned NOT NULL,
  `p6` int(11) unsigned NOT NULL,
  `p6n` int(11) unsigned NOT NULL,
  `all` int(11) unsigned NOT NULL,
  `pool` int(11) unsigned NOT NULL,
  `address` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;
