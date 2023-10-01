<?php

namespace AxelotTP;

class Helper {

	private static $instance;

	public static function getInstance(): Helper {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function hooks(): void {

	}

	/**
	 * Cuts the specified text up to specified number of characters.
	 * Strips any of shortcodes.
	 *
	 * @param string|array $args              {
	 *                                        Optional. Arguments to customize output.
	 *
	 * @type int           $maxchar           Макс. количество символов.
	 * @type string        $text              Текст который нужно обрезать. По умолчанию post_excerpt, если нет post_content.
	 *                                         Если в тексте есть `<!--more-->`, то `maxchar` игнорируется и берется
	 *                                         все до `<!--more-->` вместе с HTML.
	 * @type bool          $autop             Заменить переносы строк на `<p>` и `<br>` или нет?
	 * @type string        $more_text         Текст ссылки `Читать дальше`.
	 * @type string        $save_tags         Теги, которые нужно оставить в тексте. Например `'<strong><b><a>'`.
	 * @type string        $sanitize_callback Функция очистки текста.
	 * @type bool          $ignore_more       Нужно ли игнорировать <!--more--> в контенте.
	 *
	 * }
	 *
	 * @return string HTML
	 * @author  Kama (wp-kama.ru)
	 *
	 * @version 2.7.1
	 *
	 */
	public function trim_words( $args = '' ): string {
		if ( is_string( $args ) ) {
			parse_str( $args, $args );
		}

		$rg = (object) array_merge( [
			'maxchar'           => 350,
			'text'              => '',
			'save_tags'         => '',
			'sanitize_callback' => 'strip_tags',
		], $args );

		$text = $rg->text;

		// strip content shortcodes: [foo]some data[/foo]. Consider markdown
		$text = preg_replace( '~\[([a-z0-9_-]+)[^\]]*\](?!\().*?\[/\1\]~is', '', $text );
		// strip others shortcodes: [singlepic id=3]. Consider markdown
		$text = preg_replace( '~\[/?[^\]]*\](?!\()~', '', $text );
		$text = trim( $text );

		$text = 'strip_tags' === $rg->sanitize_callback
			? strip_tags( $text, $rg->save_tags )
			: call_user_func( $rg->sanitize_callback, $text, $rg );

		$text = trim( $text );

		// cut
		if ( mb_strlen( $text ) > $rg->maxchar ) {
			$text = mb_substr( $text, 0, $rg->maxchar );
			$text = preg_replace( '/(.*)\s[^\s]*$/s', '\\1...', $text ); // del last word, it not complate in 99%
		}

		return $text;
	}

	/**
	 * Склонение слова после числа.
	 *
	 *     // Примеры вызова:
	 *     num_decline( $num, 'книга,книги,книг' )
	 *     num_decline( $num, 'book,books' )
	 *     num_decline( $num, [ 'книга','книги','книг' ] )
	 *     num_decline( $num, [ 'book','books' ] )
	 *
	 * @param int|string   $number      Число после которого будет слово. Можно указать число в HTML тегах.
	 * @param string|array $titles      Варианты склонения или первое слово для кратного 1.
	 * @param bool         $show_number Указываем тут 00, когда не нужно выводить само число.
	 *
	 * @return string Например: 1 книга, 2 книги, 10 книг.
	 *
	 * @version 3.0
	 */
	public function plural_form( $number, $titles, $show_number = 1 ) {

		if ( is_string( $titles ) ) {
			$titles = preg_split( '/, */', $titles );
		}

		// когда указано 2 элемента
		if ( empty( $titles[2] ) ) {
			$titles[2] = $titles[1];
		}

		$cases = [ 2, 0, 1, 1, 1, 2 ];

		$intnum = abs( (int) strip_tags( $number ) );

		$title_index = ( $intnum % 100 > 4 && $intnum % 100 < 20 )
			? 2
			: $cases[ min( $intnum % 10, 5 ) ];

		return ( $show_number ? "$number " : '' ) . $titles[ $title_index ];
	}

	/**
	 * Преобразование номера телефона в формат ссылки
	 *
	 * @param string $phone
	 *
	 * @return string
	 */
	public function format_phone( $phone ) {
		return str_replace( [ ' ', '(', ')', '-' ], '', $phone );
	}
}
