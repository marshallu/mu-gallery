<?php
/**
 * Required Advanced Custom Fields information for the plugin.
 *
 * @package mu-gallery
 */

if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_60216f9349902',
		'title' => 'Image Meta',
		'fields' => array(
			array(
				'key' => 'field_60216f94a1124',
				'label' => 'Image Link to URL',
				'name' => 'mu_gallery_external_url',
				'type' => 'url',
				'instructions' => 'If you wish to link from a gallery to an external link, you can include a URL here.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'attachment',
					'operator' => '==',
					'value' => 'image',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

	endif;
