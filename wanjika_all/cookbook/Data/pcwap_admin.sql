/*
Navicat MySQL Data Transfer

Source Server         : wamp
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : pcwap

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2016-04-26 14:57:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pcwap_admin
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_admin`;
CREATE TABLE `pcwap_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `status` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_admin
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_ads
-- ----------------------------
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_ads
-- ----------------------------
INSERT INTO `pcwap_ads` VALUES ('1', '', '/Uploads/mid/571f10f912246.png', '这是一个幻灯片在后台可以修改', '1', '1461653740', 'home');

-- ----------------------------
-- Table structure for pcwap_book
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_book
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_cate
-- ----------------------------
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_cate
-- ----------------------------
INSERT INTO `pcwap_cate` VALUES ('1', '关于我们', 'guanyuwomen', '', '', '', '<div class=\"x12 padding-large-top padding-large-bottom\" style=\"margin:0px;padding:30px 0px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n	<span class=\" x1 icon-headphones text-large text-gray\" style=\"font-style:inherit;font-weight:inherit;font-size:24px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#999999;\"></span> \r\n	<div class=\"x11 padding-left\" style=\"margin:0px;padding:0px 0px 0px 10px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n		<span class=\"text-big text-black \" style=\"font-style:inherit;font-weight:inherit;font-size:16px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#000000;\">自由舒适</span> \r\n		<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n			简约风格的空间，年轻活力的伙伴儿，Macbook、Beats、HHKB，写满梦想的白板，午后的阳光，惬意的音乐… 在舒适的空间自由呼吸。\r\n		</p>\r\n	</div>\r\n</div>\r\n<p>\r\n	<span class=\" x1 icon-smile-o text-large text-gray\" style=\"font-style:inherit;font-weight:inherit;font-size:24px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#999999;\"></span> \r\n	<p>\r\n		<span class=\"text-big text-black \" style=\"font-style:inherit;font-weight:inherit;font-size:16px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#000000;\">“议会”制度</span> \r\n		<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n			我们致力于打破「东诚信息」的企业边界，让员工、客户、合作伙伴、供应商通过“议会”制度进行提案制定规则，影响「东诚信息」的管理和业务，共同创造价值。\r\n		</p>\r\n		<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n			<span style=\"color:#000000;font-family:inherit;font-size:16px;font-style:inherit;font-weight:inherit;line-height:inherit;background-color:#FFFFFF;\">工匠精神</span>\r\n		</p>\r\n		<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n			<span style=\"font-family:inherit;font-style:inherit;font-weight:inherit;line-height:1.5;background-color:#FFFFFF;\">不断雕琢客户的产品，不断改善自己的服务，享受产品和服务在双手中升华的过程。对细节有很高的要求，追求完美和极致。对精品有着执着的坚持，其利虽微，却忠于内心。</span>\r\n		</p>\r\n	</p>\r\n</p>\r\n<div class=\"x4 float-right\" style=\"margin:0px;padding:0px;border:0px;font-size:14px;font-family:\'Microsoft YaHei\', simsun, \'Helvetica Neue\', Arial, Helvetica, sans-serif;vertical-align:baseline;color:#333333;background-color:#FFFFFF;\">\r\n	<div class=\"x12 padding-large-top padding-large-bottom\" style=\"margin:0px;padding:30px 0px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n		<span class=\" x1 icon-paper-plane-o text-large text-gray\" style=\"font-style:inherit;font-weight:inherit;font-size:24px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#999999;\"></span> \r\n		<div class=\"x11 padding-left\" style=\"margin:0px;padding:0px 0px 0px 10px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n			<span class=\"text-big text-black \" style=\"font-style:inherit;font-weight:inherit;font-size:16px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#000000;\">极致效率</span> \r\n			<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n				勤奋严谨的思考，严于律己的伙伴，短小精悍的流程，先进的生产力工具：Base、Asana、Xero、Harvest、Slack、Code Climate、Adobe Assets ... ，抓住每个提升效率的微小机会。\r\n			</p>\r\n		</div>\r\n	</div>\r\n	<div class=\"x12 padding-large-top padding-large-bottom\" style=\"margin:0px;padding:30px 0px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n		<span class=\" x1 icon-phone text-large text-gray\" style=\"font-style:inherit;font-weight:inherit;font-size:24px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#999999;\"></span> \r\n		<div class=\"x11 padding-left\" style=\"margin:0px;padding:0px 0px 0px 10px;border:0px;font-style:inherit;font-weight:inherit;font-size:inherit;font-family:inherit;vertical-align:baseline;\">\r\n			<span class=\"text-big text-black \" style=\"font-style:inherit;font-weight:inherit;font-size:16px;line-height:inherit;font-family:inherit;vertical-align:baseline;color:#000000;\">联系我们</span> \r\n			<p class=\"text-gray text-small\" style=\"font-style:inherit;font-weight:inherit;font-size:12px;font-family:inherit;vertical-align:baseline;color:#999999;\">\r\n				江西东诚信息科技有限公司<br />\r\n电话： 0797-8185699 (工作日10:00-19:30)<br />\r\n邮箱： bd@jxdcit.com<br />\r\n地址： 江西赣州兴国路18号财智广场B栋1112\r\n			</p>\r\n		</div>\r\n	</div>\r\n</div>', '', '', '20', '', '3', '0', '99', '1', '');
INSERT INTO `pcwap_cate` VALUES ('2', '产品中心', 'chanpinzhongxin', '', '', '', '', '', '', '20', '', '2', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('3', '新闻动态', 'xinwendongtai', '', '', '', '', '', '', '20', '', '1', '0', '100', '1', '');
INSERT INTO `pcwap_cate` VALUES ('4', '联系我们', 'lianxiwomen', '', '', '', '<p>\r\n	<br />\r\n</p>\r\n<p>\r\n	江西东诚信息科技有限公司&nbsp;\r\n</p>\r\n<p>\r\n	电话： 0797-8185699 (工作日10:00-19:30)\r\n</p>\r\n<p>\r\n	&nbsp;邮箱： bd@jxdcit.com&nbsp;\r\n</p>\r\n<p>\r\n	地址： 江西赣州兴国路18号财智广场B栋1112\r\n</p>', '', '', '20', '', '3', '0', '100', '1', '');

-- ----------------------------
-- Table structure for pcwap_diy
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_diy
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_info
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_info
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_links
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_links
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_search
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_search
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_tags
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_tags`;
CREATE TABLE `pcwap_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `hits` int(10) unsigned DEFAULT '1',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_tags
-- ----------------------------

-- ----------------------------
-- Table structure for pcwap_weixin_appid
-- ----------------------------
DROP TABLE IF EXISTS `pcwap_weixin_appid`;
CREATE TABLE `pcwap_weixin_appid` (
  `appid` varchar(255) NOT NULL DEFAULT '',
  `secret` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_weixin_appid
-- ----------------------------
INSERT INTO `pcwap_weixin_appid` VALUES ('465465', '654654s', '1');

-- ----------------------------
-- Table structure for pcwap_weixin_gz
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_weixin_gz
-- ----------------------------
INSERT INTO `pcwap_weixin_gz` VALUES ('1', '1', '1', '3', '欢迎关注我们', null);

-- ----------------------------
-- Table structure for pcwap_weixin_huifu
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_weixin_huifu
-- ----------------------------
INSERT INTO `pcwap_weixin_huifu` VALUES ('1', '你好', '1', null, null, '你好，你的信息我们已收到', null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('2', '最新', '2', null, null, '', null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('3', '热门', '2', null, null, null, null);
INSERT INTO `pcwap_weixin_huifu` VALUES ('4', 'pcwap', '2', '6', '3', '', null);

-- ----------------------------
-- Table structure for pcwap_weixin_menu
-- ----------------------------
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

-- ----------------------------
-- Records of pcwap_weixin_menu
-- ----------------------------
