<?php
/*-------------------------------------------------------
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
 --------------------------------------------------------
| Filename: AbstractDatabaseDriver.php
| Author: Takács Ákos (Rimelek)
 --------------------------------------------------------
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
 --------------------------------------------------------*/
namespace PHPFusion\Database;


abstract class AbstractDatabaseDriver
{
	/**
	 * This is a MySQL error code
	 *
	 * http://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
	 */
	const ERROR_UNKNOWN_DATABASE = 1049;

	/**
	 * @var array
	 */
	private static $queries = array();

	/**
	 * @var bool
	 */
	private $debug = FALSE;

	/**
	 * @param bool $debug
	 */
	public function setDebug($debug = TRUE)  {
		$this->debug = (bool) intval($debug);
	}

	/**
	 * @return bool
	 */
	public function isDebug() {
		return $this->debug;
	}

	/**
	 * Send a database query
	 *
	 * @param string $query SQL
	 * @param array $parameters
	 * @return mixed The result of the query or FALSE on error
	 */
	public function query($query, array $parameters = array()) {

		$query_time = microtime(TRUE);
		$result = $this->_query($query, $parameters);
		$query_time = round((microtime(TRUE)-$query_time), 7);
		self::$queries[spl_object_hash($this)][] = array($query_time, $query);
		return $result ? : FALSE;
	}

	/**
	 * Get all queries of all connections
	 *
	 * @return array structure: array(
	 * 		$connection_hash => array(
	 *			array($time, $sql),
	 * 			//...
	 * 		),
	 * 		//...
	 * )
	 */
	public static function getGlobalQueryLog() {
		return self::$queries;
	}

	/**
	 * Get the number of queries of all connections
	 *
	 * @return int
	 */
	public static function getGlobalQueryCount() {
		return (count(self::$queries, COUNT_RECURSIVE) - count(self::$queries)) / 3;
	}

	/**
	 * Get the summarized execution time of all queries of all connections
	 *
	 * @return float
	 */
	public static function getGlobalQueryTimeSum() {
		$sum = 0;
		foreach (self::$queries as $instance) {
			foreach ($instance as $query) {
				$sum += $query[0];
			}
		}
		return $sum;
	}

	/**
	 * Get all queries of this connection
	 *
	 * @return array structure: array(
	 * 		$connection_hash => array(
	 *			array($time, $sql),
	 * 			//...
	 * 		),
	 * 		//...
	 * )
	 */
	public function getQueryLog() {
		return self::$queries[spl_object_hash($this)];
	}

	/**
	 * Get the number of queries of this connection
	 *
	 * @return int
	 */
	public function getQueryCount() {
		return count(self::$queries[spl_object_hash($this)]);
	}

	/**
	 * Get the summarized execution time of all queries of this connections
	 *
	 * @return float
	 */
	public function getQueryTimeSum() {
		$sum = 0;
		foreach (self::$queries[spl_object_hash($this)] as $query) {
			$sum += $query[0];
		}
		return $sum;
	}

	/**
	 * Fetch all rows as associative arrays
	 *
	 * @param $result
	 * @return array
	 */
	public function fetchAllAssoc($result) {
		$rows = array();
		while ($row = $this->fetchAssoc($result)) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Fetch all rows as numeric arrays
	 *
	 * @param $result
	 * @return array
	 */
	public function fetchAllRows($result) {
		$rows = array();
		while ($row = $this->fetchRow($result)) {
			$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * Send a database query
	 *
	 * This method will be called from AbstractDatabase::query()
	 * AbstractDatabase::query() will log the queries and check the
	 * execution time.
	 *
	 * @param string $query SQL
	 * @param array $parameters
	 * @return mixed The result of the query or FALSE on error
	 */
	abstract protected function _query($query, array $parameters = array());

	/**
	 * Connect to the database
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @throws Exception\SelectionException
	 * @throws Exception\ConnectionException
	 */
	abstract public function __construct($host, $user, $pass, $db);


	/**
	 * Count the number of rows in a table filtered by conditions
	 *
	 * @param string $field Parenthesized field name
	 * @param string $table Table name
	 * @param string $conditions conditions after "where"
	 * @return int
	 */
	abstract public function count($field, $table, $conditions = "");

	/**
	 * Fetch the first column of a specific row
	 *
	 * @param mixed $result The result of a query
	 * @param int $row
	 * @return mixed
	 */
	abstract public function fetchFirstColumn($result, $row = 0);

	/**
	 * Count the number of selected rows by the given query
	 *
	 * @param mixed $result The result of a query
	 * @return int
	 */
	abstract public function countRows($result);

	/**
	 * Fetch one row as an associative array
	 *
	 * @param mixed $result The result of a query
	 * @return array Associative array
	 */
	abstract public function fetchAssoc($result);

	/**
	 * Fetch one row as a numeric array
	 *
	 * @param mixed $result The result of a query
	 * @return array Numeric array
	 */
	abstract public function fetchRow($result);

	/**
	 * Get the last inserted auto increment id
	 *
	 * @return int
	 */
	abstract public function getLastId();

	/**
	 * Implementation of \PDO::quote()
	 *
	 * @see http://php.net/manual/en/pdo.quote.php
	 *
	 * @param $value
	 * @return string
	 */
	abstract public function quote($value);

}