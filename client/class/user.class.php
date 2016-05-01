<?php
include(dirname(__FILE__)."/db.mysql.class.php");
class User {
	var $db;
	var $uid;
	var $usid;
	var $userdata;
	function User($uid = 0){
		$this->db = new DB();
		$this->uid = 0;
		if ($uid){
			$row = $this->db->getRow("SELECT id, username, email, image FROM users WHERE id=".$uid);
			if (count($row)){
				$this->uid = $row["id"];
				$this->userdata = $row;
				return true;
			} else die("Error el usuario no existe");
		}
	}
	function doLogin($username, $password){
		$row = $this->db->getRow("SELECT id, username, email FROM users WHERE (username = '".$username."' OR email = '".$username."') AND password = '".hash("sha512", $password)."'");
		if (count($row)){
			$this->uid = $row["id"];
			$this->userdata = $row;
			$_SESSION["uid"] = $this->getUID();
			$this->usid = session_id();
			$this->db->query("UPDATE users SET session_id = '".$this->usid."' WHERE id = ".$this->uid);
			return true;
		} else return false;
	}
	function doRegister($username, $email, $passwd){
		$count = $this->db->getField("SELECT count(*) as cuantos FROM users WHERE username = '".$username."' OR email = '".$email."'", "cuantos");
		if ($count) return 0;
		else return $this->db->insertQuery("INSERT INTO users (username, email, password) VALUES ('".$username."', '".$email."', '".hash("sha512", $passwd)."')");
	}
	function recoverPassword($email){
		if ($this->db->getField("SELECT count(*) as cuantos FROM users WHERE email = '".$email."'", "cuantos")){
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			$pass = array();
			$alphaLength = strlen($alphabet) - 1;
			for ($i = 0; $i < 8; $i++) {
				$n = rand(0, $alphaLength);
				$pass[] = $alphabet[$n];
			}
			$this->db->query("UPDATE users SET password = '".hash('sha512',$pass)."' WHERE email='".$email."'");
			return implode($pass);
		} else return "";
	}
	//HICE ESTO, POR SI CAGADITA LO COMENTo
	function updatePassword($newpasswd){
		if($this->db->query("UPDATE users SET password = '".hash('sha512',$newpasswd)."' WHERE id=".$this->uid)) return true;
		else return false;
	}
	function updateEmail($email){
		if($this->db->query("UPDATE users SET email = '".$email."' WHERE id=".$this->uid)) return true;
		else return false;
	}
	function updateProfilePic($image){
		if($this->db->query("UPDATE users SET image = '".$image."' WHERE id=".$this->uid)) return true;
		else return false;
	}
	function getUID(){
		return $this->uid;
	}
	function getSessionID(){
		return $this->usid;
	}
	function getUserData(){
		return $this->userdata;
	}
}