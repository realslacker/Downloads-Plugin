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
use_helper('Installer');

if ( ! Installer::removeTable(TABLE_PREFIX.'downloads') ) Installer::failUninstall( 'downloads' );
if ( ! Installer::removeTable(TABLE_PREFIX.'downloadtags') ) Installer::failUninstall( 'downloads' );
if ( ! Installer::removeTable(TABLE_PREFIX.'downloads_tags') ) Installer::failUninstall( 'downloads' );

if ( ! Installer::removePermissions('downloads_view,downloads_new,downloads_edit,downloads_delete,downloads_settings') ) Installer::failUninstall( 'downloads' );

if ( ! Installer::removeRoles('download manager admin,download manager editor') ) Installer::failUninstall( 'downloads' );

if ( ! Plugin::deleteAllSettings('downloads') ) Installer::failUninstall( 'downloads', __('Could not remove plugin settings.') );

Flash::set('success', __('Successfully uninstalled plugin.'));
redirect(get_url('setting'));
