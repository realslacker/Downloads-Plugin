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

$allowed_filetypes = str_replace(',',', ',Plugin::getSetting('filetypes','downloads'));

?>
<h1><?php echo isset($id) ? __('Edit Download') . " (#$id): $name" : __('New Download');?></h1>
<form method="post" enctype="multipart/form-data" action="<?php echo isset($id) ? get_url('plugin/downloads/update/'.$id) : get_url('plugin/downloads/create'); ?>">
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('File'); ?></legend>
		<?php if (isset($id)) echo "<a href=\"/download-file/{$id}/{$filename}\" target=\"_blank\">{$filename}</a>"; ?><br />
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="download"><?php echo __('Download:');?> </label></td>
				<td class="field"><input name="download" id="download" type="file" /></td>
				<td class="help"><?php echo __('Allowed filetypes').': '.$allowed_filetypes; ?></td>
			</tr>
		</table>
	</fieldset>
	<br />
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Information'); ?></legend>
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="name"><?php echo __('Name:');?> </label></td>
				<td class="field"><input name="name" id="name" type="text" size="35" maxsize="255" value="<?php echo isset($name) ? $name : '';?>"/></td>
				<td class="help"><?php echo __('The download name; be descriptive'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="description"><?php echo __('Description:');?> </label></td>
				<td class="field"><textarea name="description" id="description" rows="4" cols="35"><?php echo isset($description) ? $description : '';?></textarea></td>
				<td class="help"><?php echo __('Describe the download; visible to the public'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="keywords"><?php echo __('Key Words:');?> </label></td>
				<td class="field"><input name="keywords" id="keywords" type="text" size="35" maxsize="255" value="<?php echo isset($keywords) ? $keywords : '';?>" /></td>
				<td class="help"><?php echo __('Key words for search; separate with spaces'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="tags"><?php echo __('Tags:');?> </label></td>
				<td class="field"><input name="tags" id="tags" type="text" size="35" maxsize="255" value="<?php echo isset($tags) ? $tags : '';?>"/></td>
				<td class="help"><?php echo __('Seperate with commas.'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="expires"><?php echo __('Expires Date:');?> </label></td>
				<td class="field"><input name="expires" id="expires" type="text" size="35" maxsize="255" value="<?php echo isset($expires) ? $expires : '';?>"/></td>
				<td class="help"><?php echo __('Use MM/DD/YYYY format'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="active"><?php echo __('Active:');?> </label></td>
				<td class="field"><input name="active" id="active" type="checkbox" value="1" <?php echo $active ? 'checked="checked"' : '';?> /></td>
				<td class="help"><?php echo __('Is the download active?'); ?></td>
			</tr>
		</table>
	</fieldset>
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save');?>" />
	</p>
</form>
<script type="text/javascript" src="<?php echo PLUGINS_URI;?>/downloads/js/jquery.maskedinput-1.2.2.min.js"></script>
<script type="text/javascript" src="<?php echo PLUGINS_URI;?>/downloads/js/jquery.autocomplete.pack.js"></script>
<script type="text/javascript">
// <![CDATA[
function setConfirmUnload(on, msg) {
	window.onbeforeunload = (on) ? unloadMessage : null;
	return true;
}

function unloadMessage() {
	return '<?php echo __('You have modified this page.  If you navigate away from this page without first saving your data, the changes will be lost.'); ?>';
}

$(document).ready(function() {
	// setup some masks
	$('#expires').mask('99/99/9999');

	// Prevent accidentally navigating away
	$(':input').bind('change', function() { setConfirmUnload(true); });
	$('form').submit(function() { setConfirmUnload(false); return true; });

	// setup autocomplete for tags
	$('#tags').autocomplete('<?php echo get_url('plugin/downloads/autocomplete/tags');?>', {
		multiple: true,
		autoFill: true
	});
});
// ]]>
</script>