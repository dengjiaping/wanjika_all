/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50045
Source Host           : localhost:3306
Source Database       : wap

Target Server Type    : MYSQL
Target Server Version : 50045
File Encoding         : 65001

Date: 2017-03-24 14:30:59
*/

SET FOREIGN_KEY_CHECKS=0;
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pcwap_cate
-- ----------------------------
INSERT INTO pcwap_cate VALUES ('1', '关于我们', 'guanyuwomen', '', '', '', '关于我们在后台修改', '', '', '20', '', '3', '0', '99', '1', '');
INSERT INTO pcwap_cate VALUES ('2', '产品中心', 'chanpinzhongxin', '', '', '', '', '', '', '20', '', '2', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('3', '新闻动态', 'xinwendongtai', '', '', '', '', '', '', '20', '', '1', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('4', '联系我们', 'lianxiwomen', '', '', '', '联系我们在后台修改', '', '', '20', '', '3', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('5', '我的菜谱', 'wodecaipu', '', '', '', '', '', '', '20', '', '2', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('6', '创建菜谱', 'chuangjiancaipu', '', '', '', '', '', '', '20', '/Uploads/mid/589ad7d3ba4be.jpg', '1', '0', '100', '1', '');
INSERT INTO pcwap_cate VALUES ('7', '我的草稿', 'wodecaogao', null, null, null, '', null, null, '20', null, '2', '0', '100', '1', null);
