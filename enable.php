<?php
/*
 * Downloads Plugin for WolfCMS <http://www.wolfcms.org>
 * Copyright (C) 2011 Shannon Brooks <shannon@brooksworks.com>
 *
 * This file is part of Downloads Plugin. Downloads Plugin is licensed under the GNU GPLv3 license.
 * Please see license.txt for the full license text.
 */

// Security Measure
if (!defined('IN_CMS')) { exit(); }

//	include the Installer helper
try {
	use_helper('Installer');
} catch (Exception $e) {
    die ($e->getMessage());
}

//	only support MySQL
$driver = Installer::getDriver();
if ( $driver != 'mysql' ) Installer::failInstall( 'downloads', __('Only MySQL is supported!') );

//	get plugin version
$version = Plugin::getSetting('version', 'downloads');

switch ($version) {

	//	no version found so we do a clean install
	default:
	
		//	sanity check to make sure we are really dealing with a clean install
		if ($version !== false) Installer::failInstall( 'downloads', __('Unknown Version!') );
		
		//	create tables
		
		$downloads_table = TABLE_PREFIX.'downloads';
		$downloads_table_sql =<<<SQL
			CREATE TABLE IF NOT EXISTS {$downloads_table}  (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`name` VARCHAR( 255 ) NULL DEFAULT NULL ,
				`filename` VARCHAR( 255 ) NULL DEFAULT NULL ,
				`description` MEDIUMTEXT NULL DEFAULT NULL ,
				`keywords` VARCHAR( 255 ) NULL DEFAULT NULL ,
				`tags` VARCHAR( 255 ) NULL DEFAULT NULL ,
				`active` TINYINT( 1 ) NOT NULL DEFAULT '1' ,
				`downloads` INT( 11 ) NOT NULL DEFAULT '0' ,
				`hash` CHAR( 40 ) NULL DEFAULT NULL ,
				`expires` DATE NULL DEFAULT NULL ,
				`created` DATETIME NULL DEFAULT NULL ,
				`updated` DATETIME NULL DEFAULT NULL ,
				PRIMARY KEY ( `id` )
			) ENGINE=MYISAM DEFAULT CHARSET=utf8
SQL;
		if ( ! Installer::createTable($downloads_table,$downloads_table_sql) ) Installer::failInstall( 'downloads', __('Could not create table 1 of 3.') );
		
		$downloadtags_table = TABLE_PREFIX.'downloadtags';
		$downloadtags_table_sql =<<<SQL
			CREATE TABLE IF NOT EXISTS {$downloadtags_table}  (
				`id` INT NOT NULL AUTO_INCREMENT ,
				`name` VARCHAR( 255 ) NOT NULL ,
				PRIMARY KEY ( `id` ) ,
				UNIQUE ( `name` )
			) ENGINE=MYISAM DEFAULT CHARSET=utf8
SQL;
		if ( ! Installer::createTable($downloadtags_table,$downloadtags_table_sql) ) Installer::failInstall( 'downloads', __('Could not create table 2 of 3.') );
		
		$downloads_tags_table = TABLE_PREFIX.'downloads_tags';
		$downloads_tags_table_sql =<<<SQL
			CREATE TABLE IF NOT EXISTS {$downloads_tags_table}  (
				`download_id` INT( 11 ) NOT NULL ,
				`tag_id` INT( 11 ) NOT NULL,
				INDEX `download_tag` ( `download_id` , `tag_id` )
			) ENGINE=MYISAM DEFAULT CHARSET=utf8
SQL;
		if ( ! Installer::createTable($downloads_tags_table,$downloads_tags_table_sql) ) Installer::failInstall( 'downloads', __('Could not create table 3 of 3.') );
		
		//	create new permissions
		if ( ! Installer::createPermissions('downloads_view,downloads_new,downloads_edit,downloads_delete,downloads_settings') ) Installer::failInstall('downloads');
		
		//	create new roles
		if ( ! Installer::createRoles('download manager admin,download manager editor,download manager user') ) Installer::failInstall('downloads');
		
		//	assign permissions
		//	note: admin_view is needed in case they don't have any other permissions, otherwise they won't be able to log in to admin interface
		if ( ! Installer::assignPermissions('download manager admin','admin_view,downloads_view,downloads_new,downloads_edit,downloads_delete,downloads_settings') ) Installer::failInstall('downloads');
		if ( ! Installer::assignPermissions('download manager editor','admin_view,downloads_view,downloads_new,downloads_edit,downloads_delete') ) Installer::failInstall('downloads');
		if ( ! Installer::assignPermissions('download manager user','admin_view,downloads_view') ) Installer::failInstall('downloads');
		if ( ! Installer::assignPermissions('administrator','downloads_view,downloads_new,downloads_edit,downloads_delete,downloads_settings') ) Installer::failInstall('downloads');
		
		//	setup plugin settings
		$settings = array(
			'version'		=>	'0.0.3',
			'download_path'	=>	'public/downloads',
			'download_uri'	=>	'public/downloads',
			'umask'			=>	'0',
			'filemode'		=>	'0664',
			'dirmode'		=>	'0775',
			'filetypes'		=>	'pdf,jpg,png,zip,7z,doc,docx,mp3'
		);
		if ( ! Plugin::setAllSettings($settings, 'downloads') ) Installer::failInstall( 'downloads', __('Unable to store plugin settings!') );
		
		Flash::set('success', __('Successfully installed Download Manager plugin.'));
		
		//	we must exit the switch so upgrades are not applied to new installation (they should already be integrated for new installs)
		break;
		
	//	upgrade 0.0.1 to 0.0.2
	case '0.0.1':
	
		$settings = array('version'	=> '0.0.2');
		if ( ! Plugin::setAllSettings($settings, 'downloads') ) Installer::failInstall( 'downloads', __('Unable to store plugin settings!') );

	//	upgrade 0.0.2 to 0.0.3
	case '0.0.2':
		
		//	create new roles
		if ( ! Installer::createRoles('download manager user') ) Installer::failInstall('downloads');
		
		//	assign permissions
		//	note: admin_view is needed in case they don't have any other permissions, otherwise they won't be able to log in to admin interface
		if ( ! Installer::assignPermissions('download manager user','admin_view,downloads_view') ) Installer::failInstall('downloads');
	
		$settings = array('version'	=> '0.0.3');
		if ( ! Plugin::setAllSettings($settings, 'downloads') ) Installer::failInstall( 'downloads', __('Unable to store plugin settings!') );
		
		
		//	this line should come after all upgrade cases so that it's only set once
		Flash::set('success', __('Successfully upgraded Download Manager plugin.'));
		
	
}