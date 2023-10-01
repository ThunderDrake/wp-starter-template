<?php

namespace AxelotTP;

/**
 * РАСПРЕДЕЛЕНИЕ ШАБЛОНОВ отображения контента в зависимости от раздела сайта.
 * Данный файл создан, чтобы хранить шаблоны в стуктурированных папках и произвольными именами, а не в корне движка.
 */
class Routes {

	public function __construct() {
		add_filter( 'template_include', [ $this, 'replace_path' ], 99 );
	}

	/**
	 * Возвращает измененный путь к тому или иному шаблону.
	 *
	 * @param string $template путь к шаблону по умолчанию
	 *
	 * @return string
	 */
	public function replace_path( string $template ): string {
		if ( is_page_template() ) {
			$page_template = get_page_template_slug( get_queried_object_id() );
			$page_template = locate_template( $page_template );

			if ( $page_template && file_exists( $page_template ) ) {
				return $template;
			}
		}

		/***************
		 *** Частные ***
		 ***************/
		if ( is_front_page() ) {
			return $this->locate_template( 'front-page/front-page.php' );
		}

		if ( is_404() ) {
			return $this->locate_template( 'page-404/page-404.php' );
		}

		/***************
		 *** Общие ***
		 ***************/


		return $template;
	}

	/**
	 * Проверяет наличие указанного шаблона и заменяет им дефолтный шаблон.
	 *
	 * @param string  $path     Пользовательский путь к шаблону
	 *
	 * @return string
	 * @global string $template Дефолтный путь к шаблону
	 */
	public function locate_template( string $path ): string {
		global $template;

		$path = "templates/{$path}";

		// Проверяем наличие файла шаблона по указанному пути
		if ( $new_template = locate_template( [ $path ] ) ) {
			$template = $new_template;
		}

		return $template;
	}

}
