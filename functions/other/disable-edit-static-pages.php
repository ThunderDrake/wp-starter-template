<?php

/**
 * Возвращает список страниц, не доступных для редактирования.
 *
 * @return string[]
 */
function get_list_disable_fo_edit_static_pages() {
	return [
		//'career',
	];
}

/**
 * Мониторит открытие статичной страницы на редактирование и выводит предупреждение.
 */
function disable_edit_static_pages() {
	global $pagenow, $typenow;

	if ( is_admin() && 'post.php' === $pagenow && 'page' === $typenow ) {
		$post_id = $_GET['post'] ?? null;

		if ( $post_id && ( $post = get_post( $post_id ) ) ) {
			$text = [
				'<p>Эта страница статичная и не предполагает, чтобы в ней что-либо редактировали.</p>',
				'<p>Если Вам нужно что-то изменить, обратитесь к разработчикам сайта.</p>',
				sprintf( '<p><a href="%s">Вернуться обратно к списку страниц</a></p>', admin_url( 'edit.php?post_type=page' ) ),
			];

			if ( in_array( $post->post_name, get_list_disable_fo_edit_static_pages() ) ) {
				wp_die( implode( '', $text ) );
			}
		}
	}
}

add_action( 'current_screen', 'disable_edit_static_pages' );

/**
 * Добавляет статичным страницам лейбл об этом в таблице странице в админке.
 *
 * @param string[] $post_states An array of post display states.
 * @param WP_Post  $post        The current post object.
 *
 * @return mixed
 */
function set_state_for_static_pages( $post_states, $post ) {
	if ( in_array( $post->post_name, get_list_disable_fo_edit_static_pages() ) ) {
		$post_states[] = 'Редактируется через разработчиков';
	}

	return $post_states;
}

add_filter( 'display_post_states', 'set_state_for_static_pages', 9, 2 );
