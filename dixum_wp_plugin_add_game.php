<?php
/**
 * Plugin Name: Add game
 * Version: The Plugin's Version Number, e.g.: 1.0
 * Author: Dixum
 * Author URI: www.dixum.se
 */

 /*  Copyright Dixum
*/
function wp_add_game_page(){
	
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	

	global $wpdb;
	$status = '';
	$sql = 
			"CREATE TABLE IF NOT EXISTS `wp_games` (
				`Post_id` int(11) NOT NULL,
				`Pic_id` int(11) NOT NULL,
				`Link_android` varchar(120) NOT NULL,
				`Link_ios` varchar(120) NOT NULL,
				`Id` int(11) NOT NULL AUTO_INCREMENT,
				`Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`Id`)
			)";
	
	$wpdb->query($sql);
	
	
	if(isset($_POST['post_game'])&&!empty($_POST['post_game'])){
	
		$game_name = htmlentities(trim($_POST['game_name']));
		$game_description = nl2br(htmlentities(trim($_POST['game_description'])));
		$game_link_android = $_POST['game_link_android'];
		$game_link_ios = $_POST['game_link_ios'];
		
		if(strrpos($game_link_android, 'http://') === false){
			$game_link_android = 'http://'.$game_link_android;
		}
		if(strrpos($game_link_ios, 'http://') === false){
			$game_link_ios = 'http://'.$game_link_ios;
		}
		if(isset($game_name)&&!empty($game_name)&&isset($game_description)&&!empty($game_description)&&isset($game_link_android)&&!empty($game_link_android)&&isset($game_link_ios)&&!empty($game_link_ios)){
			if(strlen($game_name) <= 70 && strlen($game_description) <= 700 && strlen($game_link_android) <= 120 && strlen($game_link_ios) <= 120){
				$file_types_array=array("image/jpeg","image/png");
				if(in_array($_FILES['game_picture']['type'],$file_types_array)){
					$upload_overrides = array( 'test_form' => false );
					
					$attachment_id = media_handle_upload( 'game_picture', $_POST['post_id'] );
					
				
					$new_post = array(
								'post_title'	=> $game_name,
								'post_name'		=> $game_name,
								'post_content'  => $game_description,
								'post_status'   => 'publish',
								'post_type'		=> 'game'
								);	
					
					$post = wp_insert_post($new_post); 
				
					if($attachment_id&&$post){
						
						$insert = array(	
									'Post_id' => $post,
									'Pic_id' => $attachment_id,
									'Link_android' => $game_link_android,
									'Link_ios' => $game_link_ios,
									);
									
									
						if($wpdb->insert('wp_games',$insert)){
							$status = 'Send';
						}else{
							$status = 'Something went wrong';
						}
					}else{
						$status = 'Something went wrong';
					}
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
			<h2><?php echo $status; ?></h2>
			<input required type="text" placeholder="Game name" name="game_name" maxlength="50"/><br/>
			<input required type="file" name="game_picture" id="game_picture"/><br/>
			<input type="hidden" name="post_id" id="post_id" value="55" />
			<input required type="text" placeholder="Android link" name="game_link_android" maxlength="100"/><br/>
			<input required type="text" placeholder="IOS link" name="game_link_ios" maxlength="100"/><br/>
			<textarea required name="game_description" placeholder="Description" maxlength="400"></textarea><br/>
			<input type="submit" name="post_game" value="Post game"/> 
		</form>
	</div>
	
	
	<?php
	
	ob_get_clean();
}

function wp_add_game_tab(){
	add_submenu_page('edit.php?post_type=game','wp_add_game','Add Game','manage_options','addgame','wp_add_game_page');
	remove_submenu_page('edit.php?post_type=game', 'post-new.php?post_type=game');
	
}
function get_category_id($cat_name){
	$term = get_term_by('name', $cat_name, 'category');
	return $term->term_id;
}
function shortcode_add_game($atts){
	
	
	
	$id = $atts['id'];
	
	global $wpdb;
	
	$sql = "SELECT * FROM `wp_games` WHERE `Post_id` = '$id'";
	
	$thepost = $wpdb->get_row($sql, ARRAY_A);
	
	$id =  $thepost['Id'];
	$post_id = $thepost['Post_id'];
	$pic_id = $thepost['Pic_id'];
	$link_android = $thepost['Link_android'];
	$link_ios = $thepost['Link_ios'];
	
	
	$attachment_url = wp_get_attachment_url($pic_id);
	$dir = substr($attachment_url,strpos($attachment_url,'/wp-content'),strlen($attachment_url));
	$post_arr = get_post($post_id,ARRAY_A);
	$game_name = $post_arr['post_title'];
	$description = nl2br($post_arr['post_content']);
	
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
		'supports'           => array( 'title', 'editor', 'author','thumbnail')
	);

	register_post_type( 'game', $args );
	

	
}
add_action( 'init', 'game_init' );
add_action('admin_menu','wp_add_game_tab');
add_shortcode('add_game', 'shortcode_add_game' );
?>