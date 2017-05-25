INSERT IGNORE INTO `#__cf_filtertypes` (`id`, `type`) VALUES
(9, 'color_btn_sinlge'),
(10, 'color_btn_multi'),
(11, 'button_single'),
(12, 'button_multi');

ALTER IGNORE TABLE `#__cf_customfields` ADD `data_type` VARCHAR( 24 ) NOT NULL DEFAULT 'string';