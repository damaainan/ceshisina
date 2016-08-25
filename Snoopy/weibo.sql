/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : caiji

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2016-08-25 15:50:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for weibo
-- ----------------------------
DROP TABLE IF EXISTS `weibo`;
CREATE TABLE `weibo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(64) DEFAULT NULL COMMENT '微博作者',
  `createTime` datetime DEFAULT NULL COMMENT '发表时间',
  `content` mediumtext COMMENT '微博内容',
  `media` varchar(2048) DEFAULT NULL COMMENT '媒体内容',
  `isOriginal` tinyint(1) DEFAULT NULL COMMENT '是否原创0否 转发  1是原创',
  `rAuthor` varchar(64) DEFAULT NULL COMMENT '转发微博的作者',
  `rCreateTime` datetime DEFAULT NULL COMMENT '转发内容的发布时间',
  `rContent` mediumtext COMMENT '转发的内容',
  `rMedia` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
