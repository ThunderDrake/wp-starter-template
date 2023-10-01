<?php

namespace AxelotTP\Dev;

use WP_CLI;

class Main {

	protected $action;

	public function __construct() {
		WP_CLI::add_command( 'dev post delete', [ $this, 'delete_posts' ] );
	}

	/**
	 * Удаляет записи указанного типа.
	 *
	 * wp dev post delete {name-cpt} [--all] - общая команда
	 * wp dev post delete course -> удалить все Курсы, кроме самого первого по дате (самый старый курс останется).
	 * wp dev post delete course --all -> удалить все Курсы без исключения.
	 *
	 * Чтобы уверенно осталось при удалении оригинальная записи, указывайте ей, к примеру, дату 1900 год.
	 * А при генерации фейковых записей им будет назначены (сгенерированы) более "высокие" даты.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function delete_posts( $args, $assoc_args ) {
		$ctp = $args[0] ?? '';
		$all = isset( $assoc_args['all'] );

		$post_ids = get_posts( [
			'post_type'   => $ctp,
			'numberposts' => - 1,
			'fields'      => 'ids',
			'order'       => 'ASC',
		] );

		if ( ! $all ) {
			array_shift( $post_ids );
		}

		if ( $post_ids ) {
			foreach ( array_chunk( $post_ids, 500 ) as $post_ids_part ) {
				WP_CLI::runcommand( sprintf( 'post delete %s --force', implode( ' ', $post_ids_part ) ) );
			}
		}
	}

	/**
	 * Возвращает рандомные ID записей указанного типа для ACF поля-relationship.
	 * Предусмотрено кеширование.
	 *
	 * @param int   $count Максимальное количество возвращаемых ID.
	 * @param array $args  Параметры запроса для get_posts().
	 *
	 * @return int[]|string
	 */
	function get_random_post_ids( $count, $args, $for_acf = true ) {
		$args = array_merge( [
			'post_type'   => 'teacher',
			'numberposts' => - 1,
			'fields'      => 'ids',
		], $args );

		$cache_key   = md5( maybe_serialize( $args ) );
		$cache_group = 'random_item_ids_query';
		$ids         = wp_cache_get( $cache_key, $cache_group );

		if ( false === $ids ) {
			$ids = get_posts( $args );
			wp_cache_set( $cache_key, $ids, $cache_group );
		}

		if ( ! $ids && ! is_array( $ids ) ) {
			return '';
		}

		shuffle( $ids );

		if ( $for_acf ) {
			$ids = array_map( 'strval', $ids );
		}

		return array_slice( $ids, 0, $count );
	}

	/**
	 * Возвращает рандомное значение из списка переданных.
	 *
	 * @param array $items Список, например ['base', 'advanced', 'master'].
	 *
	 * @return mixed Рандомный значение из списка, например advanced.
	 */
	public function get_random_item( $items ) {
		shuffle( $items );

		return $items[0];
	}

	/**
	 * Возвращает рандомные значения из списка переданных.
	 *
	 * @param int   $count Максимальное количество возвращаемых значений.
	 * @param array $items Список, например ['base', 'advanced', 'master'].
	 *
	 * @return array Рандомные значения списка, например ['base', 'master'].
	 */
	public function get_random_items( $count, $items ) {
		shuffle( $items );

		return array_slice( $items, 0, $count );
	}

	/**
	 * Возвращает все метаполя указаной записи.
	 *
	 * @param int $post_id
	 *
	 * @return \stdClass[]
	 */
	public function get_post_all_meta( $post_id ) {
		global $wpdb;

		$items = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );
		$metas = [];

		foreach ( $items as $item ) {
			$metas[ $item->meta_key ] = $item->meta_value;
		}

		return $metas;
	}

	/**
	 * Возвращает оригинальную запись для клонирования.
	 * Оригинальная запись - это самая первая создання запись указанного типа поста,
	 * поэтому чтобы уверенно её определить, указывайте ей при создании "маленькую" дату, к примеру, 1900 год.
	 *
	 * @param array $args
	 *
	 * @return \WP_Post|null
	 */
	public function get_post_for_clone( $args ) {
		$args = array_merge( [
			'numberposts' => 1,
			'order'       => 'ASC',
		], $args );

		return get_posts( $args )[0] ?? null;
	}

	/**
	 * Возвращает рандомную дату в формате "Y-m-d H:i:s" из промежутка дат.
	 *
	 * Указывать начало и конец надо в формате "Y-m-d",
	 * например "2009-01-31" (год-месяц-день, 31 января 2009 года).
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	public function get_random_date( $start, $end ) {
		return date( "Y-m-d H:i:s", random_int( strtotime( $start ), strtotime( $end ) ) );
	}

	protected function set_timer() {
		return microtime( true );
	}

	protected function get_timer( $start, $text = 'Время выполнения операции: ', $action = '' ) {
		$_start = microtime( true );

		$this->log( $text . number_format_i18n( $_start - $start, 2 ) . ' сек', $action );

		return $_start;
	}

	protected function show_main_timer( $start, $text = 'Общее время выполнения всех операций: ', $action = '' ) {
		return $this->get_timer( $start, $text, $action );
	}

	protected function log( $text = '', $action = '' ): void {
		$action = $action ?: $this->action;

		WP_CLI::log( $action ? "$action | $text" : $text );
	}

	protected function error( $text ): void {
		$this->log( "$this->action | $text" );
		exit();
	}

}
