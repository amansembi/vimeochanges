<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>

<!--<h2 class="wvv-mb-0"><?php// echo get_the_title( $_GET['id'] ); ?></h2>--->
<?php
$vimeo_id = $db_helper->get_vimeo_id( $_GET['id'] );


?>
<div class="wvv-box" style="max-width: 500px;">
 <form class="wvv-video-upload" enctype="multipart/form-data" method="post" action="/">
 <?php
			$taxonomy = 'classes_category';
			$terms = get_terms($taxonomy);
			$vimeo_video_type = get_option( 'dgv_response_type_'.$vimeo_id );
			$vimeo_video_min_age = get_option( 'dgv_response_min_age_'.$vimeo_id );
			$vimeo_video_max_age = get_option( 'dgv_response_max_age_'.$vimeo_id );
			$vimeo_video_techr = get_option( 'dgv_response_teacher_'.$vimeo_id );
			
			?>
		<div class="form-row">
            <label for="vimeo_title"><?php _e( 'Title', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_title" id="vimeo_title" value="<?php echo get_the_title( $_GET['id'] ); ?>" disabled >
        </div>
		<div class="form-row">
            <label for="vimeo_description"><?php _e( 'Description', 'wp-vimeo-videos' ); ?></label>
            <textarea name="vimeo_description" id="vimeo_description" disabled><?php echo get_the_excerpt($_GET['id']); ?></textarea>
        </div>
		<div class="form-row">
            <label for="vimeo_video_type"><?php _e( 'Type', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_video_type" id="vimeo_video_type" value="<?php echo $vimeo_video_type; ?>" disabled >
        </div>
		
		<div class="form-row">
            <label for="vimeo_video_type"><?php _e( 'Minimum Age', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_video_min_age" id="vimeo_video_min_age" value="<?php echo $vimeo_video_min_age; ?>" disabled >
        </div>
		
		<div class="form-row">
            <label for="vimeo_video_type"><?php _e( 'Maximum  Age', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_video_max_age" id="vimeo_video_max_age" value="<?php echo $vimeo_video_max_age; ?>" disabled >
        </div>
		
		<div class="form-row">
            <label for="vimeo_video_type"><?php _e( 'Recruiting teacher', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_video_techr" id="vimeo_video_techr" value="<?php echo $vimeo_video_techr; ?>" disabled >
        </div>
		
		
		 
		<div class="form-row">
            <label for="vimeo_video"><?php _e( 'Video File', 'wp-vimeo-videos' ); ?></label>
			
			<iframe src="https://player.vimeo.com/video/<?php echo $vimeo_id; ?>?badge=0" width="640" height="346" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
           
        </div>
        
		</form>
</div>	



