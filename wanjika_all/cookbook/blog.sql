/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50045
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50045
File Encoding         : 65001

Date: 2017-02-14 15:15:23
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `pcwap_admin`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_admin`;
CREATE TABLE `pcwap_admin` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `status` smallint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_admin
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_ads`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_ads`;
CREATE TABLE `pcwap_ads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `adurl` varchar(255) default NULL,
  `adpic` varchar(255) default NULL,
  `adremak` varchar(255) default NULL,
  `isshow` smallint(1) unsigned default '1',
  `time` varchar(255) default NULL,
  `cate` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_ads
-- ----------------------------
INSERT INTO pcwap_ads VALUES ('1', '', '/Uploads/mid/589c1d99e25e2.jpg', '这是一个幻灯片在后台可以修改', '1', '1461653740', 'home');

-- ----------------------------
-- Table structure for `pcwap_book`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_book`;
CREATE TABLE `pcwap_book` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `tel` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `content` varchar(255) default NULL,
  `reply` varchar(255) default NULL,
  `status` tinyint(1) default '0',
  `infoid` int(11) default NULL,
  `time` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_book
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_cate`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_cate`;
CREATE TABLE `pcwap_cate` (
  `id` int(11) NOT NULL auto_increment,
  `catename` varchar(255) default NULL,
  `url` varchar(255) NOT NULL,
  `catetitle` varchar(255) default NULL,
  `catekey` varchar(255) default NULL,
  `catedesc` varchar(255) default NULL,
  `content` text NOT NULL,
  `catetemp` varchar(255) default NULL,
  `infotemp` varchar(255) default NULL,
  `catepage` int(4) unsigned default NULL,
  `catelogo` varchar(255) default NULL,
  `catetype` int(10) unsigned default '0',
  `pid` int(10) default '0',
  `sort` int(6) default '100',
  `menu` smallint(1) unsigned NOT NULL default '1',
  `outurl` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_cate
-- ----------------------------
INSERT INTO pcwap_cate VALUES ('1', '京酱肉丝', 'guanyuwomen', '', '', '', '关于我们在后台修改', '', '', '20', '', '3', '0', '99', '1', '');
INSERT INTO pcwap_cate VALUES ('2', '宫保鸡丁', 'chanpinzhongxin', '', '', '', '', '', '', '20', '', '2', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('3', '番茄炒蛋', 'xinwendongtai', '', '', '', '', '', '', '20', '', '1', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('4', '水煮鱼', 'lianxiwomen', '', '', '', '联系我们在后台修改', '', '', '20', '', '3', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('5', 'test', 'test', '', '', '', '撒饿他告诉公司的set<br />', '', '', '20', '', '3', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('6', '我的菜谱', 'wodecaipu', '', '', '', '', '', '', '20', '', '2', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('8', '创建菜谱', 'chuangjiancaipu', '', '', '', '', '', '', '20', '', '1', '0', '100', '1', '');

-- ----------------------------
-- Table structure for `pcwap_cookbook`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_cookbook`;
CREATE TABLE `pcwap_cookbook` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `coverpic_url` varchar(255) NOT NULL,
  `name` varchar(30) NOT NULL,
  `introduction` varchar(100) default NULL,
  `materials` text NOT NULL,
  `cook_step` text NOT NULL,
  `tips` varchar(100) default NULL,
  `userid` varchar(30) NOT NULL,
  `share_url` varchar(100) default NULL,
  `is_display` smallint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of pcwap_cookbook
-- ----------------------------
INSERT INTO pcwap_cookbook VALUES ('1', '/Uploads/recipe/589d66648a795.png', 'fanqiechaodan', 'haochi', '{\"1\":[\"xihongshi\",\"1ge\"],\"2\":[\"egg\",\"1mei\"]}', '{\"1\":{\"step\":\"step1\",\"src\":\"\\/Uploads\\/recipe\\/589d667e70bf0.jpg\"},\"2\":{\"step\":\"step2\",\"src\":\"\\/Uploads\\/recipe\\/589d668a0548b.jpg\"},\"3\":{\"step\":\"step3\",\"src\":\"\\/Uploads\\/recipe\\/589d669383a34.jpg\"}}', 'my tips', 'aaaaaaaa', 'testurl', '1');

-- ----------------------------
-- Table structure for `pcwap_diy`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_diy`;
CREATE TABLE `pcwap_diy` (
  `id` int(5) NOT NULL auto_increment,
  `cid` int(5) NOT NULL,
  `diykey` varchar(20) NOT NULL,
  `value` varchar(255) default ' ',
  `status` smallint(1) NOT NULL default '1',
  `pid` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_diy
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_info`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_info`;
CREATE TABLE `pcwap_info` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) default '',
  `key` varchar(255) default NULL,
  `desc` varchar(255) default NULL,
  `content` text NOT NULL,
  `cid` int(4) unsigned NOT NULL,
  `istop` smallint(1) unsigned default '0',
  `isrec` smallint(1) unsigned default '0',
  `isshow` smallint(1) unsigned default '0',
  `isrev` smallint(1) default '0',
  `ispic` smallint(1) default '0',
  `pic` varchar(255) default NULL,
  `hits` int(11) unsigned default '0',
  `author` varchar(255) default NULL,
  `price` varchar(20) default NULL,
  `revs` int(11) unsigned default '0',
  `pcs` varchar(255) default NULL,
  `time` varchar(20) default NULL,
  `temp` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_info
-- ----------------------------
INSERT INTO pcwap_info VALUES ('1', '234', '', '', '456', '456', '2', '0', '0', '1', '0', '1', '345', '2', 'PCWAP手机建站系统', '', '0', '', '2017-01-12 16:45:20', '');

-- ----------------------------
-- Table structure for `pcwap_links`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_links`;
CREATE TABLE `pcwap_links` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) default NULL,
  `key` varchar(255) default NULL,
  `remak` varchar(255) default NULL,
  `sort` int(10) unsigned default NULL,
  `isshow` smallint(1) default '1',
  `time` varchar(255) default NULL,
  `pic` varchar(255) NOT NULL,
  `cate` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_links
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_search`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_search`;
CREATE TABLE `pcwap_search` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(255) default NULL,
  `key` varchar(255) default NULL,
  `hits` int(11) default NULL,
  `status` tinyint(1) default '0',
  `time` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_search
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_tags`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_tags`;
CREATE TABLE `pcwap_tags` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(11) default NULL,
  `tags` varchar(255) default NULL,
  `hits` int(10) unsigned default '1',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_tags
-- ----------------------------

-- ----------------------------
-- Table structure for `pcwap_weixin_appid`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_weixin_appid`;
CREATE TABLE `pcwap_weixin_appid` (
  `appid` varchar(255) NOT NULL default '',
  `secret` varchar(255) NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_weixin_appid
-- ----------------------------
INSERT INTO pcwap_weixin_appid VALUES ('465465', '654654s', '1');

-- ----------------------------
-- Table structure for `pcwap_weixin_gz`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_weixin_gz`;
CREATE TABLE `pcwap_weixin_gz` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `cid` int(11) default NULL,
  `leixin` tinyint(1) NOT NULL default '1',
  `num` tinyint(1) default NULL,
  `text` varchar(255) default NULL,
  `sort` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_weixin_gz
-- ----------------------------
INSERT INTO pcwap_weixin_gz VALUES ('1', '1', '1', '3', '欢迎关注我们', null);

-- ----------------------------
-- Table structure for `pcwap_weixin_huifu`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_weixin_huifu`;
CREATE TABLE `pcwap_weixin_huifu` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `key` varchar(255) default NULL,
  `leixin` smallint(1) default '0',
  `cid` int(10) default NULL,
  `num` smallint(1) default NULL,
  `text` varchar(255) default NULL,
  `sort` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_weixin_huifu
-- ----------------------------
INSERT INTO pcwap_weixin_huifu VALUES ('1', '你好', '1', null, null, '你好，你的信息我们已收到', null);
INSERT INTO pcwap_weixin_huifu VALUES ('2', '最新', '2', null, null, '', null);
INSERT INTO pcwap_weixin_huifu VALUES ('3', '热门', '2', null, null, null, null);
INSERT INTO pcwap_weixin_huifu VALUES ('4', 'pcwap', '2', '6', '3', '', null);

-- ----------------------------
-- Table structure for `pcwap_weixin_menu`
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_weixin_menu`;
CREATE TABLE `pcwap_weixin_menu` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `leixin` tinyint(1) default '2',
  `url` varchar(255) default NULL,
  `keys` varchar(255) default NULL,
  `pid` int(11) default NULL,
  `sort` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_weixin_menu
-- ----------------------------
