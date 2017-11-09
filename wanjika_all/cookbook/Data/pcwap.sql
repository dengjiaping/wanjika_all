
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `pcwap_admin`;
CREATE TABLE `pcwap_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `status` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pcwap_ads`;
CREATE TABLE `pcwap_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adurl` varchar(255) DEFAULT NULL,
  `adpic` varchar(255) DEFAULT NULL,
  `adremak` varchar(255) DEFAULT NULL,
  `isshow` smallint(1) unsigned DEFAULT '1',
  `time` varchar(255) DEFAULT NULL,
  `cate` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


INSERT INTO `pcwap_ads` VALUES ('1', 'http://www.pcwap.cn', 'http://img30.360buyimg.com/mobilecms/s480x180_jfs/t1291/46/1094796668/102025/7b7f1db4/55b60172Na1ba5537.jpg', '', '1', '1438039371', 'home');
INSERT INTO `pcwap_ads` VALUES ('2', '', 'http://img30.360buyimg.com/mobilecms/s480x180_jfs/t1447/194/990426789/112874/949006c4/55b5cdacNbaf4b81d.jpg', '', '1', '1438039386', 'home');
INSERT INTO `pcwap_ads` VALUES ('3', '', 'http://img30.360buyimg.com/mobilecms/s480x180_jfs/t1294/109/1004680350/420595/d8aa8df9/55b209dcNddd15150.jpg', '', '1', '1438039405', 'home');


DROP TABLE IF EXISTS `pcwap_book`;
CREATE TABLE `pcwap_book` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `reply` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `infoid` int(11) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_cate`;
CREATE TABLE `pcwap_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catename` varchar(255) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `catetitle` varchar(255) DEFAULT NULL,
  `catekey` varchar(255) DEFAULT NULL,
  `catedesc` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `catetemp` varchar(255) DEFAULT NULL,
  `infotemp` varchar(255) DEFAULT NULL,
  `catepage` int(4) unsigned DEFAULT NULL,
  `catelogo` varchar(255) DEFAULT NULL,
  `catetype` int(10) unsigned DEFAULT '0',
  `pid` int(10) DEFAULT '0',
  `sort` int(6) DEFAULT '100',
  `menu` smallint(1) unsigned NOT NULL DEFAULT '1',
  `outurl` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


INSERT INTO `pcwap_cate` VALUES ('1', '蛋糕', 'dangao', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bc7ee5111.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('2', '面包', 'mianbao', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bc88cfc3b.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('3', '巧克力', 'qiaokeli', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bc947a198.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('4', '简餐', 'jiancan', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bcb649391.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('5', '咖啡', 'kafei', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bccb1b910.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('6', '套餐', 'taocan', '', '', '', '', '', '', '20', '/Uploads/mid/55b6bcda5dafd.png', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('7', '联系我们', 'lianxiwomen', '', '', '', '<p>\r\n	联系方式\r\n</p>\r\n<p>\r\n	', '', '', '20', '', '3', '0', '100', '0', '');


DROP TABLE IF EXISTS `pcwap_diy`;
CREATE TABLE `pcwap_diy` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `cid` int(5) NOT NULL,
  `diykey` varchar(20) NOT NULL,
  `value` varchar(255) DEFAULT ' ',
  `status` smallint(1) NOT NULL DEFAULT '1',
  `pid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_info`;
CREATE TABLE `pcwap_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT '',
  `key` varchar(255) DEFAULT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `cid` int(4) unsigned NOT NULL,
  `istop` smallint(1) unsigned DEFAULT '0',
  `isrec` smallint(1) unsigned DEFAULT '0',
  `isshow` smallint(1) unsigned DEFAULT '0',
  `isrev` smallint(1) DEFAULT '0',
  `ispic` smallint(1) DEFAULT '0',
  `pic` varchar(255) DEFAULT NULL,
  `hits` int(11) unsigned DEFAULT '0',
  `author` varchar(255) DEFAULT NULL,
  `price` varchar(20) DEFAULT NULL,
  `revs` int(11) unsigned DEFAULT '0',
  `pcs` varchar(255) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `temp` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_links`;
CREATE TABLE `pcwap_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `remak` varchar(255) DEFAULT NULL,
  `sort` int(10) unsigned DEFAULT NULL,
  `isshow` smallint(1) DEFAULT '1',
  `time` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `cate` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_search`;
CREATE TABLE `pcwap_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_tags`;
CREATE TABLE `pcwap_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `hits` int(10) unsigned DEFAULT '1',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pcwap_weixin_appid`;
CREATE TABLE `pcwap_weixin_appid` (
  `appid` varchar(255) NOT NULL DEFAULT '',
  `secret` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


INSERT INTO `pcwap_weixin_appid` VALUES ('465465', '654654s', '1');


DROP TABLE IF EXISTS `pcwap_weixin_gz`;
CREATE TABLE `pcwap_weixin_gz` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cid` int(11) DEFAULT NULL,
  `leixin` tinyint(1) NOT NULL DEFAULT '1',
  `num` tinyint(1) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `sort` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `pcwap_weixin_gz` VALUES ('1', '1', '1', '3', '欢迎关注我们', null);

DROP TABLE IF EXISTS `pcwap_weixin_huifu`;
CREATE TABLE `pcwap_weixin_huifu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `leixin` smallint(1) DEFAULT '0',
  `cid` int(10) DEFAULT NULL,
  `num` smallint(1) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `sort` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `pcwap_weixin_huifu` VALUES ('1', '你好', '1', null, null, '你好，你的信息我们已收到', null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('2', '最新', '2', null, null, '', null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('3', '热门', '2', null, null, null, null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('4', 'pcwap', '2', '6', '3', '', null);


DROP TABLE IF EXISTS `pcwap_weixin_menu`;
CREATE TABLE `pcwap_weixin_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `leixin` tinyint(1) DEFAULT '2',
  `url` varchar(255) DEFAULT NULL,
  `keys` varchar(255) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


