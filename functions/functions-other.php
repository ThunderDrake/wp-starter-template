<?php

// Задаёт основные настройки темы.
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
} );

/**
 * Проверяет, отображался шаблон уже или пока нет.
 *
 * @param string $path
 *
 * @return bool
 */
function has_template_already_displayed( $path ) {
	static $list = [];

	if ( in_array( $path, $list, true ) ) {
		return true;
	}

	$list[] = $path;

	return false;
}

/**
 * Формирует CSS классы для тега body.
 *
 * @param string[]|string $classes
 *
 * @return string
 */
function theme_body_classes( $in_classes = null ) {
	$out_classes   = explode( ' ', $in_classes );
	$out_classes[] = is_user_logged_in() ? 'site-logged-user' : 'site-not-logged-user';
	$out_classes[] = is_admin_bar_showing() ? 'site-with-admin-bar' : 'site-without-admin-bar';

	return implode( ' ', array_filter( $out_classes ) );
}


function remove_admin_bar_32px() {
	remove_action( 'wp_head', '_admin_bar_bump_cb' );
}

add_action( 'get_header', 'remove_admin_bar_32px' );


/**
 * Возвращает значение GET параметра в виде массива числовых данных.
 *
 * @param string $key
 *
 * @return int[]
 */
function get_param_ids_for_filter( $key ) {
	return array_filter( wp_parse_id_list( get_param_for_filter( $key ) ) );
}

/**
 * Возвращает значение GET параметра в виде массива строковых данных.
 *
 * @param string $key
 *
 * @return string[]
 */
function get_param_strings_for_filter( $key ) {
	return array_filter( wp_parse_slug_list( get_param_for_filter( $key ) ) );
}

/**
 * Возвращает значение GET параметра.
 *
 * @param string $key
 * @param string $default
 *
 * @return mixed
 */
function get_param_for_filter( $key, $default = '' ) {
	return $_GET[ $key ] ?? $default;
}

/**
 * Получает данные для построения табов в архивах новостей, сми и мероприятий.
 *
 * @return array[]
 */
function get_filter_tabs_for_templates(): array {
	return [
		[
			'title'  => 'Новости',
			'url'    => get_news_archive_url(),
			'active' => is_news_archive(),
		],
	];
}


/**
 * Получает массив данных таксономии post_tag, привязанный к конкретному post_type.
 *
 * @param string $post_type
 *
 * @return array
 */
function post_type_tags( $post_type = '' ) {
	global $wpdb;

	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	return $wpdb->get_results( $wpdb->prepare( "
        SELECT COUNT( DISTINCT tr.object_id )
            AS count, tt.taxonomy, tt.description, tt.term_taxonomy_id, t.name, t.slug, t.term_id
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr
            ON p.ID=tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt
            ON tt.term_taxonomy_id=tr.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t
            ON t.term_id=tt.term_taxonomy_id
        WHERE p.post_type=%s
            AND tt.taxonomy='post_tag'
        GROUP BY tt.term_taxonomy_id
        ORDER BY count DESC
    ", $post_type ) );
}

/**
 * Проверяет, разрабатывается ли сайт на локалке или нет.
 *
 * Сработает, если указан один из вариантов константы в файле wp-config.php:
 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
 * define( 'WP_ENVIRONMENT_TYPE', 'development' );
 *
 * @return bool
 */
function is_dev_site() {
	return in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
}

add_filter( 'show_admin_bar', 'admin_bar_for_editors_and_dev_site', 99 );

/**
 * Для админа и редакторов тулбар оставить, для остальных - скрыть (кроме DEV версии сайт).
 *
 * @param bool $show_admin_bar
 *
 * @return bool
 */
function admin_bar_for_editors_and_dev_site( $show_admin_bar ) {
	if ( is_dev_site() ) {
		return true;
	}

	if ( $show_admin_bar && ! current_user_can( 'edit_others_posts' ) ) {
		return false;
	}

	return $show_admin_bar;
}

add_action( 'admin_bar_menu', 'replace_wordpress_howdy', 25 );

/**
 * Изменяет "Привет" на "Здравствуйте" в тулбаре.
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function replace_wordpress_howdy( $wp_admin_bar ) {
	$my_account = $wp_admin_bar->get_node( 'my-account' );

	if ( isset( $my_account->title ) ) {
		$newtext = str_replace( 'Привет,', 'Здравствуйте,', $my_account->title ?? '' );

		$wp_admin_bar->add_node( [
			'id'    => 'my-account',
			'title' => $newtext,
		] );
	}

}
