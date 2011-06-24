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

?>
<h1><?php echo __('Documentation'); ?></h1>

<h2>Single Download Options</h2>
<h3>Download Link</h3>
<p>Displays a download link with optional link text. Text is download name by default.</p>
<code>&lt;?php echo downloadLinkById($id[,$linktext]); ?&gt;</code>
<h3>Download Box</h3>
<p>Displays a download box with download name, description, added/modified date and download count.</p>
<code>&lt;?php echo downloadBoxById($id); ?&gt;</code>
<h3>Download Player</h3>
<p>Displays a download box with an embedded flash player. Only for audio files.</p>
<code>&lt;?php echo downloadPlayerById($id,$text); ?&gt;</code>

<h2>Multiple Download Options</h2>
<h3>Download List</h3>
<p>Displays a list of downloads by tag(s). Multiple tags can be provided as an array or a comma separated list.</p>
<code>&lt;?php echo downloadListByTag($tags); ?&gt;</code>
<h3>Download Boxes</h3>
<p>Displays a series of download boxes by tag(s). Multiple tags can be provided as an array or a comma separated list.</p>
<code>&lt;?php echo downloadBoxesByTag($tags); ?&gt;</code>
<h3>Download Search</h3>
<p>Returns a result set of download objects as an array.</p>
<code>$array = downloadSearch($terms[,$limit[,$offset[,$order]]]);</code>
