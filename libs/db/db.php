<?php
/*
JSONDatabase class written by Mitchell Urgero <info@urgero.org>
GitHub: https://github.com/mitchellurgero/jsondatabase
===============================================================
This DB class supports the following functions:

init("DATABASE_NAME", "DATABASE_LOCATION" = null); //Load or create new database, then select it. (Optionally give a location to store the databse)
insert("TABLE_NAME", '{"data":"in","JSON":"format"}', int = null);//Insert or add a new row into given table (Optional 3rd option: replace given row number) 
select("TABLE_NAME", "WHERE" = null, "EQUALS" = null);//get data from selected row
create_table("TABLE_NAME");//Create a new table with the given name.
delete_table("TABLE_NAME");//Delete the given table.
dump_tables();//Dump all tables AND their data (Mostly for backup purposes.)
check_table("TABLE_NAME");//Check if a table exists. Returns number of rows if exists
list_tables();//List all available tables in selected database.
import("JSON_STRING_OF_DB_BACKUP");//import a database backup and restore into the given database.

Things to note:
===============
- row_id is the row number. This is not written to the db BUT generated on the fly. and always exists as a part of the row data
- dump_tables() will always take a little bit of time because it dumps ALL THE TABLES TO AN ARRAY.

How To Use
==========
include('db.php');
$db = new JSONDatabase("DATABASE_NAME");
$db->functionName(options);


*/
class JSONDatabase {
	public $db = '';
	function __construct($dbf = null, $dbd = null){
		if($dbf !== null){
			return self::init($dbf, $dbd);
		}
		return false;
	}
	public function init($dbf, $dbd = null){
		if($dbd !== null){
			//We need to change the directory of where the DB is stored!! Also remove any trailing slashes ;)
			$dbd = rtrim($dbd, '/');
			$dbf = join(DIRECTORY_SEPARATOR, array($dbd, $dbf));//Join or implode work here, join is shorter to type though
		}
		if (!file_exists($dbf) && $dbf !== null) {
    		mkdir($dbf, 0777, true);
    		mkdir($dbf."/tables", 0777, true); //Tables dir
    		mkdir($dbf."/tmp", 0777, true); //Temp dir for caching
    		mkdir($dbf."/gc", 0777, true); //Garbage Collection
    		$this->db = $dbf;
    		return true;
		} else if($dbf === null) {
			//Shit.
			return false;
		}else if(file_exists($dbf)){
			//Open db.
			$this->db = $dbf;
			return true;
		}
		return false;
	}
	public function insert($table, $data, $row = null){
		//Insert row into table at specified int/string.
		$num = 0;
		if($row === null){
			$rows = glob($this->db."/tables/$table" . '/*' , GLOB_ONLYDIR);
			$num = count($rows);
		} else {
			$num = $row;
		}
		$d = json_decode($data,true);
		foreach($d as $key=>$value){
			if($key == "row_id"){ continue;}
			if(!file_exists($this->db."/tables/$table/$num")){
				mkdir($this->db."/tables/$table/$num",0777, true);
			}
			file_put_contents($this->db."/tables/$table/$num/".$key, $value);
		}
		return $num;
	}
	public function select($table, $where = null, $equals = null, $invert = true){
		//Get data of row.
		if(is_int($where) && is_int($equals)){
			//We are pagenating, need to get all rows between the rows.
			$range = range($where, $equals);
			$rangeReturn = array();
			foreach($range as $r){
				$rangeReturn[$r] = self::select($table,"row_id",$r)[$r];
			}
			return $rangeReturn;
		}
		if($where == "row_id"){
			if(file_exists($this->db."/tables/$table/".$equals)){
				$dbd = glob($this->db."/tables/$table/".$equals."/*");
				foreach($dbd as $key){
					$k = basename($key);
					$data[$equals][$k] = file_get_contents($this->db."/tables/$table/".$equals."/$k");
				}
				$data[$equals]['row_id'] = $equals;
				return $data;
			} else {
				return false;
			}
		}
		$rows = glob($this->db."/tables/$table" . '/*' , GLOB_ONLYDIR);
		if($invert){
			$rows = array_reverse($rows);
		}
		$data = array();
		$i = 0;
		if($equals === null && $where === null){
			//Return all the rows :D
			foreach($rows as $row){
				//We now need to read the row and return it as a PHP object.
				$dbd = glob($this->db."/tables/$table/".basename($row)."/*");
				foreach($dbd as $key){
					$k = basename($key);
					$data[$i][$k] = file_get_contents($this->db."/tables/$table/".basename($row)."/$k");
				}
				$data[$i]['row_id'] = basename($i);
				$i++;
			}
			return $data;
		}
		foreach($rows as $row){
			//Return only rows that contain the search query.
			$t1 = "";
			if(file_exists($this->db."/tables/$table/".basename($row)."/$where")){
				$t1 = file_get_contents($this->db."/tables/$table/".basename($row)."/$where");
			} else {
				continue;
			}
			if($t1 == $equals){
				//We now need to read the row and return it as a PHP object.
				$dbd = glob($this->db."/tables/$table/".basename($row)."/*");
				foreach($dbd as $key){
					$k = basename($key);
					$data[$i][$k] = file_get_contents($this->db."/tables/$table/".basename($row)."/$k");
				}
				$data[$i]['row_id'] = basename($row);
			} else {
				//Might add something here for gc, but for now, do nothing and continue.
			}
			
			$i++;
		}
		return $data;
	}
	public function create_table($table){
		//Duh
		mkdir($this->db."/tables/$table", 0777, true);
		return true;
	}
	public function delete_table($table){
		//Duh
		if (!file_exists($this->db."/tables/$table")) {
			return false; //Folder not there.
		} else if($table === null) {
			//Shit.
			return false; //No folder specified.
		}else if(file_exists($this->db."/tables/$table")){
			//Open db.
			self::deleteDir($this->db."/tables/$table");
			return true;
		} else {
			return false;
		}
	}
	public function dump_tables(){
		$tables = glob($this->db."/tables" . '/*' , GLOB_ONLYDIR);
		$data;
		foreach($tables as $table){
			$t = basename($table);
			$data[$t] = self::select($t);
		}
		return json_encode($data, JSON_PRETTY_PRINT);
	}
	public function import($db_string){
		$data = json_decode($db_string, true);
		foreach($data as $table=>$data){
			if(!self::check_table($table)){
				self::create_table($table);
				foreach($data as $d){
					unset($d['row_id']); //Remove row_id because we generate this in other functions.
					self::insert($table, json_encode($d));
				}
			}
		}
		
	}
	public function list_tables(){
		$tables = glob($this->db."/tables" . '/*' , GLOB_ONLYDIR);
		$t = array();
		foreach($tables as $tt){
			$a = basename($tt);
			array_push($t, $a);
		}
		return $t;	
	}
	public function check_table($table){
		if (!file_exists($this->db."/tables/$table")){
			return false;
		} else {
			return count(glob($this->db."/tables/$table" . '/*' , GLOB_ONLYDIR));
		}
	}
	public function delete_row($table, $row){
		//Duh
		//Doesn't exactly work properly yet.
		//Since this doesn't work properly YET - disabled for now. use insert to replace row data to blank out the row instead.
		return false;
		if (!file_exists($this->db."/tables/$table")) {
			return false; //Folder not there.
		} else if($table === null) {
			//Shit.
			return false; //No folder specified.
		}else if(file_exists($this->db."/tables/$table")){
			//Duh
			if (!file_exists($this->db."/tables/$table/$row")) {
				return false; //Folder not there.
			} else if($row === null) {
				//Shit.
				return false; //No folder specified.
			}else if(file_exists($this->db."/tables/$table/$row")){
				//Open db.
				self::deleteDir($this->db."/tables/$table/$row");
				
				$rows = glob($this->db."/tables/$table" . '/*' , GLOB_ONLYDIR);
				foreach($rows as $r){
					$r = basename($r);
					if($r > $row){
						rename($this->db."/tables/$table/$r", $this->db."/tables/$table/".$r - 1);
					}
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
		return false;
	}
	public function clean($table){
		//Cleans table of blank rows. Keeps shit tidy.
	}
	//Helper functions
	function deleteDir($dirPath) {
	    if (! is_dir($dirPath)) {
	        throw new InvalidArgumentException("$dirPath must be a directory");
	    }
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	        $dirPath .= '/';
	    }
	    $files = glob($dirPath . '*', GLOB_MARK);
	    foreach ($files as $file) {
	        if (is_dir($file)) {
	            self::deleteDir($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($dirPath);
	}
	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}
