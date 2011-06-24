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

//	DownloadTag class represents a downloadtag record
class DownloadTag extends Record {

	const TABLE_NAME = 'downloadtags';
	
	//	search function to perform query
	public static function find($args = array()) {

		// Collect attributes...
		$where = isset($args['where']) ? trim($args['where']) : '1';
		$order_by = isset($args['order']) ? trim($args['order']) : 'downloadtags.name ASC';
		$offset = isset($args['offset']) ? (int)$args['offset'] : 0;
		$limit = isset($args['limit']) ? (int)$args['limit'] : 0;

		// Prepare query parts
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$limit_string = $limit > 0 ? "LIMIT $limit" : '';
		$offset_string = $offset > 0 ? "OFFSET $offset" : '';

		$tablename = self::tableNameFromClassName('DownloadTag');

		// Prepare SQL
		$sql = "SELECT * FROM $tablename AS downloadtags WHERE $where $order_by_string $limit_string $offset_string";

		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();

		// Run!
		if ($limit == 1) {
			return $stmt->fetchObject('DownloadTag');
		} else {
			$objects = array();
			while ($object = $stmt->fetchObject('DownloadTag'))
				$objects[] = $object;
			return $objects;
		}
	} //*/

	//	find all records
	public static function findAll($args = array()) {
		return self::find($args);
	} //*/
	
	//	find a specific record by it's id
	public static function findById($id) {
		return self::find(array(
			'where' => 'downloadtags.id=' . (int)$id,
			'limit' => 1
		));
	} //*/

	//	find a specific record by it's name
	public static function findByName($name) {
		return self::find(array(
			'where' => 'downloadtags.name='.Record::escape($name),
			'limit' => 1
		));
	} //*/

} // end Banner class
