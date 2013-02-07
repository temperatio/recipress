<?php


/**
 * Create Setting
 */
add_action( 'admin_init', 'recipress_init' );
function recipress_init(){
	register_setting( 'recipress_options', 'recipress_options', 'recipress_validate' );
}

/**
 * Returns the default settings
 * @depricated	v1.9.5	use recipress_options
 */
function recipress_get_options() {
	recipress_options( $field );
}

// get options field
function recipress_options( $field ) {
	$saved = ( array ) get_option( 'recipress_options' );
	$defaults = array(
		'autoadd'					=> true,
		'output'					=> array( 'single' ),
		'theme'						=> 'recipress-recipress',
		'use_photo'					=> true,
		'link_ingredients'			=> true,
		'instruction_image_size'	=> 'thumbnail',
		'post_type'					=> 'post',
		'taxonomies'				=> array( 'cuisine', 'course', 'skill_level' ),
		'cost_field'				=> false,
		'credit'					=> true,
		'paypal'					=> ''
	);

	$defaults = apply_filters( 'recipress_default_options', $defaults );

	$options = wp_parse_args( $saved, $defaults );
	$options = array_intersect_key( $options, $defaults );
	
	return $options[$field];
}


/* Create an array of option fields
------------------------------------------------------------------------- */
function recipress_options_fields() {
	$recipress_options_fields = array(
		'outputSection' => array(
			'label' => __( 'Recipe Output', 'recipress' ),
			'type' 	=> 'section'
		),
		'autoadd' => array(
			'label' => __( 'Automatically Output Recipe', 'recipress' ),
			'id' 	=> 'autoadd',
			'type' 	=> 'radio',
			'options'=> array(
				'true'	=> array(
					'label'	=> __( 'Yes', 'recipress' ),
					'value'	=> true,
					'desc'	=> __( 'Recipe will output after the post content', 'recipress' ),
					'default'=> true
				),
				'false'	=> array(
					'label'	=> __( 'No', 'recipress' ),
					'value'	=> false,
					'desc'	=> __( 'Use the <code>[recipe]</code> shortcode to output the recipe', 'recipress' )
				),
			),
			'sanitizer' => 'recipress_santitize_boolean'
		),
		'output' => array(
			'label' => __( 'Output Recipe On', 'recipress' ),
			'id' 	=> 'output',
			'type' 	=> 'checkbox_group',
			'options'=> array(
				'home'	=> array(
					'label'	=> __( 'Home Page/Latest Posts Page', 'recipress' ),
					'value'	=> 'home'
				),
				'single'	=> array(
					'label'	=> __( 'Single Post Page', 'recipress' ),
					'value'	=> 'single'
				),
				'archive'	=> array(
					'label'	=> __( 'Archive and Category Pages', 'recipress' ),
					'value'	=> 'archive'
				),
				'search'	=> array(
					'label'	=> __( 'Search Result Page', 'recipress' ),
					'value'	=> 'search'
				),
			),
			'sanitizer' => 'sanitize_text_field'
		),
		'theme' => array(
			'label' => __( 'Recipe Theme', 'recipress' ),
			'id' 	=> 'theme',
			'type' 	=> 'image_radio',
			'options'=> array(
				'recipress-light'	=> array(
					'label'	=> __( 'Light', 'recipress' ),
					'value'	=> 'recipress-light',
					'image'	=> RECIPRESS_URL . 'img/theme-light.jpg',
					'desc'	=> __( 'For use with light themes', 'recipress' ),
					'default' => true
				),
				'recipress-dark'	=> array(
					'label'	=> __( 'Dark', 'recipress' ),
					'value'	=> 'recipress-dark',
					'image'	=> RECIPRESS_URL . 'img/theme-dark.jpg',
					'desc'	=> __( 'For use with dark themes', 'recipress' )
				),
				'recipress-recipress'	=> array(
					'label'	=> __( 'ReciPress', 'recipress' ),
					'value'	=> 'recipress-recipress',
					'image'	=> RECIPRESS_URL . 'img/theme-recipress.jpg',
					'desc'	=> __( 'Custom textured design', 'recipress' )
				)
			),
			'sanitizer' => 'sanitize_text_field'
		),
		'link_ingredients' => array(
			'label' => __( 'Link Ingredients', 'recipress' ),
			'id' 	=> 'link_ingredients',
			'type' 	=> 'radio',
			'options'=> array(
				'yes'	=> array(
					'label'	=> __( 'Yes', 'recipress' ),
					'value'	=> true,
					'desc'	=> __( 'Recipe ingredients will link to a page with all recipes that call for that ingredient', 'recipress' ),
					'default'=> true
				),
				'no'	=> array(
					'label'	=> __( 'No', 'recipress' ),
					'value'	=> false,
					'desc'	=> __( 'Ingredients will be outputted as plain text with no links', 'recipress' )
				),
			),
			'sanitizer' => 'recipress_santitize_boolean'
		),
		'instruction_image_size' => array(
			'label'	=> __( 'Instruction Image Size', 'recipress' ),
			'id' 	=> 'instruction_image_size',
			'type' 	=> 'radio',
			'options'=> array(
				'thumbnail'	=> array(
					'label'	=> __( 'Thumbnail', 'recipress' ),
					'value'	=> 'thumbnail'
				),
				'medium'	=> array(
					'label'	=> __( 'Medium', 'recipress' ),
					'value'	=> 'medium'
				),
				'large'	=> array(
					'label'	=> __( 'Large', 'recipress' ),
					'value'	=> 'large',
					'default'=> true
				),
				'full'	=> array(
					'label'	=> __( 'Full', 'recipress' ),
					'value'	=> 'full'
				)
			),
			'sanitizer' => 'sanitize_text_field'
		),
		'inputSection' => array(
			'label' => __( 'Recipe Input', 'recipress' ),
			'type' 	=> 'section'
		),
		'post_type' => array(
			'label'	=> __( 'Post Type', 'recipress' ),
			'desc'	=> __( 'Name of post type to add recipes to', 'recipress' ),
			'size'	=> 'medium',
			'id'	=> 'post_type',
			'type'	=> 'text'
		),
		'taxonomies' => array(
			'label' => __( 'Use Taxonomies', 'recipress' ),
			'id' 	=> 'taxonomies',
			'type' 	=> 'checkbox_group',
			'options'=> array(
				'cuisine'	=> array(
					'label'	=> __( 'Cuisine', 'recipress' ),
					'value'	=> 'cuisine',
					'default'=> true
				),
				'course'	=> array(
					'label'	=> __( 'Course', 'recipress' ),
					'value'	=> 'course',
					'default'=> true
				),
				'skill_level'	=> array(
					'label'	=> __( 'Skill Level', 'recipress' ),
					'value'	=> 'skill_level',
					'default'=> true
				)
			),
			'sanitizer' => 'sanitize_text_field'
		),
		'cost_field' => array(
			'label' => __( 'Cost of Recipe Field', 'recipress' ),
			'id' 	=> 'cost_field',
			'type' 	=> 'radio',
			'options'=> array(
				'yes'	=> array(
					'label'	=> __( 'Yes', 'recipress' ),
					'value'	=> true,
					'desc'	=> __( 'An input for the total cost to make the recipe will be available', 'recipress' )
				),
				'no'	=> array(
					'label'	=> __( 'No', 'recipress' ),
					'value'	=> false,
					'desc'	=> __( 'This field will be omitted when creating recipes', 'recipress' ),
					'default'=> true
				),
			),
			'sanitizer' => 'recipress_santitize_boolean'
		),
		'thanksSection' => array(
			'label' => __( 'Say Thanks', 'recipress' ),
			'type' 	=> 'section'
		),
		'credit' => array(
			'label'	=> __( 'Plugin Credit', 'recipress' ),
			'type'	=> 'checkbox',
			'id'	=> 'credit',
			'desc'	=> __( 'Add a credit link to the recipe box.', 'recipress' ),
			'sanitizer' => 'recipress_santitize_boolean'
		),
		'paypal' => array(
			'label'	=> __( 'Buy me a latte!', 'recipress' ),
			'type'	=> 'paypal',
			'id'	=> 'paypal'
		)
	);

	return apply_filters( 'recipress_option_fields', $recipress_options_fields);
}

// post thumbnails
add_filter( 'recipress_option_fields', 'recipress_insert_thumbnails' );
function recipress_insert_thumbnails( $recipress_options_fields ) {
	$thumbnails = array(
		'thumbnails' => array(
			'label'	=> __( 'Use Featured Image', 'recipress' ),
			'id' 	=> 'use_photo',
			'type' 	=> 'radio',
			'options'=> array(
				'yes'	=> array(
					'label'	=> __( 'Yes', 'recipress' ),
					'value'	=> true,
					'desc'	=> __( 'Recipe photo will be the same as the post&rsquo;s featured image', 'recipress' ),
					'default'=> true
				),
				'no'	=> array(
					'label'	=> __( 'No', 'recipress' ),
					'value'	=> false,
					'desc'	=> __( 'Use a different image for the recipe', 'recipress' )
				),
			)
		)
	);
	
	if( current_theme_supports( 'post-thumbnails' ) ) 
    	return recipress_array_insert( $recipress_options_fields, 'theme', $thumbnails );
	else
		return $recipress_options_fields;
}

/* Create the options page
------------------------------------------------------------------------- */
function recipress_do_page() {
	$fields = recipress_options_fields();

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	echo '<div class="wrap">';
	screen_icon();
	echo '<h2>' . __( 'ReciPress Options', 'recipress' ) . '</h2>';

		if ( false !== $_REQUEST['settings-updated'] )
			echo '<div class="updated fade"><p><strong>' . __( 'Options saved', 'recipress' ) . '</strong></p></div>';

		echo '<form method="post" id="recipress_options" class="form-table" action="options.php">';
			settings_fields( 'recipress_options' );
			// start a loop through the fields
			$sections = 0;
			foreach ( $fields as $field) {
				
			// section titles
			if ( $field['type'] == 'section' ) {
				$sections++;
				if ( $sections > 1)
					echo '</table></div></div>';
				echo '<div class="postbox metabox-holder"><h3>' . esc_html( $field['label'] ) . '</h3>';
				// start a table
				echo '<div class="inside"><table class="form-table">';
			}
			
			else {
				// start a table row
				echo '<tr valign="top">
						<th scope="row"><label for="' . esc_attr( $field['id'] ) . '">' . esc_html( $field['label'] ) . '</label></th>
						<td>';
						
						$setting = recipress_options( $field['id'] );
						echo recipress_field( $field, $setting, true, 'recipress_options' );
					
				echo     '<td>
					</tr>';
			} // end if section else
		}  // end foreach
		echo '</table></div></div>';
		// close out the container
		echo '<p class="submit"><input type="submit" class="button-primary" value="' . __( 'Save Options', 'recipress' ) . '" /></p>
			</form></div>';
}


/* Sanitize and validate input
------------------------------------------------------------------------- */
function recipress_validate( $input ) {
	$fields = recipress_options_fields();

	foreach ( $fields as $field) {
		if ( $field['type'] == 'section' )
			continue;
			if( $field['type'] == 'section' ) {
				$sanitizer = null;
				continue;
			}
			
		$id = $field['id'];
		if ( isset( $input[$id] ) ) {
			$sanitizer = isset( $field['sanitizer'] ) ? $field['sanitizer'] : 'sanitize_text_field';
			if ( is_array( $input[$id] ) ) {
				$input[$id] = array_values( $input[$id] );
				$input[$id] = recipress_array_map_r( 'recipress_sanitize', $input[$id], $sanitizer );
			}
			else
				$input[$id] = recipress_sanitize( $input[$id], $sanitizer );
		}
		else
			$input[$id] = null;
	} // end foreach

	return $input;
}