<?php

/**
 * Caches calls to wp_nav_menu().
 */
class Pj_Cached_Nav_Menus {
	public static $cache_menus = [];

	public static function load() {
		add_filter( 'pre_wp_nav_menu', [ __CLASS__, 'pre_wp_nav_menu' ], 10, 2 );
		add_filter( 'wp_nav_menu', [ __CLASS__, 'maybe_cache_nav_menu' ], 10, 2 );
		add_action( 'wp_update_nav_menu', [ __CLASS__, 'clear_caches' ] );
		add_action( 'customize_save', [ __CLASS__, 'clear_caches' ] );

		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::add_command( 'dev menu remove_caches', [ __CLASS__, 'remove_caches' ] );
		}
	}

	private static function _cache_key( $args ) {
		$_args = (array) $args;
		unset( $_args['menu'] );

		return 'pj-cached-nav-menu:' . md5( json_encode( $_args ) );
	}

	private static function _timestamp() {
		static $timestamp;
		if ( ! isset( $timestamp ) ) {
			$timestamp = get_option( 'pj-cached-nav-menus-timestamp', 0 );
		}

		return $timestamp;
	}

	public static function pre_wp_nav_menu( $output, $args ) {
		if ( ! empty( $args->menu ) ) {
			return $output;
		}

		$cache_key           = self::_cache_key( $args );
		self::$cache_menus[] = $cache_key;

		$cache = get_option( $cache_key );
		if ( is_array( $cache ) && $cache['timestamp'] >= self::_timestamp() ) {
			$output = $cache['html'] . '<!-- pj-cached-nav-menu -->';
		}

		return $output;
	}

	public static function maybe_cache_nav_menu( $html, $args ) {
		$cache_key = self::_cache_key( $args );

		if ( ! in_array( $cache_key, self::$cache_menus ) ) {
			return $html;
		}

		$cache = [
			'html'      => $html,
			'timestamp' => time(),
		];

		update_option( $cache_key, $cache );

		return $html;
	}

	public static function clear_caches() {
		update_option( 'pj-cached-nav-menus-timestamp', time() );
	}

	public static function remove_caches() {
		global $wpdb;

		$rows = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name RLIKE 'pj-cached-nav-menu:'" );

		if ( is_int( $rows ) ) {
			WP_CLI::log( "Количество удалённый кешей меню: $rows" );
		}
	}
}

Pj_Cached_Nav_Menus::load();
