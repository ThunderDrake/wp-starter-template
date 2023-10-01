<?php


namespace AxelotTP;

class Site {

	private static $instance;

	private $static_url;

	private function __construct() {
		$this->static_url = esc_url( get_template_directory_uri() . '/assets/build' );
	}

	public static function getInstance(): Site {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function hooks() {
		//
	}

	/**
	 * Возвращает путь к папке со статическими файлами (css/js/fonts/images).
	 *
	 * @return string
	 */
	public function get_assets_url(): string {
		return $this->static_url;
	}

	/**
	 * Alias for get_assets_url().
	 *
	 * @return string
	 */
	public function get_static_url(): string {
		return $this->get_assets_url();
	}

	/**
	 * Подключает шаблон хедера сайта.
	 *
	 * @param array|string[] $args
	 *
	 * @param array          $args
	 */
	public function header( $args = [] ) {
		$args = $this->normalize_template_args( $args );

		$name = $args['tpl'] ?: '/parts/header/header.php';

		do_action( 'get_header', $name, $args );

		$this->template( $name, $args );
	}

	/**
	 * Подключает шаблон футера сайта.
	 *
	 * @param array|string[] $args
	 */
	public function footer( $args = [] ): void {
		$args = $this->normalize_template_args( $args );

		$name = $args['tpl'] ?: '/parts/footer/footer.php';

		do_action( 'get_footer', $name, $args );

		$this->template( $name, $args );
	}

	/**
	 * Приводит параметры шаблона к единому виду.
	 *
	 * @param array|string $args
	 *
	 * @return string[]
	 */
	private function normalize_template_args( $args = [] ): array {
		if ( is_string( $args ) ) {
			$args = [ 'tpl' => $args ];
		}

		return array_merge(
			[ 'class' => null, 'tpl' => null ],
			$args
		);
	}

	public function template( $slug, $args = [] ): void {
		$slug = wp_normalize_path(
			trim(
				str_replace( '.php', '', "/templates/$slug" ),
				'/'
			)
		);

		get_template_part( $slug, null, $args );
	}

	/**
	 * Выводит на экран содержимое SVG-файла.
	 *
	 * @param $slug
	 *
	 * @return void
	 */
	public function show_svg( $slug ): void {
		$path = wp_normalize_path( get_template_directory() . "/assets/build/img/$slug" );

		include $path;
	}

}
