<?php
echo highlight_string(file_get_contents('index.php'));
?>
<pre>
<?php

/*

I am trying to have LiveUser using the table structure beneath
conserving the right I already gave the users.

I was thinking of using the Complex container, but haven't even
succeded in using the Simple one.

I hope to find some help setting up the container to make LiveUser
work with my existing setup.

- I thought the different intranets would resemble applications.
- The different modules would resemble areas.
- The rights would be sub_access in the different modules (areas).
- Users are off course users.



-- phpMyAdmin SQL Dump
-- version 2.8.2.4
-- http://www.phpmyadmin.net
-- 

-- --------------------------------------------------------

-- 
-- Struktur-dump for tabellen `intranet`
-- 

CREATE TABLE `intranet` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `identifier` varchar(255) NOT NULL default '',
  `pdf_header_file_id` int(11) NOT NULL default '0',
  `key_code` varchar(255) NOT NULL default '',
  `private_key` varchar(255) NOT NULL default '',
  `public_key` varchar(255) NOT NULL default '',
  `maintained_by_user_id` int(11) NOT NULL default '0',
  `password` varchar(255) NOT NULL default '',
  `date_changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `contact_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `private_key` (`private_key`),
  KEY `public_key` (`public_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Struktur-dump for tabellen `module`
-- 

CREATE TABLE `module` (
  `id` int(11) NOT NULL auto_increment,
  `name` char(255) NOT NULL default '',
  `menu_label` char(255) NOT NULL default '',
  `show_menu` int(11) NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `position` (`position`),
  KEY `menu_label` (`menu_label`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

-- 
-- Struktur-dump for tabellen `module_sub_access`
-- 

CREATE TABLE `module_sub_access` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL default '0',
  `name` char(255) NOT NULL default '',
  `description` char(255) NOT NULL default '',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `description` (`description`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

-- 
-- Struktur-dump for tabellen `permission`
-- 

CREATE TABLE `permission` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `module_id` int(11) NOT NULL default '0',
  `module_sub_access_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `module_sub_access_id` (`module_sub_access_id`),
  KEY `intranet_id` (`intranet_id`,`user_id`,`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5194 ;

-- --------------------------------------------------------

-- 
-- Struktur-dump for tabellen `user`
-- 

CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `lastlogin` datetime NOT NULL default '0000-00-00 00:00:00',
  `email` char(255) NOT NULL default '',
  `password` char(255) NOT NULL default '',
  `session_id` char(255) NOT NULL default '',
  `active_intranet_id` int(11) NOT NULL default '0',
  `disabled` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `session_id` (`session_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=67 ;
*/

error_reporting(E_ALL);

require 'configuration.php';
require 'PEAR.php';
require 'MDB2.php';
require 'LiveUser/LiveUser.php';

//$dsn = '{dbtype}://{user}:{passwd}@{dbhost}/{dbname}';

$db =& MDB2::connect($dsn, true);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);

$LUOptions = array(
    'login' => array(
        'force'    => true
     ),
    'logout' => array(
        'destroy'  => true,
     ),
    'authContainers' => array(
        array(
            'type'         => 'MDB2',
            'expireTime'   => 3600,
            'idleTime'     => 1800,
            'storage' => array(
                'dsn' => $dsn,
                'prefix' => '',
                'alias' => array(
					'handle' => 'email',
					'passwd' => 'password',
                    'auth_user_id' => 'id',
                    'lastlogin' => 'lastlogin',
                    'is_active' => 'id',
                    'owner_user_id' => 'id',
                    'owner_group_id' => 'id',
                    'users' => 'user',
                ),
                'fields' => array(
					//'handle' => 'varchar',
					//'passwd' => 'varchar',
					'auth_user_id' => 'integer',
					'lastlogin' => 'timestamp',
                    'is_active' => 'boolean',
                    'owner_user_id' => 'integer',
                    'owner_group_id' => 'integer',
                ),
                'tables' => array(
                    'users' => array(
                        'fields' => array(
                            'lastlogin' => false,
                            'is_active' => false,
                            'owner_user_id' => false,
                            'owner_group_id' => false,
                        ),
                    ),
                ),
            ),
        ),
    ),
    'permContainer' => array(
        'type' => 'Simple',
        'storage' => array(
            'MDB2' => array(
                'dsn' => $dsn,
                'prefix' => '',
                'alias' => array(
                    'perm_users' => 'module'
                ),
            )
         ),
    ),
);


// Create new LiveUser (LiveUser) object.
// We�ll only use the auth container, permissions are not used.
echo "new liveuser object\n";
$LU =& LiveUser::factory($LUOptions);
//$LU->dispatcher->addObserver('forceLogin', 'forceLogin');

echo "checking if user can login\n";
$LU->login($test_user, $test_user_pass);
if ($LU->isLoggedIn()) {
	echo "user logged in\n";
	
}
else {
	print_r($LU->getErrors());
	echo "user not logged in\n";
}


// check a right
echo "checking to see if user has right\n";
if ($LU->checkRight(2)) {
	echo "has right\n";
}
else {
	echo "user had no right\n";
	print_r($LU->getErrors());
}
// check a right based on ownership
/*
if ($LU->checkRightLevel(1, $user, $group)) {
	echo 'on right level';
}
else {
	print_r($LU->getErrors());
}
*/

if (!$LU->init()) {
    echo "Liveuser could not init\n";
    print_r($LU->getErrors());
    
}




?>

</pre>