<?php

namespace AxelotTP;

class Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'attach_assets' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'attach_assets' ] );
	}

	public function attach_assets() {
		// HTML Love
		$this->attach_style( '/assets/build/css/vendor.css' );
		$this->attach_style( '/assets/build/css/main.css' );
		$this->attach_script( '/assets/build/js/main.js' );

		// Custom
		$this->attach_style( '/custom/custom.css' );
		$this->attach_script( '/custom/custom.js', [ 'jquery' ] );
	}

	private function attach_style( $path, $deps = [] ) {
		wp_enqueue_style( $this->get_handle( $path ), $this->get_url( $path ), $deps, $this->get_ver( $path ) );
	}

	private function attach_script( $path, $deps = [] ) {
		wp_enqueue_script( $this->get_handle( $path ), $this->get_url( $path ), $deps, $this->get_ver( $path ), true );
	}

	private function get_handle( $path ) {
		return sanitize_title( $path );
	}

	private function get_url( $path ) {
		return wp_normalize_path( get_theme_file_uri( $path ) );
	}

	private function get_ver( $path ) {
		return filemtime( get_theme_file_path( $path ) );
	}

}
