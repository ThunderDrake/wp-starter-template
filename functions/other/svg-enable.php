<?php

add_filter( 'wp_prepare_attachment_for_js', 'svg_show_in_media_library' );
add_filter( 'wp_check_filetype_and_ext', 'svg_fix_mime_type', 10, 5 );
add_filter( 'upload_mimes', 'svg_upload_allow' );

/**
 * Позволяет отобразить SVG в медиабиблиотеки как картинку.
 *
 * @param array $response
 *
 * @return array
 */
function svg_show_in_media_library( $response ) {
	if ( $response['mime'] === 'image/svg+xml' ) {
		// С выводом названия файла
		$response['image'] = [
			'src' => $response['url'],
		];
	}

	return $response;
}

/**
 *
 * Исправляет MIME SVG.
 *
 * fix_svg_mime_type
 *
 * @param mixed $data
 * @param mixed $file
 * @param mixed $filename
 * @param mixed $mimes
 * @param mixed $real_mime
 *
 * @return void
 */
function svg_fix_mime_type( $data, $file, $filename, $mimes, $real_mime = '' ) {

	// WP 5.1 +
	if ( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ) {
		$dosvg = in_array( $real_mime, [ 'image/svg', 'image/svg+xml' ] );
	} else {
		$dosvg = ( '.svg' === strtolower( substr( $filename, - 4 ) ) );
	}

	// mime тип был обнулен, поправим его
	// а также проверим право пользователя
	if ( $dosvg ) {

		// разрешим
		if ( current_user_can( 'manage_options' ) ) {

			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		} // запретим
		else {
			$data['ext']  = false;
			$data['type'] = false;
		}

	}

	return $data;
}

/**
 * Добавляет SVG в список разрешенных для загрузки файлов.
 *
 * @param array $mimes
 *
 * @return array
 */
function svg_upload_allow( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}
