/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : mi_metrics

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2015-05-26 14:03:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for metric_data
-- ----------------------------
DROP TABLE IF EXISTS `metric_data`;
CREATE TABLE `metric_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `providerId` int(11) DEFAULT NULL,
  `promoCode` varchar(255) NOT NULL,
  `cost` varchar(255) NOT NULL,
  `impressions` varchar(255) NOT NULL,
  `clicks` varchar(255) NOT NULL,
  `conversions` varchar(255) NOT NULL,
  `lastUpdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of metric_data
-- ----------------------------

-- ----------------------------
-- Table structure for provider
-- ----------------------------
DROP TABLE IF EXISTS `provider`;
CREATE TABLE `provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `providerName` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of provider
-- ----------------------------
INSERT INTO `provider` VALUES ('1', 'Facebook');
