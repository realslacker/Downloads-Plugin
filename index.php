<?php
/*
 * Download Manager Plugin for WolfCMS <http://www.wolfcms.org>
 * Copyright (C) 2011 Shannon Brooks <shannon@brooksworks.com>
 */

//	security measure
if (!defined('IN_CMS')) { exit(); }

Plugin::setInfos(array(
	'id'          => 'downloads',
	'title'       => __('Download Manager'),
	'description' => __('Provides interface to manage downloadable files.'),
	'version'     => '0.0.3',
	'license'     => 'GPL',
	'author'      => 'Shannon Brooks',
	'website'     => 'http://www.brooksworks.com/',
	'require_wolf_version' => '0.7.2'
));

//	define paths
define ('DOWNLOADS_PLUGIN_ROOT',CORE_ROOT.'/plugins/downloads');

//	watch for download requests
Observer::observe('page_requested', 'downloads_catch_click');

// Add the plugin's tab and controller
Plugin::addController('downloads', __('Download Manager'),'downloads_view,downloads_new,downloads_edit,downloads_delete,downloads_settings');

// Load the class into the system.
AutoLoader::addFile('Download', CORE_ROOT.'/plugins/downloads/Download.php');
AutoLoader::addFile('DownloadTag', CORE_ROOT.'/plugins/downloads/DownloadTag.php');
AutoLoader::addFile('DownloadTagConnection', CORE_ROOT.'/plugins/downloads/DownloadTagConnection.php');

// redirect urls already set up
function downloads_catch_click($args) {

	//	check for download
	if (preg_match('#^/download-file/(\d+)#i',$args,$matches)) {
		
		//	update the click count of the banner
		$id = (int)$matches[1];
		if (!$download = Download::findById($id)) return $args;
		$download->downloads++;
		$download->save();
		
		//	redirect to the requested url
		header ('HTTP/1.1 301 Moved Permanently', true);
		header ('Location: /'.Plugin::getSetting('download_uri','downloads').'/'.$download->filename);

		exit;
	}
	
	//	check for download by name
	if (preg_match('#^/download-file/(.*)$#i',$args,$matches)) {
		
		//	update the click count of the banner
		$filename = $matches[1];
		if (!$download = Download::findByFilename($filename)) return $args;
		$download->downloads++;
		$download->save();
		
		//	redirect to the requested url
		header ('HTTP/1.1 301 Moved Permanently', true);
		header ('Location: /'.Plugin::getSetting('download_uri','downloads').'/'.$download->filename);

		exit;
	}
	
	//	no click so keep going
	return $args;
}

//	return download search results in array
function downloadSearch($terms,$limit=10,$offset=0,$order='name',$admin=false) {

	$where = $admin === true ? '1' : "`downloads`.`active` = '1' AND ( `downloads`.`expires` > NOW() || `downloads`.`expires` IS NULL )";    

	$order = strtolower($order);
	$order = in_array($order,explode(',','id,name,filename,active,downloads,expires,created,updated')) && !empty($order) ? $order : 'name' ;
	$order = 'downloads.'.$order.' ASC';
	
	if (! empty($terms)) {
		$querys = preg_replace('/[^a-z0-9 %]/i',' ',$terms);
		$querys = strstr($querys,' ') !== false ? explode(' ',$querys) : array($querys);
		$querys = preg_replace(array('/ing$/i','/ed$/i','/s$/i'),'',$querys);
		
		foreach ($querys as $query) {
			if (strstr($query,'%') === false && !empty($query)) $query = "%{$query}%";
			if (!empty($query))	$where .= " AND ( downloads.name LIKE ".Record::escape($query)." OR downloads.description LIKE ".Record::escape($query)." OR downloads.keywords LIKE ".Record::escape($query)." ) ";
		}
	}
	
	if (!$results = Download::findAll(array('where'=>$where,'limit'=>$limit,'offset'=>$offset,'order'=>$order))) return false;
	$count = Record::countFrom('Download',$where);
	
	return array('downloads'=>$results,'count'=>$count);

}

//	output a download link by download id
function downloadLinkById($download_id,$text=null) {
	
	//	get download
	if (!$download = Download::findById($download_id)) return "<span class=\"download-broken\" title=\"broken download link\">[".(!empty($text) ? $text : 'file not found')."]</span>";
	
	//	return download link
	return "<a class=\"download-link\" href=\"/download-file/{$download->id}/{$download->filename}\" target=\"_blank\" rel=\"nofollow\">".(!empty($text) ? $text : $download->name)."</a>";
}

function downloadBoxById($download_id) {
	
	//	get download
	if (!$download = Download::findById($download_id)) return "<div><span class=\"download-broken\" title=\"broken download link\">[file not found]</span></div>";
	
	//	return download box
	return downloadBoxFormat($download);
}

//	output a download player for mp3 files
function downloadPlayerById($download_id,$text=null) {
	
	//	get download
	if (!$download = Download::findById($download_id)) return "<span class=\"download-broken\" title=\"broken download link\">[".(!empty($text) ? $text : 'file not found')."]</span>";
	
	//	return download link
	return downloadBoxFormat($download,true);
}

//	depreciated
function downloadLinksByTag($tag) {
	return downloadBoxesByTag($tag);
}

//	returns a list of downloads in an un-ordered list
function downloadListByTag($tag) {
	if (!$downloads = DownloadTagConnection::findAllByTagName($tag)) return "<span class=\"download-broken\" title=\"no downloads found\">[tag \"{$tag}\" has no downloads]</span>";
	$return = '<ul>';
	foreach ($downloads as $download) $return .= '<li>'.downloadLinkById($download->id).'</li>';
	return $return.'</ul>';
}

//	returns a list of downloads in formatted boxes
function downloadBoxesByTag($tag) {
	if (!$downloads = DownloadTagConnection::findAllByTagName($tag)) return "<span class=\"download-broken\" title=\"no downloads found\">[tag \"{$tag}\" has no downloads]</span>";
	$return = '';
	foreach ($downloads as $download) $return .= downloadBoxFormat($download);
	return $return;
}

function downloadBoxFormat($download,$player='') {
	$modified = ($download->created == $download->updated ? 'File was created ' : 'File was last updated ').date('F j, Y',strtotime($download->updated));
	$description = !empty($download->description) ? "<div class=\"download-description\">".nl2br($download->description)."</div>" : '<!-- no description -->';
	if ($player === true) {
		$playerid = 'dp'.rand();
		$player = <<<HTML
	<div class="download-player"><div id="{$playerid}"></div></div>
	<script type="text/javascript">
	<!--
	$(function(){
		AudioPlayer.embed("{$playerid}", { soundFile: "/download-file/{$download->id}/{$download->filename}" });
	});
	//-->
	</script>
HTML;
	}
	$return = <<<HTML
<div class="download">
	<div class="download-name">{$download->name}</div>
	{$description}
	{$player}
	<div class="download-link-container"><a class="download-link" href="/download-file/{$download->id}/{$download->filename}" target="_blank" rel="nofollow">Download File</a></div>
	<div class="download-info">{$modified} and has been downloaded {$download->downloads} times.</div>
</div>
HTML;
	return $return;
}



?>