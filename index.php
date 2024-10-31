<?php
/*
Plugin Name: Repeater Field
Plugin URI: https://www.webuters.com
Description: This plugin Add Repeater Field
Version: 1.0
Author: Webuters
Text Domain: Repeater Field
Originally developed for the - Repeater Field
Tested up to: 5.8.2
Stable tag: 1.0
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function wrf_repeat_css() {
    wp_register_style('wrfCss', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('wrfCss');
	wp_enqueue_style( 'wrfcss-all', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
}

add_action( 'admin_init','wrf_repeat_css');

add_action('admin_init', 'wrf_add_meta_boxes', 1);
function wrf_add_meta_boxes() {
	add_meta_box( 'repeatable-fields', 'Service Inner Content', 'wrf_repeatable_meta_box_display', ['post','page'], 'normal', 'high');
}



function wrf_repeatable_meta_box_display() {
	global $post;
	
	$repeatable_fields = get_post_meta($post->ID, 'repeatable_fields', true);

	wp_nonce_field( 'hhs_repeatable_meta_box_nonce', 'hhs_repeatable_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});
  	
		$( '.remove-row' ).on('click', function() {
			$(this).parents('tr').remove();
			return false;
		});
	});
	</script>
  
	<table id="repeatable-fieldset-one" width="100%" class="arf-table">
	<thead>
		<tr>
			<th width="33.33%">Title</th>
			<th width="33.33%">Sub Title</th>
			<th width="43.33%">Content</th>
			<th width="10%">Section Image</th>
		</tr>
	</thead>
	<tbody>
	<?php

	// echo "<pre>";
	// print_r($repeatable_fields);
	// echo "</pre>";
	// exit();
	
	if (is_array($repeatable_fields)) :
		$x=0;
	foreach ( $repeatable_fields as $field ) {
		
	?>
	<tr>
		
		<td>
			<input type="text" class="widefat" name="title[]" value="<?php if($field['title'] != '') echo esc_attr( $field['title'] ); ?>" />
		</td>
	
		<td>
        	<input type="text" class="widefat" name="subtitle[]" value="<?php if($field['subtitle'] != '') echo esc_attr( $field['subtitle'] ); ?>" />
		</td>
	
		<td>
			<?php
			 $editor_id='description_'.$x;
			
			$settings = array( 'textarea_name' => 'description[]' );

	        wp_editor( esc_textarea( $field['description']), $editor_id, $settings ); ?>       
        </td>

        <td>
        	<input type="file" name="section_image[]" placeholder="Section Image">
        	<input type="hidden" name="section_image_hidden[]" value="<?php echo esc_attr( $field['section_image'] ); ?>">
        <?php if($field['section_image'] != ''){ ?>
        	<img src="<?php echo esc_attr( $field['section_image'] ); ?>" width="50px;" height="50px;">
        <?php } ?>
    	</td>
	
		<td style="background: #F9F9F9;border-left-color: #DFDFDF;"><a class="remove-row" href="#"><i class="fa fa-minus-circle fa-2x" style="color:red"></i></a></td>
	</tr>
	<?php
	$x++;
	}
	else :

	?>
	<tr>

		<td>
			<input type="text" class="widefat" name="title[]" />
		</td>
	
		<td>
        	<input type="text" class="widefat" name="subtitle[]"  />
		</td>
	
		<td>
			<?php
			$t=time();
			 $editor_id='description_'.$t;
			
			$settings = array( 'textarea_name' => 'description[]' );

	        wp_editor( "Put Your Content Here", $editor_id, $settings ); ?>
        	
        </td>

        <td>
			<input type="file" name="section_image[]" placeholder="Section Image" >
		</td>
	
		<td style=" background: #F9F9F9;border-left-color: #DFDFDF;"><a class="remove-row" href="#"><i class="fa fa-minus-circle fa-2x" style="color:red"></i></a></td>
	</tr>
	<?php endif; ?>
	
	<tr class="empty-row screen-reader-text">

		<td><input type="text" class="widefat" name="title[]" /></td>
	
		<td>
        	<input type="text" class="widefat" name="subtitle[]" />
		</td>
		
		<td>
			<?php
			$t=time();
			 $editor_id='description_'.$t;
			
			$settings = array( 'textarea_name' => 'description[]' );

	        wp_editor( "Put Your Content Here", $editor_id, $settings ); ?>
        </td>

        <td>
			<input type="file" name="section_image[]" placeholder="Section Image" >
		</td>
		  
		<td style="background: #F9F9F9;border-left-color: #DFDFDF;">
			<a class="remove-row" href="#"><i class="fa fa-minus-circle fa-2x" style="color:red"></i></a>
		</td>
		
	</tr>
	</tbody>
	</table>
	
	<p style="text-align:right"><a id="add-row" class="arf-button button button-primary" href="#">Add More</a></p>
	<?php
}

add_action('save_post', 'wrf_repeatable_meta_box_save');
function wrf_repeatable_meta_box_save($post_id) {

	$plugin_dir = plugins_url();
	$target_dir = plugin_dir_path(__FILE__);

	if ( ! isset( $_POST['hhs_repeatable_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['hhs_repeatable_meta_box_nonce'], 'hhs_repeatable_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$old = get_post_meta($post_id, 'repeatable_fields', true);
	
	$new = array();

	// echo "<pre>";
	// print_r($_FILES);
	// echo "</pre>";

	// exit();
	
	
	$titles = sanitize_text_field($_POST['title']);
	$subtitles = sanitize_text_field($_POST['subtitle']);
	$descriptions = sanitize_textarea_field($_POST['description']);
	$sectionImage = sanitize_file_name($_POST['section_image']);
	$sectionImageHidden = sanitize_file_name($_POST['section_image_hidden']);
	
	
	$count = count( $titles );
	
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $titles[$i] != '' ) :
			$new[$i]['title'] = stripslashes( $titles[$i]  );
			
            if ( $subtitles[$i] == '' )
				$new[$i]['subtitle'] = '';
			else
				$new[$i]['subtitle'] = stripslashes( $subtitles[$i] );
		
			if ( $descriptions[$i] == '' )
				$new[$i]['description'] = '';
			else

		$new[$i]['description'] =  $descriptions[$i];
				$new[$i]['description'] = stripslashes( $descriptions[$i] );



		if(isset($_FILES['section_image']['name'][$i]) && ($_FILES['section_image']['size'][$i] > 0)){

		  // UPLOAD SECTION IMAGE (START) //
		    $mainFileName = $_FILES['section_image']['name'][$i];

		    $actual_name = pathinfo($mainFileName,PATHINFO_FILENAME);
		    $original_name = $actual_name;
		    $extension = pathinfo($mainFileName, PATHINFO_EXTENSION);

		    $j = 1;
		    while(file_exists($plugin_dir."/repeater-field/images/".$actual_name.".".$extension))
		    {          
		        $actual_name = (string)$original_name."~".$j;
		        $mainFileName = $actual_name.".".$extension;
		        $j++;
		    }
		    
		    $mainFileTempName = $_FILES['section_image']['tmp_name'][$i];
		    $targetMainFile   = $target_dir."images/".basename($mainFileName);

		  // UPLOAD CATEGORY IMAGE (END) //


		    move_uploaded_file($mainFileTempName, $targetMainFile);


	    	$new[$i]['section_image'] = $plugin_dir."/repeater-field/images/".$mainFileName;
		  }elseif($sectionImageHidden[$i] !="" ){
		  	$new[$i]['section_image'] =  $sectionImageHidden[$i]; 
		  }else{
		  	$new[$i]['section_image'] = '';
		  }

			
		endif;
	}

	if ( !empty( $new ) && $new != $old )
		update_post_meta( $post_id, 'repeatable_fields', $new );
	elseif ( empty($new) && $old )
		delete_post_meta( $post_id, 'repeatable_fields', $old );
}