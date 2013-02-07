<?php

/**
 * Recipe Output
 *
 * Creates an array with named keys for us with recipress_array_insert() and recipress_array_remove().
 *
 * @uses	recipress_recipe()
 * @return	string	completely formatted recipe
 */
function get_the_recipe() {
	// determine if post has a recipe
	if( has_recipress_recipe() && recipress_output() ) {
		
		// create the array
		$recipe['before'] = '<div class="hrecipe ' . recipress_options( 'theme' ) . '" id="recipress_recipe">';
		$recipe['title'] = '<h2 class="fn">' . recipress_recipe( 'title' ) . '</h2>';
		$recipe['photo'] = recipress_recipe( 'photo', array( 'class' => 'alignright photo recipress_thumb' ) );
		$recipe['meta'] = '<p class="seo_only">' . _x( 'By', 'By Recipe Author', 'recipress' ) . ' <span class="author">' . get_the_author() . '</span>
							' . __( 'Published:', 'recipress' ) . ' <span class="published updated">' . get_the_date( 'F j, Y' ) . 
							'<span class="value-title" title="' . get_the_date( 'c' ) . '"></span></span></p>';
							
		// details
		$recipe['details_before'] = '<ul class="recipe-details">';
		if( recipress_recipe( 'yield' ) )
			$recipe['yield'] = '<li><b>' . __( 'Yield:', 'recipress' ) . '</b> <span class="yield">' . recipress_recipe( 'yield' ) . '</span></li>';
		if( recipress_recipe( 'cost' ) && recipress_options( 'cost_field' ) == true )
			$recipe['cost'] = '<li><b>' . __( 'Cost:', 'recipress' ) . '</b> <span class="cost">' . recipress_recipe( 'cost' ) . '</span></li>';
		if( recipress_recipe( 'prep_time' ) && recipress_recipe( 'cook_time' ) )
			$recipe['clear_items'] = '<li class="clear_items"></li>';
		if( recipress_recipe( 'prep_time' ) )
			$recipe['prep_time'] = '<li><b>' . __( 'Prep:', 'recipress' ) . '</b> <span class="preptime"><span class="value-title" title="' . recipress_recipe( 'prep_time', true ) . '"></span>' . recipress_recipe( 'prep_time' ) . '</span></li>';
		if( recipress_recipe( 'cook_time' ) )
			$recipe['cook_time'] = '<li><b>' . __( 'Cook:', 'recipress' ) . '</b> <span class="cooktime"><span class="value-title" title="' . recipress_recipe( 'cook_time', true ) . '"></span>' . recipress_recipe( 'cook_time' ) . '</span></li>';
		// if at least two of these three items exist: a && ( b || c ) || ( b && c )
		if( recipress_recipe( 'prep_time' ) && ( recipress_recipe( 'cook_time' ) || recipress_recipe( 'other_time' ) ) || ( recipress_recipe( 'cook_time' ) || recipress_recipe( 'other_time' ) ) )
			$recipe['ready_time'] ='<li><b>' . __( 'Ready In:', 'recipress' ) . '</b> <span class="duration"><span class="value-title" title="' . recipress_recipe( 'ready_time',true ) . '"></span>' . recipress_recipe( 'ready_time' ) . '</span></li>';
		$recipe['details_after'] = '</ul>';
		
		// summary
		$summary = recipress_recipe( 'summary' );
		if( ! $summary )
			$recipe['summary'] = '<p class="summary seo_only">' . recipress_gen_summary() . '</p>';
		else
			$recipe['summary'] = '<p class="summary">' . $summary . '</p>';
		
		// indredients
		$recipe['ingredients_title'] = '<h3>' . __( 'Ingredients', 'recipress' ) . '</h3>';
		$recipe['ingredients'] = recipress_ingredients_list( recipress_recipe( 'ingredient' ) );
		
		// instructions
		$recipe['instructions_title'] = '<h3>' . __( 'Instructions', 'recipress' ) . '</h3>';
		$recipe['instructions'] = recipress_instructions_list( recipress_recipe( 'instruction' ) );
					
		// taxonomies
		$recipe['taxonomies_before'] = '<ul class="recipe-taxes">';
		$recipe['cuisine'] = recipress_recipe( 'cuisine', '<li><b>' . __( 'Cuisine', 'recipress' ) . ':</b> ', ', ', '</li>' );
		$recipe['course'] = recipress_recipe( 'course', '<li><b>' . __( 'Course:', 'recipress' ) . '</b> ', ', ', '</li>' );
		$recipe['skill_level'] = recipress_recipe( 'skill_level', '<li><b>' . __( 'Skill Level', 'recipress' ) . ':</b> ', ', ', '</li>' );
		$recipe['taxonomies_after'] = '</ul>';
		
		// close
		$recipe['credit'] = recipress_credit();
		$recipe['after'] = '</div>';
	
	// filter and return the recipe
	$recipe = apply_filters( 'the_recipe',$recipe);
	return implode( '', $recipe );
	}
}

// the_recipe
function the_recipe( $content) {
	return $content . get_the_recipe();
}

// shortcode
add_shortcode( 'recipe', 'recipress_shortcode' );
function recipress_shortcode( $content ) {
	$autoadd = recipress_options( 'autoadd' );
	if ( isset( $autoadd ) && $autoadd )
		$content  .= get_the_recipe();
	return $content;
}

// auto add?
add_action( 'template_redirect', 'recipress_autoadd' );
function recipress_autoadd() {
	if ( recipress_options( 'autoadd' ) ) {
		add_action( 'the_content', 'the_recipe', 10 );
	}
}