<?php
/*
Plugin Name: ReciPress
Plugin URI: http://www.recipress.com
Description: Create recipes in your posts with a clean interface and layout that are easy to organize.
Version: 1.9.6
Author: Tammy Hart
Author URI: http://tammyhartdesigns.com
*/

/* 
Copyright (c) 2012, Tammy Hart 
 
This program is free software; you can redistribute it and/or 
modify it under the terms of the GNU General Public License 
as published by the Free Software Foundation; either version 2 
of the License, or (at your option) any later version. 
 
This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 
 
You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. 
*/  

// Localization
add_action( 'init', 'recipress_textdomain' );
function recipress_textdomain() {
	load_plugin_textdomain( 'recipress', false, basename( dirname( __FILE__ ) ) . '/lang' );
}

// Constants
define( 'RECIPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'RECIPRESS_DIR', plugin_dir_path( __FILE__ ) );

function get_recipress_url() { return RECIPRESS_URL; }

// Includes
include( RECIPRESS_DIR . 'php/functions.php' );
include( RECIPRESS_DIR . 'php/options.php' );
include( RECIPRESS_DIR . 'php/meta_box.php' );
include( RECIPRESS_DIR . 'php/taxonomies.php' );
include( RECIPRESS_DIR . 'php/output.php' );
include( RECIPRESS_DIR . 'php/widgets.php' );

// Styles and Scripts
add_action( 'admin_enqueue_scripts', 'recipress_admin_enqueue' );
function recipress_admin_enqueue( $hook ) {
	global $recipress_settings_page, $wp_styles;
	
	// icon css is always needed
	wp_enqueue_style( 'recipress_icon', RECIPRESS_URL . 'css/icon.css' );
	
	// we only need the rest of this on the post type(s) and the settings pages
	$post_type = get_post_type();	
	if ( $hook != $recipress_settings_page && isset( $post_type ) && $post_type != recipress_options( 'post_type' )  )
		return;
	
	// js
	wp_register_script( 'chosen', RECIPRESS_URL . 'js/chosen.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'recipress_back', RECIPRESS_URL . 'js/recipress.admin.js', array( 'jquery', 'jquery-ui-sortable', 'chosen' ), '', true );
	wp_localize_script( 'recipress_back', 'pluginDir', htmlspecialchars( RECIPRESS_URL ) );
	// css
	wp_enqueue_style( 'recipress_back', RECIPRESS_URL . 'css/recipress.admin.css' );
	wp_enqueue_style( 'chosen', RECIPRESS_URL . 'css/chosen.css' );
	wp_enqueue_style( 'recipress_back_ie', RECIPRESS_URL . 'css/ie.css' );
	$wp_styles->add_data( 'recipress_back_ie', 'conditional', 'lt IE 9' );
}

add_action( 'wp_enqueue_scripts', 'recipress_wp_enqueue' );
function recipress_wp_enqueue() {
	wp_enqueue_style( 'recipress_front', RECIPRESS_URL . 'css/recipress.wp.css' );
}

// Register taxonomies and insert terms on plugin activation
add_action( 'init', 'register_recipress_taxonomies' );
register_activation_hook( __FILE__, 'activate_recipress_taxonomies' );

function activate_recipress_taxonomies() {
	// activate taxonomies
	register_recipress_taxonomies();
	// insert terms
	recipress_default_taxonomies();
	$GLOBALS['wp_rewrite']->flush_rules();
}

/**
 * Load up the menu pages
 */
add_action( 'admin_menu', 'recipress_add_page' );
function recipress_add_page() {
	global $recipress_settings_page;
	$recipress_settings_page = add_menu_page( __( 'ReciPress Options', 'recipress' ), __( 'ReciPress', 'recipress' ), 'manage_options', 'recipress_options', 'recipress_do_page' );
	add_submenu_page( 'recipress_options', __( 'ReciPress Options', 'recipress' ), __( 'ReciPress Options', 'recipress' ), 'manage_options', 'recipress_options', 'recipress_do_page' );
	add_submenu_page( 'recipress_options', __( 'Ingredients', 'recipress' ), __( 'Ingredients', 'recipress' ), 'edit_others_posts', 'edit-tags.php?taxonomy=ingredient' );
	$taxonomies = recipress_options( 'taxonomies' );
	if( in_array( 'cuisine', (array) $taxonomies ) )
		add_submenu_page( 'recipress_options', __( 'Cuisines', 'recipress' ), __( 'Cuisines', 'recipress' ), 'edit_others_posts', 'edit-tags.php?taxonomy=cuisine' );
	if( in_array( 'course', (array) $taxonomies ) )
		add_submenu_page( 'recipress_options', __( 'Courses', 'recipress' ), __( 'Courses', 'recipress' ), 'edit_others_posts', 'edit-tags.php?taxonomy=course' );
	if( in_array( 'skill_level', (array) $taxonomies ) )
		add_submenu_page( 'recipress_options', __( 'Skill Levels', 'recipress' ), __( 'Skill Levels', 'recipress' ), 'edit_others_posts', 'edit-tags.php?taxonomy=skill_level' );
}

// highlight the proper top level menu
add_action( 'parent_file', 'recipe_tax_menu_correction' );
function recipe_tax_menu_correction( $parent_file ) {
	global $current_screen;
	$taxonomy = $current_screen->taxonomy;
	if ( in_array( $taxonomy , array( 'ingredient', 'cuisine', 'course', 'skill_level' ) ) )
		$parent_file = 'recipress_options';
	return $parent_file;
}

// add the taxonomy to the body class
add_filter( 'admin_body_class', 'taxonomy_admin_body_class' );
function taxonomy_admin_body_class( $classes ) {
	global $current_screen;
	$taxonomy = $current_screen->taxonomy;
	if ( is_admin() && isset( $taxonomy ) ) {
		$classes .= 'taxonomy-' . $taxonomy;
	}
	return $classes;
}