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
<h1><?php echo __('Systemtest'); ?></h1>
<p><?php

echo __('Please check the following lines if Uploading dont work.').'<br>';

$yesno[true] = __('Yes');
$yesno[false] = '<b>'.__('No').' :-(</b>';

echo '<h3>'.__('Temporary Directory') .'</h3>';
echo __('Path') . ': ' . $tempdir. '<br>';
echo __('Directory exits?') . ': ' . $yesno[file_exists($tempdir)].'<br>';
echo __('Directory is writeable?') .': ' . $yesno[is_writable($tempdir)];

echo '<h3>'.__('Destination Directory') .'</h3>';
echo __('Path') . ': ' . $download_path. '<br>';
echo __('Directory exits?') . ': ' . $yesno[file_exists($download_path)].'<br>';
echo __('Directory is writeable?') .': ' . $yesno[is_writable($download_path)];

?></p>
<p>&nbsp;</p>

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
