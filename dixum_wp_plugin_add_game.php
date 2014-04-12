<?php

/**
 * Plugin Name: Add game
 * Author: Dixum
 * Author URI: www.dixum.se
 * Description:[add_game id="index"] index = id of the post 
 *
 */
function game_init() {
	$labels = array(
		'name'               => 	'Games',
		'singular_name'      => 	'Game',
		'menu_name'          => 	'Games',
		'name_admin_bar'     => 	'Game',
		'add_new'            => 	'',
		'add_new_item'       => 	'',
		'new_item'           => 	'',
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

function wp_add_game_tab(){
	add_submenu_page('edit.php?post_type=game','wp_add_game','Add Game','manage_options','addgame','wp_add_game_page');
	remove_submenu_page('edit.php?post_type=game', 'post-new.php?post_type=game');
}

function wp_add_game_page(){
	
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	$status = '';
	
	if(isset($_POST['post_game'])&&!empty($_POST['post_game'])){
	
		$game_name = htmlentities(trim($_POST['game_name']));
		$game_description = htmlentities(trim($_POST['game_description']));
		$game_link_android = $_POST['game_link_android'];
		$game_link_ios = $_POST['game_link_ios'];
		$game_website = $_POST['game_website'];
		
		if(strrpos($game_link_android, 'http://') === false){
			$game_link_android = 'http://'.$game_link_android;
		}
		if(strrpos($game_link_ios, 'http://') === false){
			$game_link_ios = 'http://'.$game_link_ios;
		}
		if(strrpos($game_website, 'http://') === false){
			$game_website = 'http://'.$game_website;
		}
		
		if(isset($game_name)&&!empty($game_name)&&isset($game_description)&&!empty($game_description)&&isset($game_link_android)&&!empty($game_link_android)&&isset($game_link_ios)&&!empty($game_link_ios)&&isset($game_website)&&!empty($game_website)){
			if(strlen($game_name) <= 70 && strlen($game_description) <= 700 && strlen($game_link_android) <= 120 && strlen($game_link_ios) <= 120 && strlen($game_website) <= 120){
				$file_types_array=array("image/jpeg","image/png");
				if(in_array($_FILES['game_picture']['type'],$file_types_array)){
					
					$attachment_id = media_handle_upload( 'game_picture', $_POST['post_id'] );
					
					$new_post = array(
								'post_title'	=> $game_name,
								'post_name'		=> $game_name,
								'post_status'   => 'publish',
								'post_type'		=> 'game'
								);	
					
					$post = wp_insert_post($new_post); 
					
					add_post_meta($post,'Iso',$game_link_ios,true);
					add_post_meta($post,'Android',$game_link_android,true);
					add_post_meta($post,'Game description',$game_description,true);
					add_post_meta($post,'Pic id',$attachment_id ,true);
					add_post_meta($post,'Game website',$game_website,true);
					$status ="Send";
				}else{
					$status = 'Invalid file type';
				}
			}else{
				$status = 'Error in length';
			}
		}else{
			$status = 'You must fill in everything!';
		}
	}

	ob_start();?>
	<div class="wrap">
		<form action="admin.php?page=addgame" method="POST" enctype="multipart/form-data">
			<h1>Add game</h1>
			<h2><?php echo ob_get_clean(); echo $status; ob_start();?></h2>
			<input required type="text" placeholder="Game name" name="game_name" maxlength="50"/><br/>
			<input required type="file" name="game_picture" id="game_picture"/><br/>
			<input type="hidden" name="post_id" id="post_id" value="55" />
			<input required type="text" placeholder="Android link" name="game_link_android" maxlength="100"/><br/>
			<input required type="text" placeholder="IOS link" name="game_link_ios" maxlength="100"/><br/>
			<input required type="text" placeholder="Game website" name="game_website"maxlength="100"/><br/>
			<textarea required name="game_description" placeholder="Description" maxlength="400"></textarea><br/>
			<input type="submit" name="post_game" value="Post game"/> 
		</form>
	</div>
	
	
	<?php
	
	echo ob_get_clean();
}

function shortcode_add_game($atts){

	$id = $atts['id'];
	
	$post_arr = get_post($post_id,ARRAY_A);
	$post_metdata = get_metadata('post',$id);
	
	$game_name = $post_arr['post_title'];
	$description = nl2br($post_metdata['Game description'][0]);
	$link_android = $post_metdata['Android'][0];
	$link_ios = $post_metdata['Iso'][0];
	$pic_id= $post_metdata['Pic id'][0];
	$game_website = $post_metdata['Game website'][0];
	
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
					<br/>
					<a href=".$game_website.">".$game_website."<a/>
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

add_action( 'init', 'game_init' );
add_action('admin_menu','wp_add_game_tab');
add_shortcode('add_game', 'shortcode_add_game' );
?>