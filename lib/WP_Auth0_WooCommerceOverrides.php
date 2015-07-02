<?php

class WP_Auth0_WooCommerceOverrides {

    public static function init() {
        add_filter( 'woocommerce_checkout_login_message', array(__CLASS__, 'override_woocommerce_checkout_login_form') );
        add_filter( 'woocommerce_before_customer_login_form', array(__CLASS__, 'override_woocommerce_login_form') );
    }

    public static function override_woocommerce_checkout_login_form( $html ){
        self::override_woocommerce_login_form($html);

        if (isset($_GET['wle'])) {
            echo "<style>.woocommerce-checkout .woocommerce-info{display:block;}</style>";
        }
    }

    public static function override_woocommerce_login_form( $html ){
        self::render_auth0_login_css();
        echo self::render_form('');
    }

}
