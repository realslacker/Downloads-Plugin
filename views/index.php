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
	<form id="forms-search" action="<?=get_url('plugin/downloads'); ?>" method="get">
		<input type="text" name="q" value="<?=isset($_REQUEST['q']) ? $_REQUEST['q'] : '';?>" /> <input type="submit" value="Search Forms" />
	</form>
</div>
<h1>Download Manager</h1>
<table id="files-list" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th class="name"><a href="<?=$baseURL;?>&order=name"><?=__('Name'); ?></a></th>
      <th class="id"><a href="<?=$baseURL;?>&order=id"><?=__('ID');?></a></th>
      <th class="dcount"><a href="<?=$baseURL;?>&order=downloads"><?=__('Downloads'); ?></a></th>
      <th class="created"><a href="<?=$baseURL;?>&order=created"><?=__('Created'); ?></a></th>
      <th class="expires"><a href="<?=$baseURL;?>&order=expires"><?=__('Expires'); ?></a></th>
      <th class="action" style="width:56px;"><?=__('Action');?></th>
    </tr>
  </thead>
  <tbody>
<?
foreach ($downloads as $download) {
	$name = htmlspecialchars($download->name);
?>
	<tr class="<?=odd_even(); ?>">
		<td><a href="<?=get_url('plugin/downloads/edit/'.$download->id); ?>"><?=$name;?></a></td>
		<td><code><?=$download->id;?></code></td>
		<td><code><?=$download->downloads; ?></code></td>
		<td><code><?=date('m/d/Y',strtotime($download->created)); ?></code></td>
		<td><code><?=!empty($download->expires) ? date('m/d/Y',strtotime($download->expires)) : 'never'; ?></code></td>
		<td>
			<?="<a href=\"/download-file/{$download->id}/{$download->filename}\" target=\"_blank\" title=\"{$download->filename}\"><img src=\"".PLUGINS_URI."/downloads/images/icon-open.png\" alt=\"view icon\" title=\"View\"></a>"; ?>
			<a class="edit-link" name="<?=$name;?>" href="<?=get_url('plugin/downloads/edit/'.$download->id); ?>"><img src="<?=PLUGINS_URI;?>/downloads/images/icon-edit.png" alt="edit icon" title="Edit <?=$name;?>" /></a>
			<a class="delete-link" name="<?=$name;?>" href="<?=get_url('plugin/downloads/delete/'.$download->id); ?>"><img src="<?=PLUGINS_URI;?>/downloads/images/icon-trash.png" alt="delete file icon" title="Delete <?=$name;?>" /></a>
		</td>
	</tr>
<?
}
?>
</tbody>
</table>
<?=$pagiation;?>
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