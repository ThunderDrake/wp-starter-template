<?php

namespace Axelot;

class Hide {

	public function __construct() {
		// Убирает notice для переключения обратно на Админа.
		// Это можно сделать и в выпадающем меню профиля админбара.
		if ( isset( $GLOBALS['user_switching'] ) ) {
			remove_action( 'all_admin_notices', [ $GLOBALS['user_switching'], 'action_admin_notices' ], 1 );
		}

		// Отключает все стандартные виджеты WordPress.
		remove_action( 'init', 'wp_widgets_init', 1 );
		// Отключает только некоторые виджеты (надо выбрать только ВСЕ выше или некоторые тут).
		// $this->unregister_basic_widgets();

		remove_action( 'admin_print_scripts-index.php', 'wp_localize_community_events' );
		add_action( 'admin_head', [ $this, 'remove_wp_help_tab' ] );

		add_filter( 'admin_footer_text', function() {
			return '<div class="admin-footer-copyright">Разработано <a href="http://insaim.design/?utm_source=case&utm_medium=footer_link&utm_campaign=axelot-tp" target="_blank">студией INSAIM</a>.<br>Нашли баг или есть предложение по доработке? <a href="/">Напишите нам</a> :)</div>';
		} );
		add_filter( 'update_footer', '__return_empty_string', 11 );
		add_filter( 'pre_site_transient_php_check_' . md5( phpversion() ), '__return_empty_array' );
		add_filter( 'pre_option_https_migration_required', '__return_empty_string' );

		add_action( 'admin_menu', [ $this, 'remove_items_admin_menu' ] );
		add_action( 'admin_menu', [ $this, 'move_items_admin_menu' ] );

		add_action( 'add_admin_bar_menus', [ $this, 'admin_bar' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'dashboard' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'disable_full_mode_in_gutenberg' ] );

		add_filter( 'intermediate_image_sizes', [ $this, 'delete_intermediate_image_sizes' ] );

		$this->remove_emoji();
	}

	/**
	 * Удаляет из админ-сайдбара пункты меню.
	 */
	public function remove_items_admin_menu() {
		remove_menu_page( 'edit.php' );          // Записи
		remove_menu_page( 'edit-comments.php' ); // Комментарии
	}

	/**
	 * В админ-сайдбаре перемещает пункты меню.
	 */
	public function move_items_admin_menu() {
		global $menu;

		$separator = $menu[4];

		$menu[19] = $separator;

		// Перемещаем "Медиафайлы"
		$menu[22] = $menu[10];
		unset( $menu[10] );
	}

	/**
	 * Отключает создание миниатюр файлов для указанных размеров.
	 *
	 * @param array $sizes
	 *
	 * @return array
	 */
	function delete_intermediate_image_sizes( $sizes ) {
		return array_diff( $sizes, [
			'medium_large',
		] );
	}

	/**
	 * Изменяет базовый набор элементов (ссылок) в тулбаре.
	 *
	 * @return void
	 */
	function admin_bar() {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_customize_menu', 40 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_search_menu', 4 );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu' );
		remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
	}

	/**
	 * Удаляет виджеты из Консоли WordPress.
	 *
	 * @return void
	 */
	function dashboard() {
		$dash_side   = &$GLOBALS['wp_meta_boxes']['dashboard']['side']['core'];
		$dash_normal = &$GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'];

		unset( $dash_side['dashboard_quick_press'] );         // Быстрая публикация
		unset( $dash_side['dashboard_recent_drafts'] );       // Последние черновики
		unset( $dash_side['dashboard_primary'] );             // Блог WordPress
		unset( $dash_side['dashboard_secondary'] );           // Другие Новости WordPress

		unset( $dash_normal['dashboard_incoming_links'] );    // Входящие ссылки
		unset( $dash_normal['dashboard_right_now'] );         // Прямо сейчас
		unset( $dash_normal['dashboard_recent_comments'] );   // Последние комментарии
		unset( $dash_normal['dashboard_plugins'] );           // Последние Плагины
		unset( $dash_normal['dashboard_activity'] );          // Активность
		unset( $dash_normal['dashboard_site_health'] );       // Здоровье сайта

		unset( $dash_normal['wpseo-dashboard-overview'] );    // Обзор публикаций Yoast SEO

		remove_action( 'welcome_panel', 'wp_welcome_panel' ); // Виджет "Добро пожаловать"
	}

	/**
	 * Удаляет табы-помощники.
	 *
	 * @return void
	 */
	function remove_wp_help_tab() {
		$screen = get_current_screen();

		if ( $screen ) {
			$screen->remove_help_tabs();
		}
	}

	public function remove_emoji() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	/**
	 * Выборочно отключает стандартные виджеты WordPress.
	 * Код оставлен на случай надобности такой возможности.
	 *
	 * @return void
	 */
	public function unregister_basic_widgets() {
		unregister_widget( 'WP_Widget_Pages' );            // Виджет страниц
		unregister_widget( 'WP_Widget_Calendar' );         // Календарь
		unregister_widget( 'WP_Widget_Archives' );         // Архивы
		unregister_widget( 'WP_Widget_Links' );            // Ссылки
		unregister_widget( 'WP_Widget_Meta' );             // Мета виджет
		unregister_widget( 'WP_Widget_Search' );           // Поиск
		unregister_widget( 'WP_Widget_Text' );             // Текст
		unregister_widget( 'WP_Widget_Categories' );       // Категории
		unregister_widget( 'WP_Widget_Recent_Posts' );     // Последние записи
		unregister_widget( 'WP_Widget_Recent_Comments' );  // Последние комментарии
		unregister_widget( 'WP_Widget_RSS' );              // RSS
		unregister_widget( 'WP_Widget_Tag_Cloud' );        // Облако меток
		unregister_widget( 'WP_Nav_Menu_Widget' );         // Меню
		unregister_widget( 'WP_Widget_Media_Audio' );      // Audio
		unregister_widget( 'WP_Widget_Media_Video' );      // Video
		unregister_widget( 'WP_Widget_Media_Gallery' );    // Gallery
		unregister_widget( 'WP_Widget_Media_Image' );      // Image
		unregister_widget( 'WP_Widget_Custom_HTML' );      // Произвольный HTML код
		unregister_widget( 'WP_Widget_Block' );            // Блок
	}

	/**
	 * Отключает полноэкранный режим в блочном редакторе.
	 */
	public function disable_full_mode_in_gutenberg() {
		ob_start();
		?>

		<script>
			window.onload = function () {
				const isFullscreenMode = wp.data.select('core/edit-post').isFeatureActive('fullscreenMode');
				if (isFullscreenMode) {
					wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');
				}
			}
		</script>

		<?php
		wp_add_inline_script( 'wp-blocks', str_replace( [ '<script>', '</script>' ], '', ob_get_clean() ) );
	}

}

new Hide();
