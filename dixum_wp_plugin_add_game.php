<?php
/**
 * Plugin Name: Add game
 * Author: Dixum
 * Author URI: www.dixum.se
 *Description: You need to make 4 custom fields, Pic id, ios, android, and description. Short code: [add_game id="index"] index = id of the post 
 *
 */

 

function shortcode_add_game($atts){

	$id = $atts['id'];
	
	$post_arr = get_post($post_id,ARRAY_A);
	$post_metdata = get_metadata('post',$id);
	
	$game_name = $post_arr['post_title'];
	$description = nl2br($post_metdata['description'][0]);
	$link_android = $post_metdata['android'][0];
	$link_ios = $post_metdata['ios'][0];
	$pic_id= $post_metdata['Pic id'][0];
	
	$attachment_url = wp_get_attachment_url($pic_id);
	$dir = substr($attachment_url,strpos($attachment_url,'/wp-content'),strlen($attachment_url));
	
	ob_start();
	$output_string="
		<div class = 'thumbnail'>
			<img class = 'portfolio_img' src=' ".site_url().$dir ."' alt = 'Placeholder for game image'/>
			<div class = 'caption'>
				<h3>".$game_name ."</h3>
				<p>
					".$description ."
				</p>
				<div class = 'portfolio_footer'>
					<div class = 'row'>
						<div class = 'col-sm-6'>
							<a href='".$link_ios."'><img src='".get_template_directory_uri() ."/"."img/game/stores/astore.png'/ alt = 'Appstore link'></a>
						</div>
						<div class = 'col-sm-6'>
							<a href='".$link_android."'><img src='".get_template_directory_uri() ."/"."img/game/stores/gplay.png'/ alt = 'Google Play link'></a>
						</div>
					</div>
				</div>
				<a href='#' class = 'btn btn-primary site_link'>Classified Site</a>
			</div>
		</div>
	";
	
	ob_get_clean();
	return ($output_string);
	
	
}
function game_init() {
	$labels = array(
		'name'               => 	'Games',
		'singular_name'      => 	'Game',
		'menu_name'          => 	'Games',
		'name_admin_bar'     => 	'Game',
		'add_new'            => 	'Add New',
		'add_new_item'       => 	'Add New Game',
		'new_item'           => 	'New Game',
		'edit_item'          => 	'Edit Game',
		'view_item'          => 	'View Game',
		'all_items'          => 	'All Games', 
		'search_items'       => 	'Search Games', 
		'parent_item_colon'  => 	'Parent Games:', 
		'not_found'          => 	'No games found.', 
		'not_found_in_trash' =>		'No games found in Trash.', 
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'game' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title','custom-fields')
	);

	
	register_post_type( 'game', $args );	
}
add_action( 'init', 'game_init' );
add_shortcode('add_game', 'shortcode_add_game' );
?>