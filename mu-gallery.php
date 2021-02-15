<?php
/**
 * MU Gallery
 *
 * This plugin was built to allow for Marshall University websites to display photo galleries.
 *
 * @package MU Gallery
 *
 * Plugin Name: MU Gallery
 * Plugin URI: https://www.marshall.edu
 * Description: A photo gallery plugin for Marshall University
 * Version: 1.0
 * Author: Christopher McComas
 */

if ( ! class_exists( 'ACF' ) ) {
	return new WP_Error( 'broke', __( 'Advanced Custom Fields is required for this plugin.', 'my_textdomain' ) );
}

require plugin_dir_path( __FILE__ ) . '/acf-fields.php';

/**
 * Flush rewrites whenever the plugin is activated.
 */
function mu_gallery_activate() {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mu_gallery_activate' );

/**
 * Flush rewrites whenever the plugin is deactivated, also unregister 'employee' post type and 'department' taxonomy.
 */
function mu_gallery_deactivate() {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mu_gallery_deactivate' );

/**
 * Remove the default WordPress gallery shortcode.
 */
remove_shortcode( 'gallery' );

/**
 * Build our own WordPress gallery shortcode
 *
 * @param array $atts The attributes included with the shortcode.
 * @return string
 */
function mu_custom_gallery( $atts ) {

	global $post;
	$pid     = $post->ID;
	$gallery = '';

	if ( empty( $pid ) ) {
		$pid = $post['ID'];
	}

	if ( ! empty( $atts['ids'] ) ) {
		$atts['orderby'] = 'post__in';
		$atts['include'] = $atts['ids'];
	}

	$data = shortcode_atts(
		array(
			'orderby'       => 'menu_order ASC, ID ASC',
			'include'       => '',
			'id'            => $pid,
			'columns'       => 3,
			'link'          => 'file',
			'class'         => '',
			'captions'      => false,
			'announcements' => false,
		),
		$atts
	);

	if ( $data['announcements'] ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'numberposts'    => -1,
			'meta_query'     => array( // phpcs:ignore
				'relation' => 'AND',
				array(
					'key'   => 'mymu_slides_add_to_mymu_slideshow', // phpcs:ignore
					'value' => true, // phpcs:ignore
				),
				array(
					'key'     => 'mymu_slides_expire_date',
					'value'   => date( 'Y-m-d h:i:s' ), // phpcs:ignore
					'type'    => 'DATETIME',
					'compare' => '>',
				),
			),
		);
	} else {
		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'orderby'        => $data['orderby'],
		);

		if ( ! empty( $data['include'] ) ) {
			$args['include'] = $data['include'];
		} else {
			$args['post_parent'] = $data['id'];
			$args['numberposts'] = -1;
		}

		if ( '' === $args['include'] ) {
			$args['orderby'] = 'date';
			$args['order']   = 'asc';
		}
	}

	if ( $data['columns'] <= 3 ) {
		$data['columns'] = 3;
	} elseif ( $data['columns'] >= 5 ) {
		$data['columns'] = 5;
	}

	$images = get_posts( $args );

	$output = '<div class="flex flex-wrap lg:-mx-4">';

	foreach ( $images as $image ) {

		if ( $data['announcements'] ) {
			$thumbnail = wp_get_attachment_image_src( $image->ID, 'large' );
			// echo '<pre>';
			// echo print_r( $thumbnail );
			// echo '</pre><br><br>';
			echo "THUMBNAIL: " . get_the_post_thumbnail_url($image->ID,'large');
			$thumbnail = $thumbnail[0];
		} else {
			$thumbnail = wp_get_attachment_image_src( $image->ID, 'large' );
			$thumbnail = $thumbnail[0];
		}

		$image_alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );

		$output .= '<div class="w-full lg:w-1/' . esc_attr( $data['columns'] ) . ' lg:px-4 my-4">';
		$output .= '<div x-data="{ imgModal : false, imgModalSrc : \'\', imgModalDesc : \'\' }" x-on:keydown.escape="imgModal = \'\'">';
		$output .= '<template @img-modal.window="imgModal = true; imgModalSrc = $event.detail.imgModalSrc; imgModalDesc = $event.detail.imgModalDesc;" x-if="imgModal">';
		$output .= '<div x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90" x-on:click.away="imgModalSrc = \'\'" class="p-2 fixed w-full h-100 inset-0 z-100 overflow-hidden flex justify-center items-center bg-black-overlay-30">';
		$output .= '<div x-on:click.away="imgModal = \'\'" class="flex flex-col max-w-3xl max-h-full overflow-auto">';
		$output .= '<div class="z-50">';
		$output .= '<button x-on:click="imgModal = \'\'" class="float-right pt-2 pr-2 outline-none focus:outline-none">';
		$output .= '<svg class="fill-current text-white h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18">';
		$output .= '<path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">';
		$output .= '</path>';
		$output .= '</svg>';
		$output .= '</button>';
		$output .= '</div>';
		$output .= '<div class="p-2">';
		$output .= '<img loading="lazy" :alt="imgModalSrc" class="object-contain h-1/2-screen" :src="imgModalSrc">';
		$output .= '<p x-text="imgModalDesc" class="text-center text-white"></p>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</template>';
		$output .= '</div>';
		$output .= '<div x-data="{}">';

		if ( get_field( 'mu_gallery_external_url', $image->ID ) ) {
			$output .= '<a href="' . get_field( 'mu_gallery_external_url', $image->ID ) . '"><img src="' . esc_url( $thumbnail ) . '" alt="' . esc_attr( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ) . '"></a>';
		} else {
			$output .= '<img x-on:click="$dispatch(\'img-modal\', {  imgModalSrc: \'' . esc_url( $thumbnail ) . '\', imgModalDesc: \'' . wp_kses_post( $image->post_excerpt ) . '\' })" src="' . esc_url( $thumbnail ) . '" alt="' . esc_attr( get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ) . '" class="cursor-pointer" />';
		}

		if ( $data['captions'] ) {
			$output .= '<div class="bg-gray-100 px-4 py-3 border border-t-0">' . esc_attr( $image->post_excerpt ) . '</div>';
		}

		$output .= '</div>';
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}
add_shortcode( 'gallery', 'mu_custom_gallery' );

/**
 * Removes unneeded form fields from the Dashboard for Gallery.
 */
function mu_gallery_hide_options() {
	echo '<style>';
	echo '.gallery-settings label[for=gallery-settings-link-to],
	.gallery-settings #gallery-settings-link-to,
	.gallery-settings label[for=gallery-settings-size],
	.gallery-settings #gallery-settings-size,
	.gallery-settings label[for=gallery-settings-random-order],
	.gallery-settings #gallery-settings-random-order {
		display: none;
	}';
	echo '</style>';
}
add_action( 'admin_head', 'mu_gallery_hide_options' );
