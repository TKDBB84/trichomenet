/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 50527
Source Host           : localhost:3306
Source Database       : trichomenet

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2012-09-26 12:05:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `cords`
-- ----------------------------
DROP TABLE IF EXISTS `cords`;
CREATE TABLE `cords` (
  `xCord` int(11) NOT NULL DEFAULT '0',
  `yCord` int(11) NOT NULL DEFAULT '0',
  `fk_leaf_id` int(11) unsigned NOT NULL,
  `cord_type` enum('outter','inner') NOT NULL,
  PRIMARY KEY (`xCord`,`yCord`,`fk_leaf_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

-- ----------------------------
-- Records of cords
-- ----------------------------

-- ----------------------------
-- Table structure for `genotypes`
-- ----------------------------
DROP TABLE IF EXISTS `genotypes`;
CREATE TABLE `genotypes` (
  `genotype_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `genotype` text,
  `owner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`genotype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of genotypes
-- ----------------------------

-- ----------------------------
-- Table structure for `leafs`
-- ----------------------------
DROP TABLE IF EXISTS `leafs`;
CREATE TABLE `leafs` (
  `leaf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_genotype_id` int(10) unsigned NOT NULL DEFAULT '0',
  `leaf_name` text,
  `file_name` text NOT NULL,
  `fk_shape_id` int(10) unsigned DEFAULT NULL,
  `tip_x` int(11) DEFAULT NULL,
  `tip_y` int(11) DEFAULT NULL,
  `leaf_area` int(11) DEFAULT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`leaf_id`,`file_name`(20))
) ENGINE=MyISAM AUTO_INCREMENT=301 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of leafs
-- ----------------------------

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `password` text NOT NULL,
  `org` text NOT NULL,
  `last_crypt` text NOT NULL,
  `last_active_genotype` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` (`user_id`,`email`,`name`,`password`,`org`) VALUES (0,'guest','Guest','$2a$15$/n7kgVQHZ4e8jnKytQJGuuI0NGAbLcHcMNAhM4/0uwF2gWwk.nC.e','Guest Account');
