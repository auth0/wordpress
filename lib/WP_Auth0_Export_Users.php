<?php

class WP_Auth0_Export_Users {

    public static function init() {
        add_action('admin_footer', array( __CLASS__, 'a0_add_users_export'));
        add_action('load-users.php', array( __CLASS__, 'a0_export_selected_users'));
    }

	public static function a0_add_users_export() {
	    $screen = get_current_screen();
	    if ( $screen->id != "users" )   // Only add to users.php page
	        return;
	    ?>
	    <script type="text/javascript">
	        jQuery(document).ready(function($) {
	            $('<option>').val('a0_users_export').text('Export users profile').appendTo("select[name='action']");
	        });
	    </script>
	    <?php
	}

    public static function a0_export_selected_users() {
        if(isset($_GET['action']) && $_GET['action'] === 'a0_users_export' && isset($_GET['users'])) {
            $user_ids = $_GET['users'];

            if ($user_ids) {
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename=users_export.csv');
                header('Pragma: no-cache');

                $users = WP_Auth0_DBManager::get_auth0_users($user_ids);

                echo self::process_str("email", true);
                echo self::process_str("nickname", true);
                echo self::process_str("name", true);
                echo self::process_str("givenname", true);
                echo self::process_str("gender", true);
                echo self::process_numeric("age", true);
                echo self::process_numeric("latitude", true);
                echo self::process_numeric("longitude", true);
                echo self::process_numeric("zipcode", true);
                echo self::process_numeric("income", true);
                echo self::process_numeric("country_code", true);
                echo self::process_numeric("country_name", true);
                echo self::process_str("idp", true);
                echo self::process_str("created_at", true);
                echo self::process_str("last_login", true);
                echo self::process_numeric("logins_count", false);
                echo "\n";

                foreach ($users as $user) {
                    $profile = new WP_Auth0_UserProfile($user->auth0_obj);

                    echo self::process_str($profile->get_email(), true);
                    echo self::process_str($profile->get_nickname(), true);
                    echo self::process_str($profile->get_name(), true);
                    echo self::process_str($profile->get_givenname(), true);
                    echo self::process_str($profile->get_gender(), true);
                    echo self::process_numeric($profile->get_age(), true);
                    echo self::process_numeric($profile->get_latitude(), true);
                    echo self::process_numeric($profile->get_longitude(), true);
                    echo self::process_numeric($profile->get_zipcode(), true);
                    echo self::process_numeric($profile->get_income(), true);
                    echo self::process_numeric($profile->get_country_code(), true);
                    echo self::process_numeric($profile->get_country_name(), true);
                    echo self::process_str(implode('|', $profile->get_idp()), true);
                    echo self::process_str($profile->get_created_at(), true);
                    echo self::process_str($profile->get_last_login(), true);
                    echo self::process_numeric($profile->get_logins_count(), false);
                    echo "\n";

                }

                exit;
            }

        }
    }

    protected static function process_str($attr, $coma) {
        return ( !empty( $attr ) ? '"'.$attr.'"' : '' ). ( $coma ? ',' : '' );
    }

    protected static function process_numeric($attr, $coma) {
        return ( !empty( $attr ) ? $attr : '' ). ( $coma ? ',' : '' );
    }


}
