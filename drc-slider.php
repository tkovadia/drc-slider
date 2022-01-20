<?php
/*
Plugin Name: DRC Slider
Plugin URI:
Description: DRC Slider
Author: Tushar Patel
Author URI:
Version: 1.0
Text Domain: drc-slider
License: GPL2
*/

if (!defined('ABSPATH')) exit('No direct script access allowed');

//define( 'DRCS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
//define( 'DRCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


class DRC_Slider {
	
	public function __construct()
	{			
		add_action( 'init', array( $this,'drs_register_post_type') );
		add_filter( 'manage_slider_posts_columns', array($this, 'drc_set_shortcode_edit_slider_columns') );
		add_action( 'manage_slider_posts_custom_column' , array($this, 'shortcode_slider_column' ), 10, 2 );
		add_action( 'admin_init', array($this, 'drc_add_slider_meta_box') );
		add_action( 'save_post', array($this, 'drc_slider_meta_box_save'));
		
		add_action( 'admin_enqueue_scripts', array($this, 'drc_admin_scripts') );
		add_action( 'wp_enqueue_scripts', array($this, 'drc_frontend_scripts') );
	
		add_shortcode('DRS_Slider', array($this, 'drc_slider_shortcode'));
	}
	
	// Register Slider post type
	public function drs_register_post_type(){
		$labels = [
			"name" => __( "Slider", "drc-slider" ),
			"singular_name" => __( "Slider", "drc-slider" ),
			"menu_name" => __( "My Slider", "drc-slider" ),
			"all_items" => __( "All Slider", "drc-slider" ),
			"add_new" => __( "Add new", "drc-slider" ),
			"add_new_item" => __( "Add new Slider", "drc-slider" ),
			"edit_item" => __( "Edit Slider", "drc-slider" ),
			"new_item" => __( "New Slider", "drc-slider" ),
			"view_item" => __( "View Slider", "drc-slider" ),
			"view_items" => __( "View Slider", "drc-slider" ),
			"search_items" => __( "Search Slider", "drc-slider" ),
			"not_found" => __( "No Slider found", "drc-slider" ),
			"not_found_in_trash" => __( "No Slider found in trash", "drc-slider" ),
			"parent" => __( "Parent Slider:", "drc-slider" ),
			"featured_image" => __( "Featured image for this Slider", "drc-slider" ),
			"set_featured_image" => __( "Set featured image for this Slider", "drc-slider" ),
			"remove_featured_image" => __( "Remove featured image for this Slider", "drc-slider" ),
			"use_featured_image" => __( "Use as featured image for this Slider", "drc-slider" ),
			"archives" => __( "Slider archives", "drc-slider" ),
			"insert_into_item" => __( "Insert into Slider", "drc-slider" ),
			"uploaded_to_this_item" => __( "Upload to this Slider", "drc-slider" ),
			"filter_items_list" => __( "Filter Slider list", "drc-slider" ),
			"items_list_navigation" => __( "Slider list navigation", "drc-slider" ),
			"items_list" => __( "Slider list", "drc-slider" ),
			"attributes" => __( "Slider attributes", "drc-slider" ),
			"name_admin_bar" => __( "Slider", "drc-slider" ),
			"item_published" => __( "Slider published", "drc-slider" ),
			"item_published_privately" => __( "Slider published privately.", "drc-slider" ),
			"item_reverted_to_draft" => __( "Slider reverted to draft.", "drc-slider" ),
			"item_scheduled" => __( "Slider scheduled", "drc-slider" ),
			"item_updated" => __( "Slider updated.", "drc-slider" ),
			"parent_item_colon" => __( "Parent Slider:", "drc-slider" ),
		];

		$args = [
			"label" => __( "Slider", "drc-slider" ),
			"labels" => $labels,
			"description" => "",
			"public" => false,
			"publicly_queryable" => false,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => false,
			"show_in_menu" => true,
			"menu_icon" => 'dashicons-images-alt',
			"show_in_nav_menus" => false,
			"delete_with_user" => false,
			"exclude_from_search" => true,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => [ "slug" => "slider", "with_front" => true ],
			"query_var" => true,
			"supports" => [ "title" ],
			"show_in_graphql" => false,
		];

		register_post_type( "slider", $args );
	}
	
	// Add the shortcode columns
	public function drc_set_shortcode_edit_slider_columns($columns) {
		unset( $columns['author'] );
		$date = $columns['date'];
		unset( $columns['date'] );
		$columns['shortcode'] = __( 'Shortcode', 'drc-slider' );
		$columns['date'] = __( 'Date', 'drc-slider' );

		return $columns;
	}
	
	// Add the data to the shortcode columns
	public function shortcode_slider_column( $column, $post_id ) {
		switch ( $column ) {

			case 'shortcode' :
				echo '<code>[mySlideshow id="'.$post_id.'"]</code>'; 
				break;
			case 'date' :
				echo get_the_date( 'dS M Y', $post_id ); 
				break;	

		}
	}
	
	// Add meta box
	public function drc_add_slider_meta_box(){
		add_meta_box( 'slider_shortcode_meta_box',
			'Slider Shortcode ',
			array($this, 'drc_display_slider_shortcode' ),
			'slider',
			'side',
			'high'
		);
		
		add_meta_box( 'slider_repeater_meta_box', 
			'Add Slider Images',
			array($this, 'drc_display_slider_repeater' ),
			'slider',
			'normal',
			'default'
		);
	}
	
	// Add the data to the slider shortcode meta box
	function drc_display_slider_shortcode( $slider ) {
	
		echo '<br/><code>[DRS_Slider id="'.$slider->ID.'"]</code><br/><br/>';
		   
	}
	
	// Add the data to the slider repeater meta box
	function drc_display_slider_repeater( $slider ) {
		global $post;
		$slider_data = get_post_meta($post->ID, 'slider_data', true);
		wp_nonce_field( 'drc_slider_meta_box_nonce', 'drc_slider_meta_box_nonce' ); ?>
		
		<table id="drc_slider_data" width="100%" cellpadding="15">
			<thead>
				<tr>
					<th width="30%">Slider Image</th>
					<th width="40%">Slider Title/Description</th>
					<th width="15%">Show/Hide</th>
					<th width="15%">Action</th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ( $slider_data ) {
				foreach ( $slider_data as $data ) { ?>
					<tr class="single-slider-row ui-state-default">
						<td>
							<div class="hidden_slider_image_div">							
							<?php echo wp_get_attachment_image( $data['SliderImage'], array('150', '150'), "", array( "class" => "img-responsive" ) );  ?>
							</div>
							<input class="button upload_slider_img_button" type="button" value="Upload Image" />
							<input class="hidden_slider_image " type="hidden" name="SliderImage[]" value="<?php echo $data['SliderImage']; ?>" />
						</td> 
						<td>
							<input type="text"  placeholder="Slider Title" name="SliderTitle[]" value="<?php if($data['SliderTitle'] != '') echo esc_attr( $data['SliderTitle'] ); ?>" />
							<br/><br/>
							<textarea placeholder="Description" cols="55" rows="5" name="SliderDescription[]"> <?php if ($data['SliderDescription'] != '') echo esc_attr( $data['SliderDescription'] ); ?> </textarea>
						</td>
						<td >
						<?php print_r($data['SliderAction']); ?>
							<input type="radio" name="SliderAction[1][]" <?php if($data['SliderAction'] == 'Show'){ echo 'checked'; } ?> value="Show"> Show <br/><input type="radio" name="SliderAction[1][]" <?php if($data['SliderAction'] == 'Hide'){ echo 'checked'; } ?> value="Hide"> Hide
						</td>
						<td ><a class="button remove-row" href="#1">Remove</a></td>
					</tr>
					<?php 
				}
			}	
			else { ?>
				<tr class="single-slider-row ui-state-default">
					<td>
						<div class="hidden_slider_image_div"></div>
						<input class="button upload_slider_img_button" type="button" value="Upload Image" />
						<input class="hidden_slider_image " type="hidden" name="SliderImage[]" />
					</td>
					<td>						
						<input type="text" placeholder="Slider Title" name="SliderTitle[]"/>
						<br/><br/>
						<textarea placeholder="Slider Description" cols="35" rows="3" name="SliderDescription[]"></textarea>
					</td>
					<td>
						<input type="radio" name="SliderAction[1][]" value="Show" checked /> Show <br/><input type="radio" name="SliderAction[1][]" value="Hide" /> Hide
					</td>
					<td>
						<a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a>
					</td>
				</tr>
			<?php } ?>
					
				<tr class="empty-row screen-reader-text">
					<td>
						<div class="hidden_slider_image_div"></div>
						<input class="button upload_slider_img_button" type="button" value="Upload Image" />
						<input class="hidden_slider_image " type="hidden" name="SliderImage[]" />
					</td>
					<td>						
						<input type="text" placeholder="Slider Title" name="SliderTitle[]"/>
						<br/><br/>
						<textarea placeholder="Slider Description" cols="35" rows="3" name="SliderDescription[]"></textarea>
					</td>
					<td>
						<input type="radio" name="SliderAction[%s][]" value="Show" checked /> Show <br/><input type="radio" name="SliderAction[%s][]" value="Hide" /> Hide
					</td>
					<td><a class="button remove-row" href="#">Remove</a></td>
				</tr>
				
			</tbody>
		</table>
		<p><a id="add_slider" class="button" href="#">Add Slider</a></p>
		
		
		
	<?php	   
	}
	
	function drc_slider_meta_box_save($post_id) {
		if ( ! isset( $_POST['drc_slider_meta_box_nonce'] ) ||
		! wp_verify_nonce( $_POST['drc_slider_meta_box_nonce'], 'drc_slider_meta_box_nonce' ) )
			return;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		if (!current_user_can('edit_post', $post_id))
			return;

		$old_data = get_post_meta($post_id, 'slider_data', true);
		$new_data = array();
		$SliderImage = $_POST['SliderImage'];
		$SliderTitle = $_POST['SliderTitle'];
		$SliderDescription = $_POST['SliderDescription'];
		$SliderAction = $_POST['SliderAction'];
		$count = count( $SliderImage );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( $SliderImage[$i] != '' ){
				$new_data[$i]['SliderImage'] = $SliderImage[$i];
				$new_data[$i]['SliderTitle'] = stripslashes( strip_tags( $SliderTitle[$i] ) );
				$new_data[$i]['SliderDescription'] = stripslashes( $SliderDescription[$i] );
				$new_data[$i]['SliderAction'] = stripslashes( $SliderAction[$i] );
			}
		}
		if ( !empty( $new_data ) && $new_data != $old_data )
			update_post_meta( $post_id, 'slider_data', $new_data );
		elseif ( empty($new_data) && $old_data )
			delete_post_meta( $post_id, 'slider_data', $old_data );

	
	}
	
	
	public function drc_admin_scripts() {
		global $post_type;
		if( 'slider' == $post_type ){
			wp_enqueue_style( 'drc-admin-style', plugins_url( '/css/admin.css', __FILE__ ) , array(), '1.0.0', 'all' );

			wp_enqueue_media();
			wp_enqueue_script( 'jquery-ui-sortable' );			
			
			wp_enqueue_script( 'drc-slider-js', plugins_url( '/js/drc_admin_coustom.js', __FILE__ ), array('jquery'), null, true );
			wp_localize_script( 'drc-slider-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}
	
	public function drc_frontend_scripts() {
		wp_register_style( 'drc-front-slick-style', plugins_url( '/css/slick.css', __FILE__ ), array(), '1.0.0', 'all' );
		wp_enqueue_script( 'drc-front-slick-js', plugins_url ( '/js/slick.js', __FILE__ ), array('jquery'), null, true );
		wp_enqueue_script( 'drc-front-gallery-js', plugins_url ( '/js/front-end.js', __FILE__ ), array('jquery'), null, true );
	}
	public function drc_slider_shortcode($atts){
		
		$arg = shortcode_atts( array( 'id' => ''), $atts );
	
		$slider_data = '' ; 
		if ( get_post_meta( $arg['id'], 'slider_data', true ) ) {
			$slider_data = get_post_meta( $arg['id'], 'slider_data', true );
		} 
			
		$result = '';
			
		if ( ! empty( $slider_data ) ) {
			$result .= '<section class="lazy slider" data-sizes="50vw">';
			foreach ( $slider_data as $data ) {
				
				if ( $data['SliderAction'] == 'Hide' ) {
					continue;
				}
				
				$attachment = wp_get_attachment_image( $data['SliderImage'], 'full' );
				
				$result .= '<div>';
				$result .= $attachment;
				if($data['SliderTitle'] != ''){
					$result .= '<h2>'.$data['SliderTitle'].'</h2>';
				}
				if($data['SliderDescription'] != ''){
					$result .= '<div><p>'.$data['SliderDescription'].'</p></div>';
				}
				$result .= '</div>';
				
			}
			$result .= '</section>';
			
		}	
		
		wp_enqueue_style( 'drc-front-slick-style' );
		wp_enqueue_script( 'drc-front-slick-js' );
		wp_enqueue_script( 'drc-front-gallery-js' );
		
		return $result;  
	}
}

$DRCslider = new DRC_Slider();
