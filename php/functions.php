<?php

/**
 * Add an item to an array
 *
 * @param	array	$arr1	the array that you're adding to
 * @param	string	$key	the key where something should be added before or after
 * @param	array	$arr2	the array to add to the first array
 * @param	bool	$before	whether or not to add $arr2 before or after $key
 * 
 * @return	array			merged array with item in new the specified location of the original array
 */
function recipress_array_insert( $arr1, $key, $arr2, $before = false) {
	$index = array_search( $key, array_keys( $arr1) );
	
	if( $index === FALSE)
		$index = count( $arr1 );
	else
		if( ! $before )
			$index++;

	$end = array_splice( $arr1, $index );
	return array_merge( $arr1, $arr2, $end );
}

/**
 * remove item from an array by key
 * 
 * @param	array	{anon}	the array to remove from
 * @param	string	{anon}	the item(s) to remove fro the array. add as many strings as you want
 *
 * @return	array			original array with the items removed
 */
function recipress_array_remove() { 
    $args = func_get_args(); 
    $array = $args[0]; 
    $keys = array_slice( $args,1 ); 
      
    foreach( $array as $k => $v ) { 
        if( in_array( $k, $keys ) ) 
            unset( $array[$k] ); 
    } 
    return $array; 
} 

/**
 * Conditional for if the post as a recipe
 *
 * @return bool
 */
function has_recipress_recipe() {
	$hasRecipe = false;
	$meta = get_post_meta( get_the_ID(), 'hasRecipe', true);
	if( $meta == 'Yes' )
		$hasRecipe = true;
	return $hasRecipe;
}

/**
 * post type to add the recipe meta box to
 *
 * @depcrecated 1.9.5 use recipress_options( 'post_type' ) instead
 */
function recipress_post_type() {
	return recipress_options( 'post_type' );
}

/**
 * Where to output the recipe
 *
 * @return	bool	based on the output settings and if the current page is included in those settings or not
 */
function recipress_output() {
	$output = false;
	$outputs = recipress_options( 'output' );
	
	if( is_home() && in_array( 'home', $outputs ) ) $output = true;
	if( is_single() && in_array( 'single', $outputs ) ) $output = true;
	if( is_archive() && in_array( 'archive', $outputs ) ) $output = true;
	if( is_search() && in_array( 'search', $outputs ) ) $output = true;
	
	return $output;
}

/**
 * ReciPress thme class
 *
 * @deprecated 1.9.5 use recipress_options( 'theme' ) instead
 */
function recipress_theme() {
	return recipress_options( 'theme' );
}

/**
 * Generate a summary from the post content
 *
 * @return	string	140 character excerpt from the post content
 */
function recipress_gen_summary() {
   $excerpt = get_the_content();
   $excerpt = strip_tags( trim( $excerpt ) );
   $new_excerpt = '';
   $charlength = 140;
   if( strlen( $excerpt ) > $charlength ) {
       $subex = substr( $excerpt, 0, $charlength - 5 );
       $exwords = explode( ' ', $subex );
       $excut = -( strlen( $exwords[count( $exwords )-1] ) );
       if( $excut < 0 ) {
            $new_excerpt .= substr( $subex, 0, $excut );
       } else {
       	    $new_excerpt .= $subex;
       }
       $new_excerpt .= '&hellip;';
   } else {
	   $new_excerpt .= $excerpt;
   }
   return $new_excerpt;
}

/**
 * Whether or not to add the photo field
 *
 * @return bool
 */
function recipress_add_photo() {
	$add_photo = false;
	if( ! current_theme_supports( 'post-thumbnails' ) || ( current_theme_supports( 'post-thumbnails' ) && recipress_options( 'use_photo' ) == false ) )
		$add_photo = true;
	return $add_photo;
}

/**
 * which taxonomies to use
 *
 * @deprecated 1.9.5 use recipress_options( 'taxonomies' ) instead
 */
function recipress_use_taxonomies() {
	return recipress_options( 'taxonomies' );
}

/**
 * turns minutes into readable format
 *
 * @param	integar	$minutes	amount of minutes to calculate
 * @param	bool	$iso		optional iso format for outputting for screen readers
 */
function recipress_time( $minutes, $iso = false ) {
	if ( $minutes == '' )
		return false;
	$time = '';
	$hours = '';
	if( $minutes > 60 ) {
		$hours = floor( $minutes / 60 );
		$minutes = $minutes - floor( $minutes/60 ) * 60;
	}
	if ( $iso == true ) {
		if ( $hours )
			$time = 'PT' . $hours . 'H' . $minutes . 'M';
		else
			$time = 'PT' . $minutes . 'M';
	} else {
		$h = __( 'hrs', 'recipress' );
		$m = __( 'mins', 'recipress' );
		if( $hours < 2 )
			$h = __( 'hr', 'recipress' );
		if( $minutes < 02 )
			$m = __( 'min', 'recipress' );
		if ( $hours != '' )
			$time = $hours . ' ' . $h . ' ' . $minutes . ' ' . $m;
		else
			$time = $minutes . ' ' . $m;
	}
	return $time;
}

/**
 * Parses an array of ingredients into a readable format
 *
 * @param	array	$ingredients	array of ingredients
 * @return	string					unordered list of ingredients
 */
function recipress_ingredients_list( $ingredients ) {
	$output = '<ul class="ingredients">';
	foreach( $ingredients as $ingredient ) {
		$amount = $ingredient['amount'];
		$measurement = $ingredient['measurement'];
		$the_ingredient = $ingredient['ingredient'];
		$notes = $ingredient['notes'];
		
		if(!$ingredient['ingredient']) continue;
		
		$output .= '<li class="ingredient">';
		if ( isset( $amount ) || isset( $measurement ) ) 
			$output .= '<span class="amount">' . $amount . ' ' . $measurement . '</span> ';
		if ( isset( $the_ingredient ) )
			$term = get_term_by( 'name', $the_ingredient, 'ingredient' );
			$output .= '<span class="name">';
			if ( !empty( $term) && recipress_options( 'link_ingredients' ) == true )
				$output .= '<a href="' . get_term_link( $term->slug, 'ingredient' ) . '">' . $the_ingredient . '</a>';
			else
				$output .= $the_ingredient;
			$output .= '</span> ';
		if ( isset( $notes ) ) 
			$output .= '<i class="notes">' . $notes . '</i></li>';
	}
	$output .= '</ul>';
	
	return $output;
}

/**
 * Parses an array of instructions into a readable format
 *
 * @param	array	$instructions	array of instructions
 * @return	string					unordered list of instructions
 */
function recipress_instructions_list( $instructions ) {
	$output = '<ol class="instructions">';
	foreach( $instructions as $instruction ) {
		$size = recipress_options( 'instruction_image_size' );
		$image = $instruction['image'] != '' ? wp_get_attachment_image( $instruction['image'], $size, false, array( 'class' => 'align-' . $size ) ) : '';
		
		$output .= '<li>';
		if ( $size == 'thumbnail' || $size == 'medium' ) 
			$output .= $image;
		$output .= $instruction['description'];
		if ( $size == 'large' || $size == 'full' ) 
			$output .= '<br />' . $image;
		$output .= '</li>';
	}
	$output .= '</ol>';
	
	return $output;
}

/**
 * Outputs link back to ReciPress.com
 */
function recipress_credit() {
	$credit = recipress_options( 'credit' );
	if( isset( $credit) && $credit == 1)
		return '<p class="recipress_credit"><a href="http://www.recipress.com" target="_target">WordPress Recipe Plugin</a> by ReciPress</p>';
}

/**
 * Returns items from the post data for the recipe output
 *
 * @param	string				$field	the desired post meta field to output
 * @param	string|array|bool	$attr	any extra data that needs to be passed for a particular field
 */
function recipress_recipe( $field, $attr = null ) {
	
	$output = false;
	$meta = get_post_meta( get_the_ID(), $field, true );
	
	switch( $field ) {
		// title
		case 'title':
			$output = get_the_title() . ' ' . __( 'Recipe', 'recipress' );
			if( $meta )
				$output = $meta;
		break;
		
		// photo
		case 'photo':
			if( current_theme_supports( 'post-thumbnails' ) && recipress_options( 'use_photo' ) == true ) 
				$output = get_the_post_thumbnail( get_the_ID(), 'thumbnail', $attr );
			elseif ( $meta )
				$output = wp_get_attachment_image( $meta, 'thumbnail', false, $attr );
		break;
			
		
		// taxonomy terms: cuisine, course, skill_level
		case 'cuisine':
		case 'course':
		case 'skill_level':
			$output = get_the_term_list( get_the_ID(), $field, $attr );
		break;
		
		// prep_time, cook_time
		case 'prep_time':
		case 'cook_time':
			$output = recipress_time( $meta, $attr );
		break;
		
		// ready_time
		case 'ready_time':
			$prep_time = get_post_meta( get_the_ID(), 'prep_time', true );
			$cook_time = get_post_meta( get_the_ID(), 'cook_time', true );
			$other_time = get_post_meta( get_the_ID(), 'other_time', true );
			$ready_time = $prep_time + $cook_time + $other_time;
			$output = recipress_time( $ready_time, $attr );
		break;
		
		// yield
		case 'yield':
			$servings = get_post_meta( get_the_ID(), 'servings', true );
			if( $meta && $servings )
				$output = $meta . ' ( ' . $servings . ' ' . __( 'Servings', 'recipress' ) . ' )';
			elseif ( ! $meta && $servings )
				$output = $servings . ' ' . __( 'Servings', 'recipress' );
			elseif ( $meta && ! $servings )
				$output = $meta;
		break;
		
		// ingredients
		case 'ingredients':
			$output = array();
			foreach( $meta as $ingredient)
				$output = unserialize( $ingredient );
		break;
		
		// instructions
		case 'instructions':
			$output = array();
			foreach( $meta as $instruction)
				$output = unserialize( $instruction );
		break;
		
		// plain output of field
		default:
			$output = $meta;
		break;
		
	} // end switch
	
	return $output;
	
}

/**
 * sanitize boolean inputs
 */
function recipress_santitize_boolean( $string ) {
	if ( ! isset( $string ) || $string != 1 || $string != true )
		return false;
	else
		return true;
}

/**
 * outputs properly sanitized data
 *
 * @param	string	$string		the string to run through a validation function
 * @param	string	$function	the validation function
 *
 * @return						a validated string
 */
function recipress_sanitize( $string, $function = 'sanitize_text_field' ) {
	switch ( $function ) {
		case 'intval':
			return intval( $string );
		case 'absint':
			return absint( $string );
		case 'wp_kses_post':
			return wp_kses_post( $string );
		case 'wp_kses_data':
			return wp_kses_data( $string );
		case 'esc_url_raw':
			return esc_url_raw( $string );
		case 'is_email':
			return is_email( $string );
		case 'sanitize_title':
			return sanitize_title( $string );
		case 'santitize_boolean':
			return santitize_boolean( $string );
		case 'sanitize_text_field':
		default:
			return sanitize_text_field( $string );
	}
}

/**
 * Map a multideminsional array
 *
 * @param	string	$func		the function to map
 * @param	array	$meta		a multidimensional array
 * @param	array	$sanitizer	a matching multidimensional array of sanitizers
 *
 * @return	array				new array, fully mapped with the provided arrays
 */
function recipress_array_map_r( $func, $meta, $sanitizer = 'sanitize_text_field' ) {
		
    $newMeta = array();
	$meta = array_values( $meta );
	
	foreach( $meta as $key => $array ) {
		if ( $array == '' )
			continue;
		/**
		 * some values are stored as array, we only want multidimensional ones
		 */
		if ( ! is_array( $array ) ) {
			return array_map( $func, $meta, (array)$sanitizer );
			break;
		}
		/**
		 * the sanitizer will have all of the fields, but the item may only 
		 * have valeus for a few, remove the ones we don't have from the santizer
		 */
		$keys = array_keys( $array );
		$newSanitizer = $sanitizer;
		if ( is_array( $sanitizer ) ) {
			foreach( $newSanitizer as $sanitizerKey => $value )
				if ( ! in_array( $sanitizerKey, $keys ) )
					unset( $newSanitizer[$sanitizerKey] );
		}
		/**
		 * run the function as deep as the array goes
		 */
		foreach( $array as $arrayKey => $arrayValue )
			if ( is_array( $arrayValue ) )
				$array[$arrayKey] = recipress_array_map_r( $func, $arrayValue, $newSanitizer[$arrayKey] );
				
		$array = array_map( $func, $array, $newSanitizer );
		$newMeta[$key] = array_combine( $keys, array_values( $array ) );
	}
    return $newMeta;
}

/**
 * recives data about a form field and spits out the proper html
 *
 * @param	array					$field		array with various bits of information about the field
 * @param	string|int|bool|array	$meta		the saved data for this field
 * @param	bool					$option		ss this for an option or a meta box?
 * @param	string					$setting	name of the setting to use if $option == true
 *
 * @return	string								html for the field
 */
function recipress_field( $field, $meta = null, $option = false, $setting = null ) {
	if ( ! ( $field || is_array( $field ) ) )
		return;
	
	// get field data
	$type = isset( $field['type'] ) ? $field['type'] : null;
	$label = isset( $field['label'] ) ? $field['label'] : null;
	$desc = isset( $field['desc'] ) ? '<span class="description">' . $field['desc'] . '</span>' : null;
	$place = isset( $field['place'] ) ? $field['place'] : null;
	$size = isset( $field['size'] ) ? $field['size'] : null;
	$options = isset( $field['options'] ) ? $field['options'] : null;
	$sanitizer = isset( $field['sanitizer'] ) ? $field['sanitizer'] : 'sanitize_text_field';
	$id = $name = isset( $field['id'] ) ? $field['id'] : null;
	if ( $option )
		$name = $setting . '[' . $name . ']';
	
	// sanitize $meta for outputting
	if ( $meta && is_array( $meta ) )
		$meta = recipress_array_map_r( 'recipress_sanitize', $meta, $sanitizer );
	elseif ( $meta && ! is_array( $meta ) )
		$meta = recipress_sanitize( $meta, $sanitizer );
	else
		$meta = null;
	
	// perform switch
	switch ( $type ) {
		// text
		case 'text':
		default:
			echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="text-' . esc_attr( $size ) . '" size="30" placeholder="' . esc_attr( $place ) . '" value="' . esc_attr( $meta ) . '" />&nbsp;&nbsp;' . $desc;
		break;
		// text
		case 'number':
		default:
			echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="text-' . esc_attr( $size ) . '" size="30" placeholder="' . esc_attr( $place ) . '" value="' . intval( $meta ) . '" />&nbsp;&nbsp;' . $desc;
		break;
		// textarea
		case 'textarea':
			echo '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" cols="60" rows="4" class="text-' . esc_attr( $size ) . '">' . esc_textarea( $meta ) . '</textarea>&nbsp;&nbsp;' . $desc;
		break;
		// checkbox
		case 'checkbox':
			echo '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" ' . checked( $meta, true, false ) . ' value="1" />
					<label for="' . esc_attr( $id ) . '">' . $desc . '</label>';
		break;
		// checkbox_group
		case 'checkbox_group':
			echo '<ul class="meta_box_items">';
			foreach ( $options as $option )
				echo '<li><input type="checkbox" value="' . $option['value'] . '" name="' . esc_attr( $name ) . '[]" id="' . esc_attr( $id ) . '-' . $option['value'] . '"' , is_array( $meta ) && in_array( $option['value'], $meta ) ? ' checked="checked"' : '' , ' /> 
						<label for="' . esc_attr( $id ) . '-' . $option['value'] . '">' . $option['label'] . '</label></li>';
			echo '</ul>' . $desc;
		break;
		// radio
		case 'radio':
			echo '<ul class="meta_box_items">';
			foreach ( $options as $option )
				echo '<li><input type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '-' . $option['value'] . '" value="' . $option['value'] . '" ' . checked( $meta, $option['value'], false ) . ' />
						<label for="' . esc_attr( $id ) . '-' . $option['value'] . '">' . $option['label'] . '</label></li>';
			echo '</ul>' . $desc;
		break;
		// image radio
		case 'image_radio':
			echo '<fieldset class="image_radio">';
			foreach ( $options as $option ) {
				$active = $meta == $option['value'] ? ' class="active"' : '';
				echo '<label' . $active . '>
						<input type="radio" name="' . esc_attr( $name ) . '" value="' . $option['value'] . '"' . checked( $meta, $option['value'], false ) . ' />
						<img src="' . esc_url( $option['image'] ) . '" alt="' . esc_attr( $option['label'] ) . '" />
					 <strong>' . esc_html( $option['label'] ) . '</strong>
					 <span class="description">' . esc_html( $option['desc'] ) . '</span></label>';
				}
				echo '</fieldset>';
		break;
		// image
		case 'image':
			$image = RECIPRESS_URL . 'img/image.png';	
			if( $meta )  {
				$image = wp_get_attachment_image_src( $meta, 'medium' );
				$image = $image[0];
			}				
			echo '<div class="recipress_image">
					<input name="' . esc_attr( $name ) . '" type="hidden" class="recipress_upload_image" value="' . $meta . '" />',
					'<img src="' . esc_attr( $image ) . '" class="recipress_preview_image" alt="" />
						<input class="recipress_upload_image_button button" rel="' . get_the_ID() . '" type="button" value="' . __( 'Add Image', 'recipress' ) . '" /><br />
						<small>&nbsp;<a href="#" class="recipress_clear_image_button">' . __( 'Remove Image', 'recipress' ) . '</a></small></div>
						<br clear="all" />' . $desc;
		break;
		// tax_select
		case 'tax_select':
			echo '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '">',
					'<option value="">' . __( 'Select One', 'recipress' ) . '</option>'; // Select One
			$terms = get_terms( $id, 'get=all' );
			$post_terms = wp_get_object_terms( get_the_ID(), $id );
			$taxonomy = get_taxonomy( $id );
			$selected = $post_terms ? $taxonomy->hierarchical ? $post_terms[0]->term_id : $post_terms[0]->slug : null;
			foreach ( $terms as $term ) {
				$term_value = $taxonomy->hierarchical ? $term->term_id : $term->slug;
				echo '<option value="' . $term_value . '"' . selected( $selected, $term_value, false ) . '">' . $term->name . '</option>';
			}
			echo '</select>&nbsp;&nbsp;<span class="description"><a href="' . admin_url( 'edit-tags.php?taxonomy=' . $id ) . '">' . __( 'Manage', 'recipress' ) . ' ' . $taxonomy->label . '</a></span>';
		break;
		// paypal donate
		case 'paypal':
			echo '<p>' . __( 'Do you find this plugin useful and want to say thanks?', 'recipress' ) . '</p>
					<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=tammyhartdesigns%40gmail%2ecom&item_name=Recipe%20Box%20Plugin%20Latte%20Fund&no_shipping=0&no_note=1&tax=0&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8" target="_blank" class="button">' . __( 'Donate', 'recipress' ) . '</a>';
		break;
		// ingredient ( this will never be in the options table, so it ignores a few things )
		case 'ingredient':
			echo '<ul class="table" id="ingredients_table">
					<li class="thead"><ul class="tr">
						<li class="th left_corner"><span class="sort_label"></span></li>
						<li class="th cell-amount">' . __( 'Amount', 'recipress' ) . '</li>
						<li class="th cell-measurement">' . __( 'Measurement', 'recipress' ) . '</li>
						<li class="th cell-ingredient">' . __( 'Ingredient', 'recipress' ) . '</li>
						<li class="th cell-notes">' . __( 'Notes', 'recipress' ) . '</li>
						<li class="th right_corner"><a class="ingredient_add" href="#"></a></li>
					</ul></li>
					<li class="tbody">';
			$i = 0;
			if( in_array( $meta, array( '', array() ) ) ) {
				$keys = array( 'amount', 'measurement', 'ingredient', 'notes' );
				$meta = array ( array_fill_keys( $keys, null ) );
			}
			foreach( $meta as $row ) {
				echo '<ul class="tr">
					<li class="td"><span class="sort"></span></li>
					<li class="td cell-amount"><input type="text" placeholder="0" name="' . esc_attr( $name ) . '[' . $i . '][amount]" id="ingredient_amount_' . $i . '" value="' . $row['amount'] . '" size="3" /></li>
					<li class="td cell-measurement"><input type="text" name="' . esc_attr( $name ) . '[' . $i . '][measurement]" id="ingredient_measurement_' . $i . '" value="' . $row['measurement'] . '" size="30" /></li>';
				// ingredients chosen select
				echo '<li class="td cell-ingredient"><select data-placeholder="Select One" name="' . esc_attr( $name ) . '[' . $i . '][ingredient]" id="ingredient_' . $i . '" class="chosen ingredient">
						<option value="">Select One</option>';
				$ingredients = get_terms( 'ingredient', 'get=all' );
				foreach ( $ingredients as $ingredient )
						echo '<option value="' . $ingredient->slug . '"' . selected( $row['ingredient'], $ingredient->slug, false ) . '>' . $ingredient->name . '</option>'; 
				echo '</select></li>';
				// continue tr items
				echo '<li class="td cell-notes"><input type="text" name="' . esc_attr( $name ) . '[' . $i . '][notes]" id="ingredient_notes_' . $i . '" value="' . $row['notes'] . '" size="30" placeholder="' . __( 'e.g., chopped, fresh, etc.', 'recipress' ) . '" /></li>
					<li class="td"><a class="ingredient_remove" href="#"></a></li>
					<li class="recipress-clear"></li>
				</ul>';
				$i++;
			}
			echo '</li></ul>
				' . $desc;
		break;
		// instruction ( this will never be in the options table, so it ignores a few things )
		case 'instruction':
			echo '<ul class="table" id="instructions_table">
					<li class="thead"><ul class="tr">
						<li class="th left_corner"><span class="sort_label"></span></li>
						<li class="th cell-description">' . __( 'Description', 'recipress' ) . '</li>
						<li class="th image">' . __( 'Image', 'recipress' ) . '</li>
						<li class="th right_corner"><a class="instruction_add" href="#"></a></li>
					</ul></li>
					<li class="tbody">';
			$i = 0;
			if( in_array( $meta, array( '', array() ) ) ) {
				$keys = array( 'image', 'description' );
				$meta = array ( array_fill_keys( $keys, null ) );
			}
			foreach( $meta as $row) {
				$image = RECIPRESS_URL . 'img/image.png';
				if( !empty( $row['image'] ) ) {
					$image = wp_get_attachment_image_src( $row['image'], 'medium' );
					$image = $image[0];
				}	
				echo '<ul class="tr" id="insutrction_row-' . $i . '">
					<li class="td"><span class="sort"></span></li>
					<li class="td cell-description"><textarea placeholder="' . __( 'Describe this step in the recipe', 'recipress' ) . '" class="instruction" name="' . esc_attr( $name ) . '[' . $i . '][description]" cols="40" rows="4" id="ingredient_description_' . $i . '">' . esc_html( $row['description'] ) . '</textarea></li>
					<li class="td image"><div class="recipress_image">
						<input name="' . esc_attr( $name ) . '[' . $i . '][image]" type="hidden" class="recipress_upload_image instruction" value="' . $row['image'] . '" />
								<img src="' . esc_url( $image ) . '" class="recipress_preview_image" alt="" />
								<input class="recipress_upload_image_button button" rel="' . intval( get_the_ID() ) . '" type="button" value="' . __( 'Add Image', 'recipress' ) . '" />
								<small>&nbsp;<a href="#" class="recipress_clear_image_button">' . __( 'Remove Image', 'recipress' ) . '</a></small></div>
					</li>
					<li class="td"><a class="instruction_remove" href="#"></a></li>
					<li class="recipress-clear"></li>
				</ul>';
				$i++;
			}
			echo '</li></ul>
				<div class="recipress-clear"></div>' . $desc;
		break;
	}
}