<?php

class WP_Auth0_Options_Generic {
    protected $options_name = '';
    private $_opt = null;

    public function get_options_name() {
        return $this->options_name;
    }

    public function get_options(){
        if(empty($this->_opt)){
            $options = get_option( $this->options_name, array());

            if(!is_array($options))
                $options = $this->defaults();

            $options = array_merge( $this->defaults(), $options );

            $this->_opt = $options;
        }
        return $this->_opt;
    }

    public function get( $key, $default = null ){
        $options = $this->get_options();

        if(!isset($options[$key]))
            return apply_filters( 'wp_auth0_get_option', $default, $key );
        return apply_filters( 'wp_auth0_get_option', $options[$key], $key );
    }

    public function set( $key, $value ){
        $options = $this->get_options();
        $options[$key] = $value;
        $this->_opt = $options;
        update_option( $this->options_name, $options );
    }

    protected function defaults(){
        return array();
    }
}
