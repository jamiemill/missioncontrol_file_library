DROP TABLE IF EXISTS `file_library_files`;
CREATE TABLE `file_library_files` (
  `id` varchar(36) NOT NULL,
  `slug` varchar(255) default NULL,
  `filename` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `type` varchar(16) default NULL,
  `file_library_folder_id` int(8) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `is_smart` int(1) default '0',
  `deleted` int(1) default '0',
  `deleted_date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `file_library_folders`;
CREATE TABLE `file_library_folders` (
  `id` varchar(36) NOT NULL,
  `name` varchar(255) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `is_smart` int(1) default '0',
  `deleted` int(1) default '0',
  `deleted_date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
