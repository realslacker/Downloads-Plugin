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

$baseURL = get_url('plugin/downloads') . '?q=' . (isset($_REQUEST['q']) ? $_REQUEST['q'] : '');
$fullURL = $baseURL . (isset($_REQUEST['order']) ? '&order=' . $_REQUEST['order'] : '');

$pagiation = '';
if ($pages > 1) for ($i = 1; $i <= $pages; $i++) $pagiation .= '<a href="'.$fullURL.'&pg='.$i.'">'.($i==$page ? '<strong>'.$i.'</strong>' : $i ).'</a> ';
if (!empty($pagiation)) $pagiation = '<div style="text-align:center;">'.($page != 1 ? '<span style="float:left;"><a href="'.$fullURL.'&pg=1">&lt;&lt;</a> <a href="'.$fullURL.'&pg='.($page-1).'">&lt;</a></span> ' : '').'<span class="pages">'.$pagiation.'</span>'.($page != $pages ? ' <span style="float:right;"><a href="'.$fullURL.'&pg='.($page+1).'">&gt;</a> <a href="'.$fullURL.'&pg='.$pages.'">&gt;&gt;</a></span>' : '').'</div>';

?>
<div style="float:right;margin:-10px;" class="noprint">
	<form id="forms-search" action="<?php echo get_url('plugin/downloads'); ?>" method="get">
		<input type="text" name="q" value="<?php echo isset($_REQUEST['q']) ? $_REQUEST['q'] : '';?>" /> <input type="submit" value="Search Forms" />
	</form>
</div>
<h1>Download Manager</h1>
<table id="files-list" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th class="name"><a href="<?php echo $baseURL;?>&order=name"><?php echo __('Name'); ?></a></th>
      <th class="id"><a href="<?php echo $baseURL;?>&order=id"><?php echo __('ID');?></a></th>
      <th class="dcount"><a href="<?php echo $baseURL;?>&order=downloads"><?php echo __('Downloads'); ?></a></th>
      <th class="created"><a href="<?php echo $baseURL;?>&order=created"><?php echo __('Created'); ?></a></th>
      <th class="expires"><a href="<?php echo $baseURL;?>&order=expires"><?php echo __('Expires'); ?></a></th>
      <th class="action" style="width:56px;"><?php echo __('Action');?></th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($downloads as $download) {
	$name = htmlspecialchars($download->name);
?>
	<tr class="<?php echo odd_even(); ?>">
		<td><a href="<?php echo get_url('plugin/downloads/edit/'.$download->id); ?>"><?php echo $name;?></a></td>
		<td><code><?php echo $download->id;?></code></td>
		<td><code><?php echo $download->downloads; ?></code></td>
		<td><code><?php echo date('m/d/Y',strtotime($download->created)); ?></code></td>
		<td><code><?php echo !empty($download->expires) ? date('m/d/Y',strtotime($download->expires)) : 'never'; ?></code></td>
		<td>
			<?php echo "<a href=\"/download-file/{$download->id}/{$download->filename}\" target=\"_blank\" title=\"{$download->filename}\"><img src=\"".PLUGINS_URI."/downloads/images/icon-open.png\" alt=\"view icon\" title=\"View\"></a>"; ?>
			<a class="edit-link" name="<?php echo $name;?>" href="<?php echo get_url('plugin/downloads/edit/'.$download->id); ?>"><img src="<?php echo PLUGINS_URI;?>/downloads/images/icon-edit.png" alt="edit icon" title="Edit <?php echo $name;?>" /></a>
			<a class="delete-link" name="<?php echo $name;?>" href="<?php echo get_url('plugin/downloads/delete/'.$download->id); ?>"><img src="<?php echo PLUGINS_URI;?>/downloads/images/icon-trash.png" alt="delete file icon" title="Delete <?php echo $name;?>" /></a>
		</td>
	</tr>
<?php
}
?>
</tbody>
</table>
<?php echo $pagiation;?>
<script type="text/javascript">
<!--
$(function(){

	$('.delete-link').click(function(e){
		if (!confirm("Delete file "+$(this).attr('name')+"?\n\nAre you sure you want to delete this download?\nPress OK to delete this download permenently.")) {
			e.preventDefault();
		}
		
	});

});
//-->
</script>