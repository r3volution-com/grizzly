<?php
include(dirname(__FILE__)."/../include/logindb.php");
class DB {
	var $db;
	function DB(){
		global $db_host, $db_user, $db_pass, $db_name;
		$this->db = new mysqli($db_host, $db_user, $db_pass, $db_name);
		if ($this->db->connect_error) {
			die('Error de Conexión ('.$this->db->connect_errno.') '.$this->db->connect_error);
		}
	}
	function getRow($query){
		$res = $this->db->query($query);
		if ($res) return $res->fetch_assoc();
	}
	function getField($query, $field){
		$res = $this->db->query($query);
		if ($res) $row = $res->fetch_assoc();
		if (isset($row[$field])) return $row[$field];
		else return "";
	}
	function getArray($query){
		$array = array();
		$res = $this->db->query($query);
		while ($row = $res->fetach_assoc()){
			$array[] = $row;
		}
		return $array;
	}
	function insertQuery($query){
		$res = $this->db->query($query);
		return $this->db->insert_id;
	}
	function query($query){
		$res = $this->db->query($query);
		if (!$res) die($this->db->error);
		return $res;
	}
}