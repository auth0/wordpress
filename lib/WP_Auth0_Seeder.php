<?php

class WP_Auth0_Seeder {

  public function init() {
    add_action( 'template_redirect', array( $this, 'seed' ), 1 );
  }

  public function seed() {
		WP_Auth0_Seeder::get_me(100);
		exit;
  }

  public static function get_me($amount) {
    global $wpdb;
		$providers = array(
			'auth0',
			'facebook',
			'twitter',
			'google-oauth2',
			'linkedin'
		);

    $postal_codes = array();
    for($a = 0; $a<10; $a++) {
      $postal_codes[] = rand(10000, 90000);
    }

		for ($a = 0; $a < $amount; $a++) {
			$rand = rand(1,1000);
			$rand2 = rand(0,9);
			$gender = array('male','female');
			$age = array(15,20,31,55,40,27);

			$userinfo = (object)array(
				'email' => 'sarlanga' .  $rand . '@pepe.pa.pe.po',
				'name' => 'dummy'.$rand,
				'nickname' => 'dummy'.$rand,
				'user_id' => rand(0,100000 ),
				'age' => $age[rand(0,5)],
				'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-'. rand(1,60) . ' days')),
				'gender' => $gender[rand(0,1)],
				'user_metadata' => (object)array(
					'geoip' => (object)array(

						'latitude' => rand(0, 180) * (rand(0,9) >5 ?1 : -1),
						'longitude' => rand(0, 180) * (rand(0,9) >5 ?1 : -1),
						'postal_code' => ($rand2 > 3 ? null : $postal_codes[rand(0, 9)]),
						'country_code' => ($rand2 > 3 ? null : 'US'),
						'country_name' => ($rand2 > 3 ? null : 'USA'),

					),
					'zipcode_income' => ($rand2 > 3 ? null : rand(10000, 90000)),
				),
				'identities' => (object)array(
					(object)array(
						'provider' => $providers[rand(0,4)]
					)
				)
			);
			$user_id = WP_Auth0_Users::create_user($userinfo);


			$wpdb->insert(
					$wpdb->auth0_user,
					array(
							'auth0_id' => $userinfo->user_id,
							'wp_id' => $user_id,
							'auth0_obj' => WP_Auth0_Serializer::serialize($userinfo),
							'last_update' =>  date( 'c' ),
					),
					array(
							'%s',
							'%d',
							'%s',
							'%s',
							'%s',
							'%s',
					)
			);
		}
  }
}
