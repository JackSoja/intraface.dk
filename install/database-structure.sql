-- phpMyAdmin SQL Dump
-- version 3.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 08, 2010 at 10:54 PM
-- Server version: 5.0.91
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `intrafac_intraface`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_account`
--

CREATE TABLE IF NOT EXISTS `accounting_account` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `number` int(11) NOT NULL default '0',
  `type_key` tinyint(2) unsigned NOT NULL default '0',
  `use_key` tinyint(2) unsigned NOT NULL default '0',
  `name` char(255) character set latin1 NOT NULL default '',
  `sum_from_account_number` int(11) NOT NULL default '0',
  `sum_to_account_number` int(11) NOT NULL default '0',
  `comment` char(255) character set latin1 NOT NULL default '',
  `vat_key` tinyint(2) NOT NULL default '0',
  `vat_percent` float(11,2) NOT NULL default '0.00',
  `active` tinyint(1) NOT NULL default '1',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_from_id` int(11) NOT NULL default '0',
  `primosaldo_debet` double(11,2) NOT NULL default '0.00',
  `primosaldo_credit` double(11,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `kontonummer` (`number`),
  KEY `user_id` (`user_id`),
  KEY `intranet_id` (`intranet_id`,`year_id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5237 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_post`
--

CREATE TABLE IF NOT EXISTS `accounting_post` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `voucher_id` int(11) NOT NULL default '0',
  `text` char(255) character set latin1 NOT NULL default '',
  `account_id` int(11) NOT NULL default '0',
  `debet` float(11,2) NOT NULL default '0.00',
  `credit` float(11,2) NOT NULL default '0.00',
  `stated` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `user_id` (`user_id`),
  KEY `year_id` (`year_id`),
  KEY `account_id` (`account_id`),
  KEY `stated` (`stated`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34209 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_vat_period`
--

CREATE TABLE IF NOT EXISTS `accounting_vat_period` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_start` date NOT NULL default '0000-00-00',
  `date_end` date NOT NULL default '0000-00-00',
  `label` char(255) character set latin1 NOT NULL default '',
  `status` tinyint(1) NOT NULL default '0',
  `voucher_id` int(11) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=140 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_voucher`
--

CREATE TABLE IF NOT EXISTS `accounting_voucher` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `date` date NOT NULL default '0000-00-00',
  `number` int(11) NOT NULL default '0',
  `text` char(255) character set latin1 NOT NULL default '',
  `reference` char(255) character set latin1 NOT NULL default '',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `intranet_id` (`intranet_id`,`year_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6744 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_voucher_file`
--

CREATE TABLE IF NOT EXISTS `accounting_voucher_file` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `voucher_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `belong_to_key` tinyint(2) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `description` char(255) character set latin1 NOT NULL default '',
  `active` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1768 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_year`
--

CREATE TABLE IF NOT EXISTS `accounting_year` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `last_year_id` int(11) NOT NULL default '0',
  `label` char(255) character set latin1 NOT NULL default '',
  `from_date` date NOT NULL default '0000-00-00',
  `to_date` date NOT NULL default '0000-00-00',
  `primosaldo_id` int(11) NOT NULL default '0',
  `locked` tinyint(1) NOT NULL default '0',
  `session_id` char(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `vat` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_year_end`
--

CREATE TABLE IF NOT EXISTS `accounting_year_end` (
  `id` int(11) NOT NULL auto_increment,
  `operating_reset_voucher_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `intranet_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `step_key` int(11) NOT NULL default '0',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `result_account_reset_voucher_id` int(11) NOT NULL default '0',
  `_old_type_key` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_year_end_action`
--

CREATE TABLE IF NOT EXISTS `accounting_year_end_action` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `debet_account_id` int(11) NOT NULL default '0',
  `credit_account_id` int(11) NOT NULL default '0',
  `amount` float(11,2) NOT NULL default '0.00',
  `voucher_id` int(11) NOT NULL default '0',
  `type_key` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=484 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_year_end_statement`
--

CREATE TABLE IF NOT EXISTS `accounting_year_end_statement` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `year_id` int(11) NOT NULL default '0',
  `account_id` int(11) NOT NULL default '0',
  `amount` float(11,2) NOT NULL default '0.00',
  `type_key` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1659 ;

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE IF NOT EXISTS `address` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `tmp_intranet_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `contactname` varchar(255) character set latin1 NOT NULL default '',
  `address` text character set latin1 NOT NULL,
  `postcode` varchar(255) character set latin1 NOT NULL default '',
  `city` varchar(255) character set latin1 NOT NULL default '',
  `country` varchar(255) character set latin1 NOT NULL default '',
  `cvr` varchar(255) character set latin1 NOT NULL default '0',
  `email` varchar(255) character set latin1 NOT NULL default '',
  `website` varchar(255) character set latin1 NOT NULL default '',
  `phone` varchar(255) character set latin1 NOT NULL default '',
  `active` int(11) NOT NULL default '0',
  `changed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `ean` varchar(255) character set latin1 NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `phone` (`phone`),
  KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `belong_to_id` (`belong_to_id`),
  KEY `type` (`type`),
  KEY `find_address` (`type`,`belong_to_id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38663 ;

-- --------------------------------------------------------

--
-- Table structure for table `basket`
--

CREATE TABLE IF NOT EXISTS `basket` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `order_id` int(11) NOT NULL default '0',
  `session_id` varchar(255) character set latin1 NOT NULL default '',
  `product_id` int(11) NOT NULL default '0',
  `product_detail_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `quantity` int(11) NOT NULL default '0',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `basketevaluation_product` int(11) NOT NULL default '0',
  `text` text character set latin1 NOT NULL,
  `shop_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date_changed` (`date_changed`),
  KEY `session_id` (`session_id`),
  KEY `product_detail_id` (`product_detail_id`,`product_variation_id`),
  KEY `intranet_id` (`intranet_id`,`product_id`,`shop_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=306823 ;

-- --------------------------------------------------------

--
-- Table structure for table `basket_details`
--

CREATE TABLE IF NOT EXISTS `basket_details` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `order_id` int(11) NOT NULL default '0',
  `session_id` varchar(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `contactperson` varchar(255) character set latin1 NOT NULL default '',
  `address` text character set latin1 NOT NULL,
  `postcode` varchar(255) character set latin1 NOT NULL default '',
  `city` varchar(255) character set latin1 NOT NULL default '',
  `country` varchar(255) character set latin1 NOT NULL default '',
  `cvr` varchar(255) character set latin1 NOT NULL default '',
  `email` varchar(255) character set latin1 NOT NULL default '',
  `phone` varchar(255) character set latin1 NOT NULL default '',
  `customer_comment` text character set latin1 NOT NULL,
  `customer_coupon` varchar(255) character set latin1 NOT NULL default '',
  `customer_ean` varchar(255) character set latin1 NOT NULL default '',
  `payment_method_key` int(11) NOT NULL default '0',
  `shop_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`shop_id`),
  KEY `order_id` (`order_id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6394 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_element`
--

CREATE TABLE IF NOT EXISTS `cms_element` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  `section_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_publish` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `shorthand` varchar(255) character set latin1 NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3991 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_page`
--

CREATE TABLE IF NOT EXISTS `cms_page` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `site_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  `child_of_id` int(11) NOT NULL default '0',
  `title` varchar(255) character set latin1 NOT NULL default '',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `navigation_name` varchar(255) character set latin1 NOT NULL default '',
  `keywords` text character set latin1 NOT NULL,
  `date_publish` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_expire` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `position` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `description` text character set latin1 NOT NULL,
  `template_id` int(11) NOT NULL default '0',
  `status_key` int(11) NOT NULL default '0',
  `allow_comments` tinyint(1) NOT NULL default '0',
  `pic_id` int(11) NOT NULL default '0',
  `password` varchar(255) character set latin1 NOT NULL default '',
  `hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `child_of_id` (`child_of_id`),
  KEY `site_id` (`site_id`,`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1492 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_parameter`
--

CREATE TABLE IF NOT EXISTS `cms_parameter` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `parameter` varchar(255) character set latin1 NOT NULL default '',
  `value` text character set latin1 NOT NULL,
  `type_key` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`belong_to_id`,`type_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18097 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_section`
--

CREATE TABLE IF NOT EXISTS `cms_section` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `template_section_id` int(11) NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3521 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_site`
--

CREATE TABLE IF NOT EXISTS `cms_site` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `url` varchar(255) character set latin1 NOT NULL default '',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_template`
--

CREATE TABLE IF NOT EXISTS `cms_template` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `for_page_type` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(4) NOT NULL default '1',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=112 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_template_section`
--

CREATE TABLE IF NOT EXISTS `cms_template_section` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `template_id` int(11) NOT NULL default '0',
  `site_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `type_key` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '1',
  `locked` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`template_id`,`site_id`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=208 ;

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) character set latin1 NOT NULL default '',
  `intranet_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `headline` varchar(255) character set latin1 NOT NULL default '',
  `text` text character set latin1 NOT NULL,
  `belong_to_id` int(11) NOT NULL default '0',
  `answer_to_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  `ip` varchar(255) character set latin1 NOT NULL default '',
  `active` int(11) NOT NULL default '1',
  `approved` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `belong_to_id` (`belong_to_id`),
  KEY `date_created` (`date_created`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=79 ;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `number` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  `paymentcondition` tinyint(2) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  `preferred_invoice` tinyint(2) NOT NULL default '0',
  `code` varchar(255) character set latin1 NOT NULL,
  `openid_url` varchar(255) character set latin1 NOT NULL default '',
  `password` varchar(255) character set latin1 NOT NULL,
  `username` varchar(255) character set latin1 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `number` (`number`),
  KEY `find contact to newsletter_subscriber` (`id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28463 ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_message`
--

CREATE TABLE IF NOT EXISTS `contact_message` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text character set latin1 NOT NULL,
  `important` tinyint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_person`
--

CREATE TABLE IF NOT EXISTS `contact_person` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `phone` varchar(255) character set latin1 NOT NULL default '',
  `mobile` varchar(255) character set latin1 NOT NULL default '',
  `email` varchar(255) character set latin1 NOT NULL default '',
  `contact_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=695 ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_reminder_single`
--

CREATE TABLE IF NOT EXISTS `contact_reminder_single` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `created_by_user_id` int(11) NOT NULL default '0',
  `reminder_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_key` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_viewed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_cancelled` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(255) character set latin1 NOT NULL default '',
  `description` text character set latin1 NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `core_translation_i18n`
--

CREATE TABLE IF NOT EXISTS `core_translation_i18n` (
  `page_id` varchar(50) character set latin1 default NULL,
  `id` text character set latin1 NOT NULL,
  `dk` text character set latin1,
  `uk` text character set latin1,
  UNIQUE KEY `i18n_page_id_id_index` (`page_id`,`id`(255)),
  KEY `i18n_page_id_index` (`page_id`),
  KEY `i18n_id_index` (`id`(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `core_translation_langs`
--

CREATE TABLE IF NOT EXISTS `core_translation_langs` (
  `id` varchar(16) character set latin1 default NULL,
  `name` varchar(200) character set latin1 default NULL,
  `meta` text character set latin1,
  `error_text` varchar(250) character set latin1 default NULL,
  `encoding` varchar(16) character set latin1 default NULL,
  UNIQUE KEY `langs_id_index` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `type_key` int(11) NOT NULL,
  `_old_deleted` int(1) NOT NULL,
  `deleted_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `currency_exchangerate`
--

CREATE TABLE IF NOT EXISTS `currency_exchangerate` (
  `id` bigint(20) NOT NULL auto_increment,
  `currency_id` bigint(20) NOT NULL,
  `used_for_key` tinyint(4) NOT NULL,
  `rate` double NOT NULL,
  `date_created` datetime NOT NULL,
  `intranet_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `currency_id_idx` (`currency_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `dbquery_result`
--

CREATE TABLE IF NOT EXISTS `dbquery_result` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `session_id` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `toplevel` int(11) NOT NULL default '0',
  `dbquery_condition` blob NOT NULL,
  `joins` blob NOT NULL,
  `keyword` blob NOT NULL,
  `first_character` varchar(255) NOT NULL default '',
  `paging` int(11) NOT NULL default '0',
  `sorting` blob NOT NULL,
  `filter` blob NOT NULL,
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`session_id`),
  KEY `clean_up_posts` (`intranet_id`,`date_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=425430 ;

-- --------------------------------------------------------

--
-- Table structure for table `debtor`
--

CREATE TABLE IF NOT EXISTS `debtor` (
  `id` int(11) NOT NULL auto_increment,
  `where_from` int(11) NOT NULL default '0',
  `where_from_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `identifier_key` varchar(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_executed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_cancelled` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_stated` date NOT NULL default '0000-00-00',
  `voucher_id` int(11) NOT NULL default '0',
  `currency_id` int(11) NOT NULL,
  `currency_product_price_exchange_rate_id` int(11) NOT NULL,
  `_old_voucher_number` varchar(255) character set latin1 NOT NULL default '',
  `this_date` date NOT NULL default '0000-00-00',
  `due_date` date NOT NULL default '0000-00-00',
  `number` int(11) NOT NULL default '0',
  `intranet_address_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `contact_address_id` int(11) NOT NULL default '0',
  `contact_person_id` int(11) NOT NULL default '0',
  `_old_attention_to` varchar(255) character set latin1 NOT NULL default '',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `status` int(11) NOT NULL default '0',
  `_old_status` int(11) NOT NULL default '0',
  `_old_status_date` date NOT NULL default '0000-00-00',
  `_old_is_credited` int(11) NOT NULL default '0',
  `_old_locked` tinyint(1) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `round_off` int(11) NOT NULL default '0',
  `payment_method` int(11) NOT NULL default '0',
  `girocode` varchar(255) character set latin1 NOT NULL default '',
  `ip` varchar(255) character set latin1 NOT NULL default '',
  `_old_is_sent` tinyint(1) NOT NULL default '0',
  `_old_payed` int(11) NOT NULL default '0',
  `_old_payed_date` date NOT NULL default '0000-00-00',
  `_old_is_sent_date` date NOT NULL default '0000-00-00',
  `active` int(1) NOT NULL default '1',
  `comment` text character set latin1 NOT NULL,
  `message` text character set latin1 NOT NULL,
  `internal_note` text character set latin1 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `contact_address_id` (`contact_address_id`),
  KEY `where_from_id` (`where_from_id`),
  KEY `number` (`number`),
  KEY `currency_id` (`currency_id`,`currency_product_price_exchange_rate_id`),
  KEY `intranet_id` (`intranet_id`,`status`,`type`),
  KEY `date_created` (`date_created`),
  KEY `find_debtor_from_item_to_get_quantity_of_product` (`id`,`intranet_id`,`date_sent`,`status`,`type`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18720 ;

-- --------------------------------------------------------

--
-- Table structure for table `debtor_item`
--

CREATE TABLE IF NOT EXISTS `debtor_item` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `debtor_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_detail_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `product_variation_detail_id` int(11) NOT NULL default '0',
  `description` text character set latin1 NOT NULL,
  `quantity` double(11,2) NOT NULL default '0.00',
  `position` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `debtor_id` (`debtor_id`),
  KEY `product_id` (`product_id`),
  KEY `product_detail_id` (`product_detail_id`),
  KEY `product_variation_id` (`product_variation_id`,`product_variation_detail_id`),
  KEY `find_quantity_of_product` (`intranet_id`,`product_id`,`product_variation_id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=54531 ;

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL auto_increment,
  `type_id` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `from_email` varchar(255) character set latin1 NOT NULL default '',
  `from_name` varchar(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_deadline` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  `intranet_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `contact_person_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `bcc_to_user` int(11) NOT NULL default '0',
  `subject` varchar(255) character set latin1 NOT NULL default '',
  `body` text character set latin1 NOT NULL,
  `error_msg` varchar(255) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `date_sent` (`date_sent`),
  KEY `belong_to_id` (`belong_to_id`),
  KEY `search_for_due_mails` (`date_deadline`,`intranet_id`,`status`,`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=121312 ;

-- --------------------------------------------------------

--
-- Table structure for table `email_attachment`
--

CREATE TABLE IF NOT EXISTS `email_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `email_id` int(11) NOT NULL default '0',
  `file_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  `filename` varchar(255) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`,`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2667 ;

-- --------------------------------------------------------

--
-- Table structure for table `filehandler_append_file`
--

CREATE TABLE IF NOT EXISTS `filehandler_append_file` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `belong_to_key` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `file_handler_id` int(11) NOT NULL default '0',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `active` int(1) NOT NULL default '1',
  `position` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`belong_to_id`,`file_handler_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7019 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_handler`
--

CREATE TABLE IF NOT EXISTS `file_handler` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `description` text character set latin1 NOT NULL,
  `file_name` varchar(100) character set latin1 NOT NULL default '',
  `server_file_name` varchar(255) character set latin1 NOT NULL default '',
  `file_size` int(11) NOT NULL default '0',
  `file_type_key` int(11) NOT NULL default '0',
  `accessibility_key` int(11) NOT NULL default '0',
  `access_key` varchar(255) character set latin1 NOT NULL default '',
  `width` int(11) default NULL,
  `height` int(11) default NULL,
  `active` int(11) NOT NULL default '1',
  `temporary` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`access_key`,`active`,`id`),
  KEY `simple_find` (`id`,`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7807 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_handler_instance`
--

CREATE TABLE IF NOT EXISTS `file_handler_instance` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `file_handler_id` int(11) NOT NULL default '0',
  `type_key` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `server_file_name` varchar(255) character set latin1 NOT NULL default '',
  `width` int(255) NOT NULL default '0',
  `height` int(255) NOT NULL default '0',
  `file_size` varchar(20) character set latin1 NOT NULL default '',
  `crop_parameter` varchar(255) character set latin1 NOT NULL default '',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`file_handler_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16242 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_handler_instance_type`
--

CREATE TABLE IF NOT EXISTS `file_handler_instance_type` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `type_key` int(11) NOT NULL default '0',
  `max_height` int(11) NOT NULL default '0',
  `max_width` int(11) NOT NULL default '0',
  `resize_type_key` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `flickr_cache`
--

CREATE TABLE IF NOT EXISTS `flickr_cache` (
  `request` varchar(35) NOT NULL default '',
  `response` mediumtext NOT NULL,
  `expiration` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `request` (`request`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ilib_category`
--

CREATE TABLE IF NOT EXISTS `ilib_category` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `belong_to` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `active` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`belong_to`,`belong_to_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=95 ;

-- --------------------------------------------------------

--
-- Table structure for table `ilib_category_append`
--

CREATE TABLE IF NOT EXISTS `ilib_category_append` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `object_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`object_id`,`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3011 ;

-- --------------------------------------------------------

--
-- Table structure for table `intraface_modules_onlinepayment__language_translation`
--

CREATE TABLE IF NOT EXISTS `intraface_modules_onlinepayment__language_translation` (
  `id` int(11) NOT NULL,
  `email` text,
  `lang` varchar(20) NOT NULL default '',
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `intranet`
--

CREATE TABLE IF NOT EXISTS `intranet` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set latin1 NOT NULL default '',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `_old_bankname` varchar(255) character set latin1 NOT NULL default '',
  `_old_regnumber` varchar(4) character set latin1 NOT NULL default '0',
  `_old_accountnumber` varchar(255) character set latin1 NOT NULL default '',
  `_old_giroaccountnumber` varchar(255) character set latin1 NOT NULL default '',
  `pdf_header_file_id` int(11) NOT NULL default '0',
  `key_code` varchar(255) character set latin1 NOT NULL default '',
  `private_key` varchar(255) character set latin1 NOT NULL default '',
  `public_key` varchar(255) character set latin1 NOT NULL default '',
  `maintained_by_user_id` int(11) NOT NULL default '0',
  `password` varchar(255) character set latin1 NOT NULL default '',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `contact_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `private_key` (`private_key`),
  KEY `public_key` (`public_key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58 ;

-- --------------------------------------------------------

--
-- Table structure for table `intranet_module_package`
--

CREATE TABLE IF NOT EXISTS `intranet_module_package` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `module_package_id` int(11) NOT NULL default '0',
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date NOT NULL default '0000-00-00',
  `order_debtor_id` int(11) NOT NULL default '0',
  `status_key` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`module_package_id`),
  KEY `order_debtor_id` (`order_debtor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payment`
--

CREATE TABLE IF NOT EXISTS `invoice_payment` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `payment_date` date NOT NULL default '0000-00-00',
  `payment_for` int(11) NOT NULL default '0',
  `payment_for_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `amount` double(11,2) NOT NULL default '0.00',
  `_old_date_stated` date NOT NULL default '0000-00-00',
  `_old_voucher_id` int(11) NOT NULL default '0',
  `date_stated` date NOT NULL default '0000-00-00',
  `voucher_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `type` (`type`),
  KEY `payment_for_id` (`payment_for_id`),
  KEY `payment_for` (`payment_for`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8634 ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminder`
--

CREATE TABLE IF NOT EXISTS `invoice_reminder` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `intranet_address_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `contact_address_id` int(11) NOT NULL default '0',
  `contact_person_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `invoice_id` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_executed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_cancelled` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_stated` date NOT NULL default '0000-00-00',
  `voucher_id` int(11) NOT NULL default '0',
  `attention_to` varchar(255) character set latin1 NOT NULL default '',
  `number` int(11) NOT NULL default '0',
  `this_date` date NOT NULL default '0000-00-00',
  `due_date` date NOT NULL default '0000-00-00',
  `reminder_fee` int(11) NOT NULL default '0',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `text` text character set latin1 NOT NULL,
  `payment_method` int(11) NOT NULL default '0',
  `girocode` varchar(255) character set latin1 NOT NULL default '',
  `send_as` enum('email','pdf') character set latin1 NOT NULL default 'pdf',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `contact_id` (`contact_id`,`contact_address_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1108 ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminder_item`
--

CREATE TABLE IF NOT EXISTS `invoice_reminder_item` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `invoice_reminder_id` int(11) NOT NULL default '0',
  `invoice_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`invoice_reminder_id`,`invoice_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1276 ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminder_unpaid_reminder`
--

CREATE TABLE IF NOT EXISTS `invoice_reminder_unpaid_reminder` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `invoice_reminder_id` int(11) NOT NULL default '0',
  `unpaid_invoice_reminder_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`invoice_reminder_id`,`unpaid_invoice_reminder_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=721 ;

-- --------------------------------------------------------

--
-- Table structure for table `kernel_log`
--

CREATE TABLE IF NOT EXISTS `kernel_log` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `primary_module_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `page_url` varchar(255) NOT NULL default '',
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `keyword`
--

CREATE TABLE IF NOT EXISTS `keyword` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `keyword` varchar(255) character set latin1 NOT NULL default '',
  `type` varchar(255) character set latin1 NOT NULL default '',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`type`,`active`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=792 ;

-- --------------------------------------------------------

--
-- Table structure for table `keyword_x_object`
--

CREATE TABLE IF NOT EXISTS `keyword_x_object` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `belong_to` int(11) NOT NULL default '0',
  `keyword_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `belong_to` (`belong_to`),
  KEY `keyword_id` (`keyword_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27405 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `type_key` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `lock_post`
--

CREATE TABLE IF NOT EXISTS `lock_post` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `table_name` varchar(255) NOT NULL default '',
  `post_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=871 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_id_seq`
--

CREATE TABLE IF NOT EXISTS `log_id_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22969 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_table`
--

CREATE TABLE IF NOT EXISTS `log_table` (
  `id` int(11) NOT NULL default '0',
  `logtime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `ident` varchar(16) NOT NULL default '',
  `priority` int(11) NOT NULL default '0',
  `message` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `id` int(11) NOT NULL auto_increment,
  `name` char(255) character set latin1 NOT NULL default '',
  `menu_label` char(255) character set latin1 NOT NULL default '',
  `show_menu` int(11) NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `menu_index` int(11) NOT NULL default '0',
  `frontpage_index` int(11) NOT NULL default '0',
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `position` (`position`),
  KEY `menu_label` (`menu_label`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_package`
--

CREATE TABLE IF NOT EXISTS `module_package` (
  `id` int(11) NOT NULL auto_increment,
  `module_package_group_id` int(11) NOT NULL default '0',
  `module_package_plan_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `module_package_group_id` (`module_package_group_id`,`module_package_plan_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_package_action`
--

CREATE TABLE IF NOT EXISTS `module_package_action` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `order_debtor_identifier` varchar(255) character set latin1 NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `action` text character set latin1 NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `intranet_id` (`intranet_id`,`order_debtor_identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_package_group`
--

CREATE TABLE IF NOT EXISTS `module_package_group` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) character set latin1 NOT NULL default '',
  `sorting_index` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `sorting_index` (`sorting_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_package_module`
--

CREATE TABLE IF NOT EXISTS `module_package_module` (
  `id` int(11) NOT NULL auto_increment,
  `module_package_id` int(11) NOT NULL default '0',
  `module` varchar(255) character set latin1 NOT NULL default '',
  `limiter` varchar(255) character set latin1 NOT NULL default '',
  `active` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `module_package_id` (`module_package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_package_plan`
--

CREATE TABLE IF NOT EXISTS `module_package_plan` (
  `id` int(11) NOT NULL auto_increment,
  `plan` varchar(255) character set latin1 NOT NULL default '',
  `plan_index` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `plan_index` (`plan_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `module_sub_access`
--

CREATE TABLE IF NOT EXISTS `module_sub_access` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL default '0',
  `name` char(255) character set latin1 NOT NULL default '',
  `description` char(255) character set latin1 NOT NULL default '',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `description` (`description`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_archieve`
--

CREATE TABLE IF NOT EXISTS `newsletter_archieve` (
  `id` int(11) NOT NULL auto_increment,
  `list_id` int(11) NOT NULL default '0',
  `subject` varchar(255) character set latin1 NOT NULL default '',
  `text` text character set latin1 NOT NULL,
  `status` tinyint(4) NOT NULL default '0',
  `sent_to_receivers` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `deadline` datetime NOT NULL default '0000-00-00 00:00:00',
  `intranet_id` int(11) NOT NULL default '0',
  `locked` tinyint(1) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `list_id` (`list_id`,`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=176 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_list`
--

CREATE TABLE IF NOT EXISTS `newsletter_list` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `title` varchar(255) character set latin1 NOT NULL default '',
  `description` text character set latin1 NOT NULL,
  `_old_subscribe_option_key` int(11) NOT NULL default '0',
  `_old_optin` tinyint(1) NOT NULL default '1',
  `subscribe_message` text character set latin1 NOT NULL,
  `_old_optout` tinyint(1) NOT NULL default '1',
  `_old_password` varchar(255) character set latin1 NOT NULL default 'vih',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `unsubscribe_message` text character set latin1 NOT NULL,
  `sender_name` varchar(255) character set latin1 NOT NULL default '',
  `reply_email` varchar(255) character set latin1 NOT NULL default '',
  `privacy_policy` varchar(255) character set latin1 NOT NULL default '',
  `active` int(11) NOT NULL default '1',
  `subscribe_subject` varchar(255) character set latin1 NOT NULL default '',
  `optin_link` varchar(255) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriber`
--

CREATE TABLE IF NOT EXISTS `newsletter_subscriber` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(255) character set latin1 NOT NULL default '',
  `list_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `email` varchar(255) character set latin1 NOT NULL default '',
  `date_submitted` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip_submitted` varchar(50) character set latin1 NOT NULL default '',
  `optin` tinyint(1) NOT NULL default '0',
  `date_optin_email_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_optin` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip_optin` varchar(255) character set latin1 NOT NULL default '',
  `resend_optin_email_count` int(11) NOT NULL,
  `intranet_id` int(11) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `date_unsubscribe` date NOT NULL,
  `unsubscribe_comment` text character set latin1 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `find_subscribers` (`intranet_id`,`optin`,`active`,`list_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18133 ;

-- --------------------------------------------------------

--
-- Table structure for table `onlinepayment`
--

CREATE TABLE IF NOT EXISTS `onlinepayment` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `belong_to_key` int(11) NOT NULL default '0',
  `belong_to_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_authorized` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_captured` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_reversed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_cancelled` datetime NOT NULL default '0000-00-00 00:00:00',
  `status_key` int(11) NOT NULL default '0',
  `text` varchar(255) character set latin1 NOT NULL default '',
  `transaction_number` int(11) NOT NULL default '0',
  `transaction_status` varchar(255) character set latin1 NOT NULL default '',
  `pbs_status` varchar(256) character set latin1 NOT NULL,
  `amount` double(11,2) NOT NULL default '0.00',
  `original_amount` double(11,2) NOT NULL default '0.00',
  `currency_id` int(11) NOT NULL default '0',
  `captured_in_currency_payment_exchange_rate_id` int(11) NOT NULL,
  `provider_key` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `belong_to_key` (`belong_to_key`),
  KEY `belong_to_id` (`belong_to_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6372 ;

-- --------------------------------------------------------

--
-- Table structure for table `onlinepayment_settings`
--

CREATE TABLE IF NOT EXISTS `onlinepayment_settings` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `onlinepayment_settings_translation`
--

CREATE TABLE IF NOT EXISTS `onlinepayment_settings_translation` (
  `id` int(11) NOT NULL,
  `email` text character set latin1,
  `lang` varchar(20) character set latin1 NOT NULL,
  `subject` varchar(255) character set latin1 NOT NULL,
  UNIQUE KEY `id` (`id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE IF NOT EXISTS `permission` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `module_sub_access_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`user_id`,`module_id`,`module_sub_access_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8295 ;

-- --------------------------------------------------------

--
-- Table structure for table `php_sessions`
--

CREATE TABLE IF NOT EXISTS `php_sessions` (
  `session_id` varchar(40) NOT NULL default '',
  `last_active` int(11) NOT NULL default '0',
  `data` text NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `procurement`
--

CREATE TABLE IF NOT EXISTS `procurement` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_recieved` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_canceled` datetime NOT NULL default '0000-00-00 00:00:00',
  `invoice_date` date NOT NULL default '0000-00-00',
  `delivery_date` date NOT NULL default '0000-00-00',
  `payment_date` date NOT NULL default '0000-00-00',
  `paid_date` date NOT NULL default '0000-00-00',
  `number` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `vendor` varchar(255) character set latin1 NOT NULL default '',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `from_region_key` int(11) NOT NULL default '0',
  `_old_total_price` double(11,2) unsigned NOT NULL default '0.00',
  `price_items` double(11,2) unsigned NOT NULL default '0.00',
  `price_shipment_etc` double(11,2) unsigned NOT NULL default '0.00',
  `vat` double(11,2) unsigned NOT NULL default '0.00',
  `status_key` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '0',
  `date_stated` date NOT NULL default '0000-00-00',
  `voucher_number` int(11) NOT NULL default '0',
  `state_account_id` int(11) NOT NULL default '0',
  `voucher_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=706 ;

-- --------------------------------------------------------

--
-- Table structure for table `procurement_item`
--

CREATE TABLE IF NOT EXISTS `procurement_item` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `procurement_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_detail_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `product_variation_detail_id` int(11) NOT NULL default '0',
  `unit_purchase_price` double(11,2) NOT NULL default '0.00',
  `quantity` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `procurement_id` (`procurement_id`),
  KEY `product_id` (`product_id`),
  KEY `product_detail_id` (`product_detail_id`),
  KEY `active` (`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `do_show` tinyint(1) NOT NULL default '1',
  `has_variation` tinyint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `changed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantity` int(11) NOT NULL default '0',
  `stock` tinyint(1) NOT NULL default '0',
  `locked` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8677 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_attribute`
--

CREATE TABLE IF NOT EXISTS `product_attribute` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `attribute_group_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `_old_deleted` tinyint(1) NOT NULL default '0',
  `deleted_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `attribute_group_id` (`attribute_group_id`),
  KEY `position` (`position`),
  KEY `find_attribute_to_variation` (`intranet_id`,`deleted_at`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=94 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_attribute_group`
--

CREATE TABLE IF NOT EXISTS `product_attribute_group` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `description` varchar(255) character set latin1 NOT NULL default '',
  `_old_deleted` tinyint(1) NOT NULL default '0',
  `deleted_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `find_group_to_attribute` (`id`,`intranet_id`,`deleted_at`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_detail`
--

CREATE TABLE IF NOT EXISTS `product_detail` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `number` int(11) NOT NULL default '0',
  `_old_name` varchar(255) character set latin1 NOT NULL,
  `_old_description` text character set latin1 NOT NULL,
  `price` float(11,2) NOT NULL default '0.00',
  `before_price` float(11,2) NOT NULL default '0.00',
  `weight` int(11) NOT NULL default '0',
  `unit` int(11) NOT NULL default '0',
  `vat` tinyint(1) NOT NULL default '1',
  `show_unit` enum('Yes','No') character set latin1 NOT NULL default 'No',
  `pic_id` int(11) NOT NULL default '0',
  `changed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `do_show` tinyint(1) NOT NULL default '1',
  `active` int(11) NOT NULL default '0',
  `state_account_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `number` (`number`),
  KEY `product_id` (`product_id`,`active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16279 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_detail_translation`
--

CREATE TABLE IF NOT EXISTS `product_detail_translation` (
  `id` int(11) NOT NULL,
  `lang` char(2) character set latin1 NOT NULL,
  `name` varchar(255) character set latin1 NOT NULL,
  `description` text character set latin1 NOT NULL,
  PRIMARY KEY  (`id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_related`
--

CREATE TABLE IF NOT EXISTS `product_related` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `related_product_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `related_product_id` (`related_product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5651 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variation`
--

CREATE TABLE IF NOT EXISTS `product_variation` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `number` int(11) NOT NULL default '0',
  `_old_deleted` tinyint(1) NOT NULL default '0',
  `deleted_at` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `find_variation` (`intranet_id`,`product_id`,`deleted_at`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1838 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variation_detail`
--

CREATE TABLE IF NOT EXISTS `product_variation_detail` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `product_variation_id` int(11) NOT NULL default '0',
  `price_difference` int(11) NOT NULL default '0',
  `weight_difference` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`),
  KEY `product_variation_id` (`product_variation_id`),
  KEY `find_detail_to_variation` (`intranet_id`,`product_variation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2241 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variation_x_attribute`
--

CREATE TABLE IF NOT EXISTS `product_variation_x_attribute` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `product_attribute_id` int(11) NOT NULL default '0',
  `attribute_number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_intranet` (`intranet_id`),
  KEY `product_attribute_id` (`product_attribute_id`),
  KEY `idx_product_variation_attribute` (`product_variation_id`,`attribute_number`),
  KEY `find_attribute_to_variation` (`intranet_id`,`product_variation_id`,`attribute_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3425 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_x_attribute_group`
--

CREATE TABLE IF NOT EXISTS `product_x_attribute_group` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_attribute_group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=788 ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `description` text character set latin1 NOT NULL,
  `intranet_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_task`
--

CREATE TABLE IF NOT EXISTS `project_task` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `project_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  `item` text character set latin1 NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `redirect`
--

CREATE TABLE IF NOT EXISTS `redirect` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `session_id` varchar(255) character set latin1 NOT NULL default '',
  `from_url` varchar(255) character set latin1 NOT NULL default '',
  `return_url` varchar(255) character set latin1 NOT NULL default '',
  `destination_url` varchar(255) character set latin1 NOT NULL default '',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `cancel_url` varchar(255) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14757 ;

-- --------------------------------------------------------

--
-- Table structure for table `redirect_parameter`
--

CREATE TABLE IF NOT EXISTS `redirect_parameter` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `redirect_id` int(11) NOT NULL default '0',
  `parameter` varchar(255) character set latin1 NOT NULL default '',
  `multiple` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`redirect_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10029 ;

-- --------------------------------------------------------

--
-- Table structure for table `redirect_parameter_value`
--

CREATE TABLE IF NOT EXISTS `redirect_parameter_value` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `redirect_id` int(11) NOT NULL default '0',
  `redirect_parameter_id` int(11) NOT NULL default '0',
  `value` varchar(255) character set latin1 NOT NULL default '',
  `extra_value` varchar(255) character set latin1 NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`redirect_id`,`redirect_parameter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11005 ;

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `setting` varchar(255) character set latin1 NOT NULL default '',
  `value` longtext character set latin1 NOT NULL,
  `sub_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`user_id`),
  KEY `setting` (`setting`),
  KEY `user_id` (`user_id`),
  KEY `sub_id` (`sub_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1955 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE IF NOT EXISTS `shop` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set latin1 NOT NULL default '',
  `identifier` varchar(255) character set latin1 NOT NULL default '',
  `receipt` text character set latin1 NOT NULL,
  `confirmation` text character set latin1 NOT NULL,
  `description` text character set latin1 NOT NULL,
  `show_online` int(1) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  `send_confirmation` tinyint(1) NOT NULL default '1',
  `confirmation_add_contact_url` tinyint(4) NOT NULL default '0',
  `confirmation_subject` varchar(255) character set latin1 NOT NULL default '',
  `confirmation_greeting` varchar(255) character set latin1 NOT NULL default '',
  `payment_link` varchar(255) character set latin1 NOT NULL default '',
  `payment_link_add` tinyint(1) NOT NULL default '0',
  `trade_of_terms_url` varchar(255) character set latin1 NOT NULL,
  `terms_of_trade_url` varchar(255) character set latin1 NOT NULL,
  `default_currency_id` int(11) NOT NULL,
  `language_key` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_dicount_campaign`
--

CREATE TABLE IF NOT EXISTS `shop_dicount_campaign` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) character set latin1 NOT NULL,
  `voucher_code_prefix` varchar(255) character set latin1 NOT NULL,
  `intranet_id` bigint(20) default NULL,
  `deleted_at` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_dicount_campaign_voucher`
--

CREATE TABLE IF NOT EXISTS `shop_dicount_campaign_voucher` (
  `id` bigint(20) NOT NULL auto_increment,
  `shop_discount_campaign_id` int(11) NOT NULL default '0',
  `code` varchar(255) character set latin1 NOT NULL,
  `quantity` bigint(20) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_expiry` datetime NOT NULL default '0000-00-00 00:00:00',
  `used_on_debtor_id` bigint(20) NOT NULL default '0',
  `created_from_debtor_id` bigint(20) NOT NULL default '0',
  `intranet_id` bigint(20) default NULL,
  `deleted_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `shop_discount_campaign_id_idx` (`shop_discount_campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_featuredproducts`
--

CREATE TABLE IF NOT EXISTS `shop_featuredproducts` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `headline` varchar(255) character set latin1 NOT NULL default '',
  `keyword_id` int(11) NOT NULL default '0',
  `shop_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `keyword_id` (`keyword_id`),
  KEY `intranet_id` (`intranet_id`,`shop_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `shop_paymentmethods`
--

CREATE TABLE IF NOT EXISTS `shop_paymentmethods` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `paymentmethod_key` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adaptation`
--

CREATE TABLE IF NOT EXISTS `stock_adaptation` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `adaptation_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantity` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`product_id`),
  KEY `product_variation_id` (`product_variation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=780 ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_regulation`
--

CREATE TABLE IF NOT EXISTS `stock_regulation` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `product_id` int(11) NOT NULL default '0',
  `product_variation_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `regulation_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `comment` text character set latin1 NOT NULL,
  `quantity` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`product_id`),
  KEY `product_variation_id` (`product_variation_id`),
  KEY `find_quantity_since_last_regulation` (`intranet_id`,`product_id`,`product_variation_id`,`regulation_date_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6874 ;

-- --------------------------------------------------------

--
-- Table structure for table `todo_contact`
--

CREATE TABLE IF NOT EXISTS `todo_contact` (
  `id` int(11) NOT NULL auto_increment,
  `list_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `todo_item`
--

CREATE TABLE IF NOT EXISTS `todo_item` (
  `id` int(11) NOT NULL auto_increment,
  `todo_list_id` int(11) NOT NULL default '0',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `item` text character set latin1 NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `position` int(11) NOT NULL default '0',
  `responsible_user_id` int(11) NOT NULL default '0',
  `intranet_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `todo_list_id` (`todo_list_id`,`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=765 ;

-- --------------------------------------------------------

--
-- Table structure for table `todo_list`
--

CREATE TABLE IF NOT EXISTS `todo_list` (
  `id` int(255) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) character set latin1 NOT NULL default '',
  `description` text character set latin1 NOT NULL,
  `public_key` varchar(255) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `lastlogin` datetime NOT NULL default '0000-00-00 00:00:00',
  `email` char(255) character set latin1 NOT NULL default '',
  `password` char(255) character set latin1 NOT NULL default '',
  `session_id` char(255) character set latin1 NOT NULL default '',
  `active_intranet_id` int(11) NOT NULL default '0',
  `disabled` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `session_id` (`session_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=168 ;

-- --------------------------------------------------------

--
-- Table structure for table `webshop_basket_evaluation`
--

CREATE TABLE IF NOT EXISTS `webshop_basket_evaluation` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `running_index` int(11) NOT NULL default '0',
  `evaluate_target_key` int(11) NOT NULL default '0',
  `evaluate_method_key` int(11) NOT NULL default '0',
  `evaluate_value` varchar(255) character set latin1 NOT NULL default '',
  `evaluate_value_case_sensitive` int(11) NOT NULL default '0',
  `go_to_index_after` int(11) NOT NULL default '0',
  `action_action_key` int(11) NOT NULL default '0',
  `action_value` varchar(255) character set latin1 NOT NULL default '',
  `action_quantity` int(11) NOT NULL default '0',
  `action_unit_key` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  `shop_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`,`shop_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;
