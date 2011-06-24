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
<p class="button"><a href="<?=get_url('plugin/downloads'); ?>"><img src="<?=PLUGINS_URI;?>/downloads/images/list.png" align="middle" /><?php echo __('List'); ?></a></p>
<p class="button"><a href="<?=get_url('plugin/downloads/add'); ?>"><img src="<?=PLUGINS_URI;?>/downloads/images/new.png" align="middle" /><?php echo __('Add New'); ?></a></p>
<p class="button"><a href="<?=get_url('plugin/downloads/documentation'); ?>"><img src="<?=PLUGINS_URI;?>/downloads/images/documentation.png" align="middle" /><?php echo __('Documentation'); ?></a></p>
<div class="box">
<h2><?php echo __('Download Manager Plugin');?></h2>
<p>
<?php echo __('Plugin Version').': '.Plugin::getSetting('version', 'downloads'); ?>
</p>
<br />
<h2><?=__('Usage');?></h2>
<p><strong>Single Download</strong><br />
<code>&lt;?php echo downloadLinkById($id,$linktext); ?&gt;<br />
&lt;?php echo downloadBoxById($id); ?&gt;<br />
&lt;?php echo downloadPlayerById($id,$text); ?&gt;</code></p>
<p><strong>Multiple Downloads</strong><br />
<code>&lt;?php echo downloadListByTag($tags); ?&gt;<br />
&lt;?php echo downloadBoxesByTag($tags); ?&gt;</code></p>
</div>
