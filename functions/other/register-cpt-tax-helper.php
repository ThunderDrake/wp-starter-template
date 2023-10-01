<?php

/**
 * При регистрации CTP "ловит" и применяет параметры:
 * "Шаблон"
 * "Количество выводимых постов"
 * "Плейсхолдер"
 */
add_action( 'registered_post_type', function ( $post_type, $ctp_object ) {

	add_filter( 'template_include', function ( $templates ) use ( $ctp_object ) {
		// Задаём шаблон одиночной записи.
		if ( ! empty( $ctp_object->template_item ) && is_singular( $ctp_object->name ) ) {
			$templates = locate_template( $ctp_object->template_item );
		}

		// Задаём шаблон архиву записей.
		if ( ! empty( $ctp_object->template_archive ) && is_post_type_archive( $ctp_object->name ) ) {
			$templates = locate_template( $ctp_object->template_archive );
		}

		return $templates;
	} );

	// Устанавливаем количество выводимых записей в архивах.
	// Поддерживается свойсва 'posts_per_page_front' (приоритет) и 'posts_per_page' (fallback).
	add_action( 'pre_get_posts', function ( $query ) use ( $ctp_object ) {
		$ppp_front = $ctp_object->posts_per_page_front ?? null;
		$ppp_front = $ppp_front ?: ( $ctp_object->posts_per_page ?? null );

		if (
			! empty( $ppp_front )
			&& ! is_admin()
			&& $query->is_main_query()
			&& $query->is_post_type_archive( $ctp_object->name )
		) {
			$query->set( 'posts_per_page', $ppp_front );
		}
	} );

	// Скрываем выбор количества постов в админке для типа записи, если он указывается программно.
	add_action( 'admin_head-edit.php', static function () use ( $ctp_object ) {
		global $typenow;

		if ( $typenow === $ctp_object->name && isset( $ctp_object->posts_per_page_admin ) ) {
			?>
			<style>
				#screen-meta .screen-options {
					display: none;
				}
			</style>
			<?php
		}
	} );

	// Устанавливаем количество постов в таблице в админке.
	add_filter( "edit_{$ctp_object->name}_per_page", static function ( $posts_per_page ) use ( $ctp_object ) {
		$ppp_admin = $ctp_object->posts_per_page_admin ?? $posts_per_page;

		return $ppp_admin === - 1 ? 1000 : $ppp_admin;
	} );

	// Устанавливаем плейсхолдер в поле Заголовок на странице редактирования записи
	add_filter( 'enter_title_here', function ( $text, $post ) use ( $ctp_object ) {
		if ( isset( $ctp_object->labels->title_placeholder ) && $post->post_type === $ctp_object->name ) {
			$text = $ctp_object->labels->title_placeholder;
		}

		return $text;
	}, 11, 2 );

}, 10, 2 );

/**
 * При регистрации Таксономии "ловит" и применяет параметры "Шаблон" и "Количество выводимых постов".
 */
add_action( 'registered_taxonomy', function ( $taxonomy, $object_type, $taxonomy_object ) {
	add_filter( 'template_include', function ( $templates ) use ( $taxonomy, $taxonomy_object ) {
		// Задаём шаблон термину.
		if ( ! empty( $taxonomy_object['template_item'] ) && is_tax( $taxonomy ) ) {
			$templates = locate_template( $taxonomy_object['template_item'] );
		}

		return $templates;
	} );

	// Устанавливаем количество выводимых записей в архивах.
	add_action( 'pre_get_posts', function ( $query ) use ( $taxonomy, $taxonomy_object ) {
		if (
			! empty( $taxonomy_object['posts_per_page'] )
			&& ! is_admin()
			&& $query->is_main_query()
			&& $query->is_tax( $taxonomy )
		) {
			$query->set( 'posts_per_page', $taxonomy_object['posts_per_page'] );
		}
	} );

	// Устанавливаем количество терминов в таблице в админке.
	add_filter( "edit_{$taxonomy}_per_page", static function ( $posts_per_page ) use ( $taxonomy_object ) {
		$ppp_admin = $taxonomy_object['terms_per_page_admin'] ?? $posts_per_page;

		return $ppp_admin === - 1 ? 1000 : $ppp_admin;
	} );

}, 10, 3 );
