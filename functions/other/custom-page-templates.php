<?php
/**
 * Добавляем шаблоны, которые лежат глубже, чем может прочесть движок.
 *
 * @param string[] $templates
 *
 * @return string[]
 */
function add_templates_to_dropdown( $templates ) {

	// выбор шаблона в атрибутах
	// $templates['templates/page-documents/page-documents.php'] = 'Шаблон страницы "Документация"';

	return $templates;
}

add_filter( 'theme_page_templates', 'add_templates_to_dropdown' );
