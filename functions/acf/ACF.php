<?php

namespace AxelotTP;

class ACF {
	private $json_dir_path;

	public function __construct() {
		$this->json_dir_path = get_parent_theme_file_path( '/functions/acf/groups-and-fields' );

		$this->hooks();
	}

	public function hooks() {
		add_filter( 'acf/settings/load_json', [ $this, 'set_dir_json_for_load' ] );
		add_filter( 'acf/settings/save_json', [ $this, 'set_dir_json_for_save' ] );

		// Скрываем админку ACF на продекшене.
		if ( ! is_dev_site() ) {
			add_filter( 'acf/settings/show_admin', '__return_false' );
		}
	}

	/**
	 * Изменяет путь к папке с json-конфигурациями групп полей при чтении.
	 *
	 * @return array
	 */
	public function set_dir_json_for_load() {
		return (array) $this->json_dir_path;
	}

	/**
	 * Изменяет путь к папке с json-конфигурациями групп полей при загрузке.
	 *
	 * @return string
	 */
	public function set_dir_json_for_save() {
		wp_mkdir_p( $this->json_dir_path );

		return $this->json_dir_path;
	}

}
