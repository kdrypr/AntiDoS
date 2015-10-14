<?php

/**
 * HTTP DoS attack and flood blocking system using Apache htaccess configuration file.
 *
 * @package AntiDoS
 * @author Titan http://www.kodevreni.com/
 * @copyright 2015 Kod Evreni
 * @license The MIT License (MIT)
 *
 * @version 1.0.0.0
 */

namespace{
	class AntiDoS{
		public $db;
		public $db_prefix;
		
		public $script_dir;
		public $htaccess;
		
		public $ip;
		public $ban_time;
		public $keep_time;
		public $reset_request;
		public $max_request;
		
		public function __construct(){
			$debug = debug_backtrace();
			
			require_once('AntiDoS_config.php');
			
			$this->db = new \PDO('mysql:host=' . $CONFIG['DB_SERVER'] . ';dbname=' . $CONFIG['DB_NAME'], $CONFIG['DB_USER'], $CONFIG['DB_PASSWORD']);
			$this->db_prefix = $CONFIG['DB_PREFIX'];
			$this->table_logs = '`' . $this->db_prefix . 'logs`';
			
			$this->script_dir = dirname($debug[count($debug) - 1]['file']);
			$this->htaccess[] = empty($_CONFIG['HTACCESS_PATH']) ? $this->script_dir . DIRECTORY_SEPARATOR . '.htaccess' : $_CONFIG['HTACCESS_PATH'];

			$this->ip = $_SERVER['REMOTE_ADDR'];
			$this->ban_time = $CONFIG['BAN_TIME'];
			$this->keep_time = $CONFIG['KEEP_TIME'];
			$this->reset_request = $CONFIG['RESET_REQUEST'];
			$this->max_request = $CONFIG['MAX_REQUEST'];
		}
		
		public function Process($cron = false){
			if(!$cron){
				if(!$this->LogIP($this->ip)){
					$this->BanIP($this->ip);
				}
			}
			
			$this->UnBanExceedIps();
			$this->PruneLogs();
		}
		
		public function BanIP($ip){	
			$ip_escape = preg_quote($ip);
		
			foreach($this->htaccess as $file){
				$read = @file_get_contents($file);
				
				if(!$read || !preg_match('/^\s*deny\s+from\s+' . $ip_escape . '\s*$/m', $read)){
					file_put_contents($file, "\r\n" . 'deny from ' . $ip, FILE_APPEND);
				}
			}
		}
		
		public function UnBanIP($ip){
			$ip_escape = preg_quote($ip);
			
			foreach($this->htaccess as $file){
				$read = @file_get_contents($file);
				
				if($read && preg_match('/^\s*deny\s+from\s+' . $ip_escape . '\s*$/m', $read)){
					$unban_string = preg_replace('/^\s*deny\s+from\s+' . $ip_escape . '\s*$/m', '', $read);
					
					unlink($file);
					file_put_contents($file, $unban_string);
				}
			}
		}
		
		public function UnBanAll(){
			foreach($this->htaccess as $file){
				$read = @file_get_contents($file);
				
				if($read && preg_match('/^\s*deny\s+from\s+.+$/m', $read)){
					$unban_string = preg_replace('/^\s*deny\s+from\s+.+$/m', '', $read);
					
					unlink($file);
					file_put_contents($file, $unban_string);
				}
			}
			
			$query = $this->db->exec('TRUNCATE ' . $this->table_logs);
		}
		
		public function BannedIPs(){
			$BannedIPs = array();
			
			foreach($this->htaccess as $file){
				$read = @file_get_contents($file);
				$parse = preg_match_all('/^\s*deny\s+from\s+(\S+)\s*$/m', $read, $matches);
				
				if($read && $parse){
					foreach($matches[1] as $ip){
						$BannedIPs[] = $ip;
					}
				}
			}
			
			return $BannedIPs;
		}
		
		public function BanExceedIps(){
			$BanExceedIps = array();
			$BannedIPs = $this->BannedIPs();
			
			foreach($BannedIPs as $ip){
				$query = $this->db->prepare('SELECT `last`, `count` FROM ' . $this->table_logs . ' WHERE `ip`=:ip');
				
				$query->execute(array(
					'ip' => $ip
				));
				
				$fetch = $query->fetch();
				
				if(!$query->rowCount() || $fetch['last'] + $this->ban_time < time()){
					$BanExceedIps[] = $ip;
				}
			}
			
			return $BanExceedIps;
		}
		
		public function UnBanExceedIps(){
			$BanExceedIps = $this->BanExceedIps();
			
			foreach($BanExceedIps as $ip){
				$this->UnBanIP($ip);
			}
		}
		
		public function LogIP($ip){			
			$query = $this->db->prepare('SELECT `ip`, `first`, `last`, `count` FROM ' . $this->table_logs . ' WHERE `ip`=:ip');
			
			$query->execute(array(
					'ip' => $ip
			));
			
			if($query->rowCount()){
				$fetch = $query->fetch();
				
				if(time() - $fetch['first'] > $this->reset_request){
					$this->ResetLog($ip);
				}
				elseif($fetch['count'] > $this->max_request){
					return false;
				}
				else{					
					$query = $this->db->prepare('UPDATE ' . $this->table_logs . ' SET `last`=:last, `count`=:count WHERE `ip`=:ip');
					
					$query->execute(array(
							'last' => time(),
							'count' => ++$fetch['count'],
							'ip' => $ip
					));
				}
			}
			else{
				$this->ResetLog($ip);
			}
			
			return true;
		}
		
		public function ResetLog($ip){			
			$query = $this->db->prepare('INSERT INTO ' . $this->table_logs . ' (`ip`, `first`, `last`, `count`) VALUES (:ip, :first, :last, :count) ON DUPLICATE KEY UPDATE `first`=:first, `last`=:last, `count`=:count');
			
			$query->execute(array(
					'ip' => $ip,
					'first' => time(),
					'last' => time(),
					'count' => 1
				));
		}	
		
		public function PruneLogs(){
			if($this->keep_time){
				$query = $this->db->prepare('DELETE FROM ' . $this->table_logs . ' WHERE `last`<:old');
				
				$query->execute(array(
						'old' => time() - $this->keep_time
				));
			}
		}
	}
}