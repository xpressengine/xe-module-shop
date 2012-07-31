<?php

class Database {

	private static $mysqli;

	private static function connect()
	{
		if(!isset(self::$mysqli))
		{
			$db_info = include dirname(__FILE__).'/db.config.php';
			self::$mysqli = new mysqli(
								$db_info['db_hostname']
								, $db_info['db_userid']
								, $db_info['db_password']
								, $db_info['db_database']
								, (int)$db_info['db_port']
			);
		}

		/* check connection */
		if (self::$mysqli->connect_errno) {
			throw new Exception(sprintf("Connect failed: %s\n", self::$mysqli->connect_error));
		}
	}

	public static function executeQuery($query)
	{
		self::connect();

		if ($result = self::$mysqli->query($query)) {
			$result_data = array();
			while($obj = $result->fetch_object()){
				$result_data[] = $obj;
			}

			/* free result set */
			$result->close();
		}

		return $result_data;
	}

	public static function executeNonQuery($query)
	{
		self::connect();

		if (self::$mysqli->query($query) === TRUE) {
			return true;
		}
		return false;
	}

	public function __destruct()
	{
		self::$mysqli->close();
	}



}