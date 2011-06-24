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

//	DownloadTagConnection class represents a DownloadTagConnection record
class DownloadTagConnection extends Record {

	const TABLE_NAME = 'downloads_tags';
	
	//	search function to perform query
	public static function find($args = array()) {

		// Collect attributes...
		$where = isset($args['where']) ? trim($args['where']) : '1';
		$order_by = isset($args['order']) ? trim($args['order']) : 'downloads_tags.download_id ASC';
		$offset = isset($args['offset']) ? (int)$args['offset'] : 0;
		$limit = isset($args['limit']) ? (int)$args['limit'] : 0;
		$group_by = isset($args['group']) ? trim($args['group']) : '';
		$having = isset($args['having']) ? trim($args['having']) : '';

		// Prepare query parts
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$group_by_string = empty($group_by) ? '' : "GROUP BY $group_by";
		$having_string = empty($having) ? '' : "HAVING $having";
		$limit_string = $limit > 0 ? "LIMIT $limit" : '';
		$offset_string = $offset > 0 ? "OFFSET $offset" : '';

		$tablename = self::tableNameFromClassName('DownloadTagConnection');
		$tablename_downloads = self::tableNameFromClassName('Download');
		$tablename_tags = self::tableNameFromClassName('DownloadTag');

		// Prepare SQL
		$sql = "SELECT downloads_tags.*, downloads.*, downloadtags.name AS tag FROM $tablename AS downloads_tags "
			. "LEFT JOIN $tablename_downloads AS downloads ON downloads.id=downloads_tags.download_id "
			. "LEFT JOIN $tablename_tags AS downloadtags ON downloadtags.id=downloads_tags.tag_id "
			. "WHERE $where $group_by_string $having_string $order_by_string $limit_string $offset_string";

		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();

		// Run!
		if ($limit == 1) {
			return $stmt->fetchObject('DownloadTagConnection');
		} else {
			$objects = array();
			while ($object = $stmt->fetchObject('DownloadTagConnection'))
				$objects[] = $object;
			return $objects;
		}
	} //*/

	//	find all records
	public static function findAll($args = array()) {
		return self::find($args);
	} //*/
	
	//	find a specific record by it's download_id
	public static function findAllByDownloadId($id) {
		return self::find(array(
			'where' => 'downloads_tags.download_id=' . Record::escape((int)$id)
		));
	} //*/

	//	find a specific record by it's tag_id
	public static function findAllByTagId($id) {
		return self::find(array(
			'where' => 'downloads_tags.tag_id=' . Record::escape((int)$id)
		));
	} //*/
	
	/*	find all records by matching the tag name
	public static function findAllByTagName($name) {
		return self::find(array(
			'where' => 'downloadtags.name=' . Record::escape($name),
			'order' => 'downloads.name ASC'
		));
	} //*/
	
	//	find all records with matching tags
	public static function findAllByTagName($tags=array()) {
		$tags = is_array($tags) ? $tags : explode(',',$tags);
		$where = '';
		$count = 0;
		foreach ($tags as $tag) if (! empty($tag)) {
			$where .= (!empty($where) ? ',' : '') . Record::escape($tag);
			$count++;
		}
		return self::find(array(
			'where' => "downloadtags.name IN ($where)",
			'order' => 'downloads.name ASC',
			'group' => 'downloads.id',
			'having' => "COUNT(*)>=$count"
		));
		
	} //*/

} // end Banner class
