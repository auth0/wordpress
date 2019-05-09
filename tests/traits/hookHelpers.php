<?php
/**
 * Contains Trait HookHelpers.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Trait HookHelpers.
 */
trait HookHelpers {

	/**
	 * Get all hooked functions from a hook name.
	 *
	 * @param string $hook - Hook to check.
	 *
	 * @return array
	 */
	public function get_hook( $hook = '' ) {
		global $wp_filter;

		if ( isset( $wp_filter[ $hook ]->callbacks ) ) {
			array_walk(
				$wp_filter[ $hook ]->callbacks,
				function( $callbacks, $priority ) use ( &$hooks ) {
					foreach ( $callbacks as $id => $callback ) {
						$hooks[] = array_merge(
							[
								'id'       => $id,
								'priority' => $priority,
							],
							$callback
						);
					}
				}
			);
		} else {
			return [];
		}

		foreach ( $hooks as &$item ) {

			if ( ! is_callable( $item['function'] ) ) {
				continue;
			}

			if ( is_array( $item['function'] ) ) {
				$item['function'] = array(
					is_object( $item['function'][0] ) ? get_class( $item['function'][0] ) : $item['function'][0],
					$item['function'][1],
				);
			} elseif ( ! is_string( $item['function'] ) && is_callable( $item['function'] ) ) {
				$item['function'] = get_class( $item['function'] );
			}
		}

		return $hooks;
	}

	/**
	 * Remove all hooked functions from a hook.
	 *
	 * @param string $hook - Hook to clear.
	 */
	public function clear_hooks( $hook = '' ) {
		global $wp_filter;
		unset( $wp_filter[ $hook ] );
	}

	/**
	 * Assert that hooked functions exists with the correct priority and arg numbers.
	 *
	 * @param string $hook_name - Hook name in WP.
	 * @param string $function - Function name, typically the class name.
	 * @param array  $hooked - Array of functions to check.
	 *
	 * @return void
	 */
	public function assertHookedClass( $hook_name, $function, array $hooked ) {
		$hooks = $this->get_hook( $hook_name );
		$found = 0;

		foreach ( $hooks as $hook ) {

			if ( ! is_array( $hook['function'] ) ) {
				continue;
			}

			if ( $function !== $hook['function'][0] ) {
				continue;
			}

			$method_name = $hook['function'][1];

			if ( ! empty( $hooked[ $method_name ] ) ) {
				$this->assertEquals( $hooked[ $method_name ]['priority'], $hook['priority'] );
				$this->assertEquals( $hooked[ $method_name ]['accepted_args'], $hook['accepted_args'] );
				$found++;
			}
		}
		$this->assertEquals( count( $hooked ), $found );
	}

	/**
	 * Assert that hooked functions exists with the correct priority and arg numbers.
	 *
	 * @param string $hook_name - Hook name in WP.
	 * @param array  $hooked - Array of functions to check.
	 *
	 * @return void
	 */
	public function assertHookedFunction( $hook_name, array $hooked ) {
		$hooks = $this->get_hook( $hook_name );
		$found = 0;

		foreach ( $hooks as $hook ) {

			if ( is_array( $hook['function'] ) ) {
				continue;
			}

			$function_name = $hook['function'];

			if ( ! empty( $hooked[ $function_name ] ) ) {
				$this->assertEquals( $hooked[ $function_name ]['priority'], $hook['priority'] );
				$this->assertEquals( $hooked[ $function_name ]['accepted_args'], $hook['accepted_args'] );
				$found++;
			}
		}
		$this->assertEquals( count( $hooked ), $found );
	}
}
