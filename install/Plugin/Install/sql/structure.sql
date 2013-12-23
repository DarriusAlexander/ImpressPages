
ALTER DATABASE  `[[[[database]]]]` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_content_element`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_content_element` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `row_number` double NOT NULL DEFAULT '0',
  `parent` int(11) DEFAULT NULL,
  `button_title` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `html` mediumtext,
  `page_title` mediumtext,
  `keywords` mediumtext,
  `description` mediumtext,
  `url` varchar(255) DEFAULT NULL,
  `dynamic_modules` mediumtext,
  `last_modified` timestamp NULL DEFAULT NULL,
  `modify_track1` timestamp NULL DEFAULT NULL,
  `modify_track2` timestamp NULL DEFAULT NULL,
  `modify_track3` timestamp NULL DEFAULT NULL,
  `modify_frequency` int(11) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cached_html` mediumtext,
  `cached_text` mediumtext COMMENT 'mainly for search purposes',
  `type` varchar(255) NOT NULL DEFAULT 'default',
  `redirect_url` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- Table structure

DROP TABLE IF EXISTS `ip_cms_language`;

-- Table structure


CREATE TABLE IF NOT EXISTS `ip_cms_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `d_short` varchar(255) NOT NULL DEFAULT '',
  `d_long` varchar(255) NOT NULL DEFAULT '',
  `row_number` double NOT NULL DEFAULT '0',
  `visible` int(1) NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL,
  `text_direction` varchar(10) NOT NULL DEFAULT 'ltr',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_log`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `level` varchar(255) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `context` mediumtext,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `message` (`message`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_module`;
   
-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `row_number` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `translation` varchar(255) NOT NULL,
  `managed` tinyint(1) NOT NULL DEFAULT '1',
  `version` decimal(10,2) NOT NULL,
  `core` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_module_group`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_module_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `row_number` int(11) NOT NULL DEFAULT '0',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `translation` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_module_permission`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_module_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_to_module_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_administrator_email_queue`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_administrator_email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` mediumtext NOT NULL,
  `to` varchar(255) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `from` varchar(255) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `immediate` tinyint(1) NOT NULL DEFAULT '0',
  `html` tinyint(1) NOT NULL,
  `send` timestamp NULL DEFAULT NULL,
  `lock` varchar(32) DEFAULT NULL,
  `locked_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `files` mediumtext,
  `file_names` mediumtext,
  `file_mime_types` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_administrator_repository_file`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_administrator_repository_file` (
  `fileId` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL COMMENT 'Module group and module key which uses file resource. Eg. standard/content_management',
  `instanceId` int(11) NOT NULL COMMENT 'Unique identificator. Tells in which part of the module the file is used. Teoretically there could be two identical records. The same module binds the same file to the same instance. For example: gallery widget adds the same photo twice.',
  `date` int(11) NOT NULL COMMENT 'Date, when this module started to use this resource.',
  PRIMARY KEY (`fileId`),
  KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='File usage table. Track which modules uses which files' AUTO_INCREMENT=7 ;


-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_administrator_repository_reflection`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_administrator_repository_reflection` (
  `reflectionId` int(11) NOT NULL AUTO_INCREMENT,
  `transformFingerprint` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'unique cropping options key',
  `original` varchar(255) NOT NULL,
  `reflection` varchar(255) NOT NULL COMMENT 'Cropped version of original file.',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`reflectionId`),
  KEY `transformFingerprint` (`transformFingerprint`,`original`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Cropped versions of original image file' AUTO_INCREMENT=1 ;


-- Table structure
  
DROP TABLE IF EXISTS `ip_cms_m_content_management_widget`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_content_management_widget` (
  `widgetId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `layout` varchar(25) NOT NULL,
  `data` text NOT NULL,
  `created` int(11) NOT NULL,
  `recreated` int(11) DEFAULT NULL COMMENT 'when last time the images were cropped freshly :)',
  `predecessor` int(11) DEFAULT NULL COMMENT 'Id of other widget that was duplicated to create this widget',
  PRIMARY KEY (`widgetId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54 ;

-- Table structure



DROP TABLE IF EXISTS `ip_cms_m_content_management_widget_instance`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_content_management_widget_instance` (
  `instanceId` int(11) NOT NULL AUTO_INCREMENT,
  `revisionId` int(11) NOT NULL,
  `widgetId` int(11) NOT NULL,
  `position` double NOT NULL,
  `blockName` varchar(25) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `created` int(11) NOT NULL COMMENT 'unix timestamp',
  `deleted` int(11) DEFAULT NULL COMMENT 'unix timestamp',
  PRIMARY KEY (`instanceId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1974 ;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_design` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme` (`theme`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_developer_widget_sort`;

-- Table structure


CREATE TABLE IF NOT EXISTS `ip_cms_m_developer_widget_sort` (
  `sortId` int(11) NOT NULL AUTO_INCREMENT,
  `widgetName` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'used for maintenance only',
  PRIMARY KEY (`sortId`),
  UNIQUE KEY `widgetName` (`widgetName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_inline_value_global`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_inline_value_global` (
  `module` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`module`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_inline_value_language`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_inline_value_language` (
  `module` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `languageId` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`module`,`key`,`languageId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_m_inline_value_page`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_m_inline_value_page` (
  `module` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `languageId` int(11) NOT NULL,
  `zoneName` varchar(30) NOT NULL,
  `pageId` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`module`,`key`,`languageId`,`zoneName`,`pageId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- Table structure

DROP TABLE IF EXISTS `ip_cms_page_layout`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_page_layout` (
  `group_name` varchar(128) NOT NULL,
  `module_name` varchar(128) NOT NULL,
  `page_id` int(11) unsigned NOT NULL,
  `layout` varchar(255) NOT NULL,
  UNIQUE KEY `group_name` (`group_name`,`module_name`,`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Custom page layouts';

-- Table structure

DROP TABLE IF EXISTS `ip_cms_parameter`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_parameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `row_number` int(11) NOT NULL DEFAULT '0',
  `regexpression` varchar(100) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `translation` varchar(255) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_parameter_group`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_parameter_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `module_id` int(11) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `row_number` int(11) NOT NULL DEFAULT '0',
  `translation` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_par_bool`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_par_bool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` tinyint(1) DEFAULT NULL,
  `parameter_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_par_integer`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_par_integer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) DEFAULT NULL,
  `parameter_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_par_lang`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_par_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation` mediumtext NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `parameter_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_par_string`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_par_string` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` mediumtext NOT NULL,
  `parameter_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;


-- Table structure

DROP TABLE IF EXISTS `ip_cms_plugin`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_plugin` (
  `name` varchar(30) NOT NULL,
  `version` decimal(10,2) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_revision`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_revision` (
  `revisionId` int(11) NOT NULL AUTO_INCREMENT,
  `zoneName` varchar(25) NOT NULL,
  `pageId` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`revisionId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_storage`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_storage` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin` varchar(40) NOT NULL,
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pluginkey` (`plugin`,`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_user`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `pass` varchar(32) NOT NULL DEFAULT '',
  `wrong_logins` int(11) NOT NULL DEFAULT '0',
  `blocked` tinyint(1) NOT NULL DEFAULT '0',
  `e_mail` varchar(255) NOT NULL DEFAULT '',
  `row_number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_user_to_mod`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_user_to_mod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0',
  `module_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_variables`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'module group and name',
  `value` text,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_zone`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `row_number` int(11) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL DEFAULT '',
  `template` varchar(255) DEFAULT NULL,
  `translation` varchar(255) NOT NULL,
  `associated_group` varchar(255) DEFAULT NULL,
  `associated_module` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_zone_parameter`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_zone_parameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` mediumtext,
  `keywords` mediumtext,
  `title` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `translation` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Table structure

DROP TABLE IF EXISTS `ip_cms_zone_to_content`;

-- Table structure

CREATE TABLE IF NOT EXISTS `ip_cms_zone_to_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `element_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='tells how mutch elements have language' ;

