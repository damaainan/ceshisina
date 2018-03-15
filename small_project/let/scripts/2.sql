CREATE TABLE `lottery_let_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `turn` char(10) NOT NULL COMMENT '期号 如 18022',
  `pdate` datetime NOT NULL COMMENT '开奖日期',
  `link` varchar(100) NOT NULL COMMENT '详情链接',
  `status` tinyint(1) unsigned NOT NULL COMMENT '0 未 1 已完成',
  `create_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1658 DEFAULT CHARSET=utf8;