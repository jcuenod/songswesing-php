<?php

class dbconnection
{
	protected $mysqli;

	public function __construct($username = "root", $password = "rootly", $dbname = "songswesing", $host = "localhost")
	{
		$this->mysqli = new mysqli($host, $username, $password, $dbname);
		if ($this->mysqli->connect_errno) {
   			printf("Connect failed: %s\n", $this->mysqli->connect_error);
 			exit();
		}
		$this->mysqli->set_charset ("utf8");
	}

	public function query_for_assoc($sql, $field_to_fetch=false)
	{
		if (empty($sql))
			return;

		$this->mysqli->real_query($sql);
		$res = $this->mysqli->use_result();

		$retval = array();
		if (!$field_to_fetch)
		{
			while ($row = $res->fetch_assoc()) {			
    			$retval[] = $row;
			}
		}
		else
		{
			while ($row = $res->fetch_assoc())
			{
				$retval[] = $row[$field_to_fetch];
			}
		}
		
		$res->close();
		return $retval;
	}

	public function just_query($sql)
	{
		if (empty($sql))
			return;

		$ret = $this->mysqli->query($sql);
		return $this->mysqli->insert_id ? $this->mysqli->insert_id : $ret;
	}
}

?>