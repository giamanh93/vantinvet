-- Basic table from 1.0
CREATE TABLE IF NOT EXISTS `#__jmap` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`type` varchar(100) NOT NULL,
  	`name` text NOT NULL,
  	`description` text NOT NULL,
  	`checked_out` int(11) unsigned NOT NULL default '0',
  	`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  	`published` tinyint(1) NOT NULL default '0',
  	`ordering` int(11) NOT NULL default '0',
  	`sqlquery` text NULL,
  	`sqlquery_managed` text NULL,
  	`params` text NOT NULL,
  	PRIMARY KEY  (`id`),
  	KEY `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

INSERT INTO `#__jmap` (`id`, `type`, `name`, `description`, `checked_out`, `checked_out_time`, `published`, `ordering`, `sqlquery`, `sqlquery_managed`, `params`) VALUES (1, 'content', 'Content', 'Default contents source', 0, '0000-00-00 00:00:00', 1, 1, '', '', '') ON DUPLICATE KEY UPDATE `id` = 1;

-- Updates on version 2.0
CREATE TABLE IF NOT EXISTS `#__jmap_pingomatic` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` VARCHAR( 255 ) NOT NULL ,
	`blogurl` VARCHAR( 255 ) NOT NULL ,
	`rssurl` VARCHAR( 255 ) NULL ,
	`services` TEXT NOT NULL ,
	`lastping` DATETIME NULL ,
	`checked_out` INT NOT NULL DEFAULT  '0',
	`checked_out_time` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00'
) ENGINE=InnoDB CHARACTER SET `utf8` ;

-- Updates on version 2.1
CREATE TABLE IF NOT EXISTS `#__jmap_menu_priorities` (
	`id` INT NOT NULL ,
	`priority` CHAR( 3 ) NOT NULL ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

-- Updates on version 3.0
CREATE TABLE IF NOT EXISTS `#__jmap_datasets` (
	`id` int(11) unsigned NOT NULL auto_increment,
  	`name` text NOT NULL,
  	`description` text NOT NULL,
  	`checked_out` int(11) unsigned NOT NULL default '0',
  	`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  	`published` tinyint(1) NOT NULL default '0',
  	`sources` text NOT NULL,
  	PRIMARY KEY  (`id`),
  	KEY `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

CREATE TABLE IF NOT EXISTS `#__jmap_dss_relations` (
	`datasetid` int(11) NOT NULL,
	`datasourceid` int(11) NOT NULL,
  PRIMARY KEY (`datasetid`, `datasourceid`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

CREATE TABLE IF NOT EXISTS `#__jmap_cats_priorities` (
	`id` INT NOT NULL ,
	`priority` CHAR( 3 ) NOT NULL ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8` ; 

-- Updates on version 3.1
CREATE TABLE IF NOT EXISTS `#__jmap_google` (
	`id` INT NOT NULL, 
	`token` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Updates on version 3.2
CREATE TABLE IF NOT EXISTS `#__jmap_metainfo` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`linkurl` varchar(600) NOT NULL,
	`meta_title` text NULL,
  	`meta_desc` text NULL,
  	`meta_image` VARCHAR(255) NULL,
  	`robots` varchar(255) NULL,
  	`published` tinyint(1) NOT NULL default '0',
  	PRIMARY KEY (`id`),
  	INDEX `linkurl` (`linkurl`),
  	INDEX `robots` (`robots`),
  	INDEX `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8`;

-- Exceptions queries in reverse versioning order 10.0 -> 1.0