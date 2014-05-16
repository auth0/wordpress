<?php

class WP_Auth0_Utils {
	public static function log($event, $description, $details = '', $level = 'notice'){			
		global $wpdb;
		
		if(!is_string($details))
			$details = print_r($details, true);
		
		$wpdb->query($wpdb->prepare("INSERT INTO `$wpdb->auth0_log`(`event`, `level`, `description`, `details`, `logtime`) VALUES(%s, %s, %s, %s, %d);", $event, $level, $description, $details, time()));
	}
	
	public static function log_crash(){
		$log_all = false;
		$error = error_get_last();
        if($error !== NULL && ( $log_all || strpos($error['file'], 'wp-content/plugins/auth0-sso') !== false ) ){
            $info = PHP_EOL."-- [CRASH REPORT] --".PHP_EOL."\tFile \t=>\t".$error['file'].PHP_EOL."\tLine \t=>\t".$error['line'].PHP_EOL."\tMessage =>\t".$error['message'];
			self::log("Auth0", "Abnormal shutdown in Auth0 SSO Plugin!", $info, "fatal");
        }
	}
}