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
<h1><?php echo __('Download Manager Plugin Settings');?></h1>
<form action="<?php echo get_url('plugin/downloads/save_settings'); ?>" method="post">
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Paths'); ?></legend>
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="download_path"><?php echo __('Downloads Path:');?> </label></td>
				<td class="field"><input name="download_path" id="download_path" type="text" size="35" maxsize="255" value="<?php echo $download_path;?>"/></td>
				<td class="help"><?php echo __('System path relative to CMS_ROOT.'); ?></td>
			</tr>
			<tr>
				<td class="label"><label for="download_uri"><?php echo __('Downloads URI:');?> </label></td>
				<td class="field"><input name="download_uri" id="download_uri" type="text" size="35" maxsize="255" value="<?php echo $download_uri;?>"/></td>
				<td class="help"><?php echo __('URI relative to URI_PUBLIC.'); ?></td>
			</tr>
		</table>
	</fieldset>
	<br/>
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Upload Controls');?></legend>
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="filetypes"><?php echo __('Allowed File Types:');?> </label></td>
				<td class="field"><input name="filetypes" id="filetypes" type="text" size="35" maxsize="255" value="<?php echo $filetypes;?>"/></td>
				<td class="help"><?php echo __('Allowed file extensions.');?></td>
			</tr>
		</table>
	</fieldset>
	<br/>
	<fieldset style="padding: 0.5em;">
		<legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('File Creation Defaults'); ?></legend>
		<table class="fieldset" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="label"><label for="umask"><?php echo __('Umask:');?> </label></td>
				<td class="field"><input name="umask" id="umask" type="text" size="35" maxsize="255" value="<?php echo $umask;?>"/></td>
				<td class="help"><?php echo __('Default PHP umask; see <a href="http://php.net/manual/en/function.umask.php">umask()</a>');?></td>
			</tr>
			<tr>
				<td class="label"><label for="dirmode"><?php echo __('Directory Creation Mode:');?> </label></td>
				<td class="field"><input name="dirmode" id="dirmode" type="text" size="35" maxsize="255" value="<?php echo $dirmode;?>"/></td>
				<td class="help"><?php echo __('Default PHP directory creation mode; see <a href="http://us3.php.net/manual/en/function.chmod.php">chmod()</a>');?></td>
			</tr>
			<tr>
				<td class="label"><label for="filemode"><?php echo __('File Creation Mode:');?> </label></td>
				<td class="field"><input name="filemode" id="filemode" type="text" size="35" maxsize="255" value="<?php echo $filemode;?>"/></td>
				<td class="help"><?php echo __('Default PHP file creation mode; see <a href="http://us3.php.net/manual/en/function.chmod.php">chmod()</a>');?></td>
			</tr>
		</table>
	</fieldset>
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save');?>" />
	</p>
</form>

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
        // Prevent accidentally navigating away
        $(':input').bind('change', function() { setConfirmUnload(true); });
        $('form').submit(function() { setConfirmUnload(false); return true; });
    });
// ]]>
</script>