<?php
/**
 * Этот шаблон используется, когда не нашлось подходящего (свёрстанного для проекта).
 */

if ( ! current_user_can( 'update_core' ) ) {
	return;
}

@get_header();
?>

	<h2 id="title-header">К сожалению, не найден шаблон для отображения этой страницы</h2>

	<script>
		// Query Monitor Template List
		addEventListener('load', function () {
			let h2 = document.querySelector("#title-header");
			let box = document.querySelector("#qm-response > div > section:nth-child(3) > ol");

			if (box && h2) {
				h2.appendChild(box.cloneNode(true));
			}
		})
	</script>

<?php
@get_footer();
