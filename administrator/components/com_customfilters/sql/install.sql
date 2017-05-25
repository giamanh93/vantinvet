CREATE TABLE IF NOT EXISTS `#__cf_customfields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) NOT NULL,
  `vm_custom_id` int(11) NOT NULL COMMENT 'is the key to the custom field id ',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '3' COMMENT 'The display type',
  `order_by` varchar(64) NOT NULL DEFAULT 'custom_title' COMMENT 'The way that the values will be displayed',
  `order_dir` varchar(12) NOT NULL DEFAULT 'ASC' COMMENT 'the direction',
  `params` text NOT NULL,
   `data_type` varchar(12) NOT NULL DEFAULT 'string', 
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_custom_id` (`vm_custom_id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__cf_filtertypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__cf_filtertypes`
--

INSERT IGNORE INTO `#__cf_filtertypes` (`id`, `type`) VALUES
(1, 'select'),
(2, 'radio'),
(3, 'checkbox'),
(4, 'link'),
(5, 'range_inputs'),
(6, 'range_slider'),
(8, 'range_calendars'),
(9, 'color_btn_sinlge'),
(10, 'color_btn_multi'),
(11, 'button_single'),
(12, 'button_multi');


