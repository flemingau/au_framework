<?php
interface DB
{
    public function connect();
    public function select_db();
    public function error();
    public function errno();
    public static function escape_string($string);
    public function query($query);
    public function multi_query($query);
    public function real_query($query);
    public function fetch_array($result);
    public function fetch_row($result);
    public function fetch_assoc($result);
    public function fetch_object($result);
    public function num_rows($result);
    public function store_result();
    public function use_result();
    public function more_results();
    public function next_result();
    public function insert_id();
    public function free_result($result);
    public function close();
} 

class Database implements DB
{
	private  $link;
	
	public function connect($server='', $username='', $password='', $new_link=true, $client_flags=0)
	{
		$this->link = mysqli_connect($server, $username, $password, $new_link, $client_flags);
	}
	
	public function select_db($database='')
	{
		return mysqli_select_db($database, $this->link);
	}
	
	public function errno()
	{
		return mysqli_errno($this->link);
	}
	
	public function error()
	{
		return mysqli_error($this->link);
	}
	
	public static function escape_string($string)
	{
		return mysqli_real_escape_string($string);
	}
	
	public function query($query)
	{
		return mysqli_query($this->link, $query);
	}
	
	public function real_query($query)
	{
		return mysqli_real_query($this->link, $query);
	}
	
	public function multi_query($query)
	{
		return mysqli_multi_query($this->link, $query);
	}
	
	public function use_result()
	{
		return mysqli_use_result($this->link);
	}
	
	public function store_result()
	{
		return mysqli_store_result($this->link);
	}
	
	public function more_results()
	{
		return mysqli_more_results($this->link);
	}
	
	public function next_result()
	{
		return mysqli_next_result($this->link);
	}
	
	public function fetch_array($result, $array_type = MYSQL_BOTH)
	{
		return mysqli_fetch_array($result, $array_type);
	}
	
	public function fetch_row($result)
	{
		return mysqli_fetch_row($result);
	}
	
	public function fetch_assoc($result)
	{
		return mysqli_fetch_assoc($result);
	}
	
	public function fetch_object($result)
	{
		return mysqli_fetch_object($result);
	}
	
	public function free_result($result)
	{
		return mysqli_free_result($result);
	}
	
	public function num_rows($result)
	{
		return mysqli_num_rows($result);
	}
	
	public function insert_id() {
		return mysqli_insert_id($this->link);
	}
	
	public function close()
	{
		return mysqli_close($this->link);
	}
	
}
?>