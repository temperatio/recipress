<?php


/* Add Meta Box
------------------------------------------------------------------------- */
add_action( 'admin_menu', 'recipe_add_box' );
function recipe_add_box() {
    global $meta_fields;
    add_meta_box( 'recipress', __( 'Recipe', 'recipress' ), 'recipe_show_box', recipress_options( 'post_type' ), 'normal', 'high' );
}


/* Custom Fields
------------------------------------------------------------------------- */
function recipress_fields() {
	$meta_fields = array(
		'title' => array(
			'label'	=> __( 'Recipe Title', 'recipress' ),
			'desc'	=> __( 'Do you want to give the recipe a different title from the post?', 'recipress' ),
			'place'	=> '',
			'size'	=> 'large',
			'id'	=> 'title',
			'type'	=> 'text'
		),
		'summary' => array(
			'label'	=> __( 'Recipe Summary', 'recipress' ),
			'desc'	=> __( 'A small summary of the recipe', 'recipress' ),
			'place'	=> '',
			'size'	=> 'small',
			'id'	=> 'summary',
			'type'	=> 'textarea',
			'sanitizer' => 'wp_kses_post'
		),
		'yield' => array(
			'label'	=> __( 'Yield', 'recipress' ),
			'desc'	=> __( 'How much/many does this recipe produce?', 'recipress' ),
			'place'	=> __( 'e.g., 1 loaf, 2 cups', 'recipress' ),
			'size'	=> 'medium',
			'id'	=> 'yield',
			'type'	=> 'text'
		),
		'servings' => array(
			'label'	=> __( 'Servings', 'recipress' ),
			'desc'	=> __( 'How many servings?', 'recipress' ),
			'place'	=> '00',
			'size'	=> 'small',
			'id'	=> 'servings',
			'type'	=> 'text',
			'sanitizer' => 'intval'
		),
		'prep_time' => array(
			'label'	=> __( 'Prep Time', 'recipress' ),
			'desc'	=> __( 'How many minutes? (60+ minutes will output as hours)', 'recipress' ),
			'place'	=> '00',
			'size'	=> 'small',
			'id'	=> 'prep_time',
			'type'	=> 'text',
			'sanitizer' => 'intval'
		),
		'cook_time' => array(
			'label'	=> __( 'Cook Time', 'recipress' ),
			'desc'	=> __( 'How many minutes? (60+ minutes will output as hours)', 'recipress' ),
			'place'	=> '00',
			'size'	=> 'small',
			'id'	=> 'cook_time',
			'type'	=> 'text',
			'sanitizer' => 'intval'
		),
		'other_time' => array(
			'label'	=> __( 'Other Time', 'recipress' ),
			'desc'	=> __( 'For calculating a proper ready time. How many minutes? (60+ minutes will output as hours)', 'recipress' ),
			'place'	=> '00',
			'size'	=> 'small',
			'id'	=> 'other_time',
			'type'	=> 'text',
			'sanitizer' => 'intval'
		),
		'ingredient' => array(
			'label'	=> __( 'Ingredients', 'recipress' ),
			'desc'	=> sprintf( __( 'Click the plus icon to add another ingredient. %1$sManage Ingedients%2$s', 'recipress' ), '<a href="'. admin_url( 'edit-tags.php?taxonomy=ingredient' ) . '">', '</a>' ),
			'id'	=> 'ingredient',
			'type'	=> 'ingredient',
			'sanitizer' => array( 
				'amount' => 'intval',
				'measurement' => 'sanitize_text_field',
				'ingredient' => 'sanitize_text_field',
				'notes' => 'sanitize_text_field'
			)
		),
		'instruction' => array(
			'label'	=> __( 'Instructions', 'recipress' ),
			'desc'	=> __( 'Click the plus icon to add another instruction.', 'recipress' ),
			'id'	=> 'instruction',
			'type'	=> 'instruction',
			'sanitizer' => array(
				'description' => 'wp_kses_post',
				'image' => 'intval'
			)
		)
	);
	
	return apply_filters( 'recipress_fields', $meta_fields );
}

// if post-thumbnails aren't supported, add a recipe photo
add_filter( 'recipress_fields', 'recipress_insert_photo' );
function recipress_insert_photo( $meta_fields ) {
	$photo = array(
		'photo' => array(
			'label'	=> __( 'Photo', 'recipress' ),
			'desc'	=> __( 'Add a photo of your completed recipe', 'recipress' ),
			'id'	=> 'photo',
			'type'	=> 'image'
		)
	);
	
	if(recipress_add_photo() ) 
    	return recipress_array_insert( $meta_fields, 'title', $photo, true );
	else
		return $meta_fields;
}

// add taxonomies
add_filter( 'recipress_fields', 'recipress_insert_taxonomies' );
function recipress_insert_taxonomies( $meta_fields ) {
	$taxonomies = recipress_options( 'taxonomies' );
	if( !empty( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy ) {
			
			$get_taxonomy = get_taxonomy( $taxonomy );
			
			$add_taxonomies[$taxonomy] = array(
					'label'	=> $get_taxonomy->label,
					'id'	=> $taxonomy,
					'type'	=> 'tax_select'
			);
		}
	}
	
	if( $add_taxonomies ) 
    	return recipress_array_insert( $meta_fields, 'summary', $add_taxonomies );
	else
		return $meta_fields;
}

// add cost field
add_filter( 'recipress_fields', 'recipress_insert_cost' );
function recipress_insert_cost( $meta_fields ) {	
	$cost = array(
		'cost' => array(
			'label'	=> __( 'Cost', 'recipress' ),
			'desc'	=> __( 'What does it cost to make this recipe?', 'recipress' ),
			'place'	=> __( '$0.00', 'recipress' ),
			'size'	=> 'medium',
			'id'	=> 'cost',
			'type'	=> 'text'
		)
	);
	
	if( recipress_options( 'cost_field' ) == true )
    	return recipress_array_insert( $meta_fields, 'yield', $cost, true );
	else
		return $meta_fields;
}


/**
 * The meta box callback
 *
 * @uses	recipress_field()	takes in field data and saved meta value and outputs html for that field
 */
function recipe_show_box() {
    global $post;
	$meta_fields = recipress_fields();
	
	wp_nonce_field( 'recipress_nonce_action', 'recipress_nonce_field' );
	
	// used in recipress.admin.js to show/hide the recipress meta fields
	$hasRecipe = get_post_meta( $post->ID, 'hasRecipe', true);
	echo '<p id="hasRecipe_box"><input type="checkbox"' . checked( $hasRecipe, 'Yes', false ) . ' id="hasRecipe" name="hasRecipe" value="Yes" /><label for="hasRecipe">' . __( 'Add a recipe?', 'recipress' ) . '</label></p>';
	
	// recipress meta fields
    echo '<div id="recipress_table"><table class="form-table">';
    foreach ( $meta_fields as $field) {
		if ( $field['type'] == 'section' ) {
			echo '<tr>
					<td colspan="2">
						<h2>' . $field['label'] . '</h2>
					</td>
				</tr>';
		}
		else {
			echo '<tr>
					<th style="width:20%"><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
					<td>';
					
					$meta = get_post_meta( $post->ID, $field['id'], true);
					echo recipress_field( $field, $meta );
					
			echo     '<td>
				</tr>';
		}
    }
    echo '</table></div>';
}


/* Save the Data
------------------------------------------------------------------------- */
add_action( 'save_post', 'recipe_save_data' );
// Save data from meta box
function recipe_save_data( $post_id) {
	global $post_type;
	
    $meta_fields = recipress_fields();
	
	// verify nonce
	if ( !$post_type == recipress_options( 'post_type' ) || !isset( $_POST['recipress_nonce_field'] ) || !wp_verify_nonce( $_POST['recipress_nonce_field'], 'recipress_nonce_action' ) )
		return $post_id;
	// check autosave
	if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
	// check permissions
	if ( !current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	
	// set the value of hasRecipe
	$hasRecipe_old = get_post_meta( $post_id, 'hasRecipe', true);
	$hasRecipe_new = $_POST['hasRecipe'];
		if ( $hasRecipe_new && $hasRecipe_new != $hasRecipe_old) {
			update_post_meta( $post_id, 'hasRecipe', $hasRecipe_new);
		} elseif ( '' == $hasRecipe_new && $hasRecipe_old) {
			delete_post_meta( $post_id, 'hasRecipe', $hasRecipe_old);
		}
	// determine if a recipe was added
	if ( $hasRecipe_new == 'Yes' ) {
		foreach ( $meta_fields as $field) {
			if( $field['type'] == 'tax_select' ) continue;
			$old = get_post_meta( $post_id, $field['id'], true);
			$new = $_POST[$field['id']];
			if ( $new && $new != $old) {
				if ( 'ingredient' == $field['id']) 
					foreach ( $new as &$ingredient) $ingredient['measurement'] = $ingredient['measurement'];
				update_post_meta( $post_id, $field['id'], $new);
			} elseif ( '' == $new && $old) {
				delete_post_meta( $post_id, $field['id'], $old);
			}
		}
		
		// save taxonomies
		$post = get_post( $post_id);
		if ( ( $post->post_type == recipress_options( 'post_type' ) ) ) { 
			$the_ingredients = $_POST['ingredient'];
			foreach( $the_ingredients as $the_ingredient )
					$ingredients[] = sanitize_text_field( $the_ingredient['ingredient'] );
			wp_set_object_terms( $post_id, $ingredients, 'ingredient' );
			$cuisine = $_POST['cuisine'];
			$course = $_POST['course'];
			$skill_level = $_POST['skill_level'];
			wp_set_object_terms( $post_id, $cuisine, 'cuisine' );
			wp_set_object_terms( $post_id, $course, 'course' );
			wp_set_object_terms( $post_id, $skill_level, 'skill_level' );
			}
	}
}



/**
 * simplify media upload
 */
add_action( 'admin_head-media-upload-popup', 'tcnmy_thickbox_head' );
function tcnmy_thickbox_head() {
	// generally hide the post thumbnail selection link
	if ( ! isset( $_GET['recipress'] ) ) :
		?>
		<style type="text/css">
			tr.submit .wp-post-thumbnail {
				display: none;
			}
		</style>
		<?php
	// hide those links and a bunch of other things
	else :
		?>
		<style type="text/css">
			#media-upload-header #sidemenu li#tab-type_url,
			#gallery-settings,
			#gallery-form table.widefat thead,
			#gallery-form .menu_order,
			#sort-buttons,
			.ml-submit,
			tr.url,
			tr.align,
			tr.image_alt,
			tr.image-size,
			tr.post_title,
			tr.post_excerpt,
			tr.post_content,
			tr.image_alt p,
			table thead input.button,
			table thead img.imgedit-wait-spin {
				display: none !important;
			}
			#media-upload a.wp-post-thumbnail {
				margin-left:0;
			}
			.media-item td.savesend {
				padding:10px;
			}
		</style>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('#media-items').bind('DOMNodeInserted',function(){
				$('input[value=\"Insert into Post\"]').val( 'Use This Image' );
				$('a.wp-post-thumbnail').hide();
			}).trigger('DOMNodeInserted');
		});
		</script>
		<?php
	endif;
}