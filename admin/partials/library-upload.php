<?php
/* @var WP_DGV_Api_Helper $vimeo_helper */
/* @var WP_DGV_Db_Helper $db_helper */
?>


<!-- jQuery library -->


<h2><?php _e( 'Upload to Vimeo', 'wp-vimeo-videos' ); ?></h2>

<!-------------------------------------------------------------------------------->
<div class="vimeoUploadTabs">
<div class="demo">
  <div role="tabpanel">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-justified nav-tabs-dropdown" role="tablist">
      <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Upload video</a></li>
      <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Existing video link</a></li>
      
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="home">
	  
	<div class="wvv-box" style="max-width: 500px;">
    <form class="wvv-video-upload" enctype="multipart/form-data" method="post" >
        <div class="form-row">
            <label for="vimeo_title"><?php _e( 'Title', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_title" id="vimeo_title">
        </div>
        <div class="form-row">
            <label for="vimeo_description"><?php _e( 'Description', 'wp-vimeo-videos' ); ?></label>
            <textarea name="vimeo_description" id="vimeo_description"></textarea>
        </div>
         <div class="form-row">
            <label for="vimeo_video_type">Type</label>	
		<?php
			$taxonomy = 'classes_category';
			$terms = get_terms($taxonomy); 			
			?>
			<select class="form-select" name="vimeo_video_type" id="vimeo_video_type" >
				<option>Select Type</option>
			<?php foreach($terms as $term){ ?>
				<option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
			<?php	} ?>
			</select>
        </div>

         <div class="form-row">
            <label for="vimeo_video_type">Minimum Age</label>
           <!-- <p><input type="text" name="vimeo_video_age" id="vimeo_video_age" value=""></p>--->
		   <select class="form-select" name="vimeo_video_min_age" id="vimeo_video_min_age" >
			  <option>Select Min Age</option>
			  <?php for($i=5; $i<=40;$i++){ ?>			  
				<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			  <?php } ?>			  
			</select>
			 <label for="vimeo_video_type">Maximum  Age</label>
			<select class="form-select" name="vimeo_video_max_age" id="vimeo_video_max_age" >
			  <option>Select Max Age</option>
			  <?php for($i=5; $i<=40;$i++){ ?>	
			  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			  <?php } ?>	  
			</select>
        </div>

        <div class="form-row">
			<label for="vimeo_video_type"> Recruiting teacher</label>
            <!--<p><input type="text" name="vimeo_video_techr" id="vimeo_video_techr" value=""></p>-->
            <input type="hidden" name="childpath" value="<?php echo plugins_url('', dirname(__FILE__) );  ?>">
				<?php 
					$args = array( 'post_type' => 'instructor', 'posts_per_page' => -1 );
					$the_query = new WP_Query( $args ); 
				?>  
				<select class="form-select" name="vimeo_video_techr" id="vimeo_video_techr" >
				<option>Select Instructor</option>				
				<?php if ( $the_query->have_posts() ) : ?>                          
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 				
						$exploreimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); 
				?>
				
				<option value="<?php the_title();?>" data="<?php echo the_ID();?>"><?php the_title();?></option>
				
				<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					<?php else:  ?>
					<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
			    <?php endif; ?>
				</select>
        </div>
		
		 <div class="form-row">
			<label for="vimeo_video_classes"> Classes</label>
            <!--<p><input type="text" name="vimeo_video_techr" id="vimeo_video_techr" value=""></p>-->
            <input type="hidden" name="childpath" value="<?php echo plugins_url('', dirname(__FILE__) );  ?>">
				<?php 
					$args = array( 'post_type' => 'classes', 'posts_per_page' => -1 );
					$the_query = new WP_Query( $args ); 
				?>  
				<select class="form-select" name="vimeo_video_classes" id="vimeo_video_classes" >
				<option>Select Classes</option>				
				<?php if ( $the_query->have_posts() ) : ?>                          
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 				
						//$exploreimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); 
				?>
				
				<option value="<?php the_title();?>" data="<?php echo the_ID();?>"><?php the_title();?></option>
				
				<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					<?php else:  ?>
					<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
			    <?php endif; ?>
				</select>
        </div>
		
        <div class="form-row">
            <label for="vimeo_video"><?php _e( 'Video File', 'wp-vimeo-videos' ); ?></label>
            <p><input type="file" name="vimeo_video" id="vimeo_video"></p>
            <div class="dgv-progress-bar" style="display: none;">
                <div class="dgv-progress-bar-inner"></div>
                <div class="dgv-progress-bar-value">0%</div>
            </div>
        </div>
        <div class="form-row with-border">
            <div class="dgv-loader" style="display:none;"></div>
            <button type="submit" class="button-primary" name="vimeo_upload" value="1">
				<?php _e( 'Upload', 'wp-vimeo-videos' ); ?>
            </button>
        </div>
    </form>
</div>
	  
	  
	  
	  </div>
<div role="tabpanel" class="tab-pane" id="profile">
	  
<div class="wvv-box" style="max-width: 500px;">
<div class="update_status"></div>
    <form class="update_existing_video" enctype="multipart/form-data" method="post" >
       <!-- <div class="form-row">
            <label for="vimeo_title"><?php// _e( 'Title', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_title" id="vimeo_title">
        </div>
        <div class="form-row">
            <label for="vimeo_description"><?php// _e( 'Description', 'wp-vimeo-videos' ); ?></label>
            <textarea name="vimeo_description" id="vimeo_description"></textarea>
        </div>-->
         <div class="form-row">
            <label for="vimeo_video_type">Type</label>	
		<?php
			$taxonomy = 'classes_category';
			$terms = get_terms($taxonomy); 			
			?>
			<select class="form-select" name="vimeo_video_type1" id="vimeo_video_type1" >
				<option value="">Select Type</option>
			<?php foreach($terms as $term){ ?>
				<option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
			<?php	} ?>
			</select>
			<p><small class="type_status errorfield" style="color:red;"></small></p>	
        </div>

         <div class="form-row">
            <label for="vimeo_video_type">Minimum Age</label>
           <!-- <p><input type="text" name="vimeo_video_age" id="vimeo_video_age" value=""></p>--->
		   <select class="form-select" name="vimeo_video_min_age1" id="vimeo_video_min_age1" >
			  <option value="">Select Min Age</option>
			  <?php for($i=5; $i<=40;$i++){ ?>			  
				<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			  <?php } ?>			  
			</select>
			<p><small class="minage_status errorfield" style="color:red;"></small></p>	
			 <label for="vimeo_video_type">Maximum  Age</label>
			<select class="form-select" name="vimeo_video_max_age1" id="vimeo_video_max_age1" >
			  <option value="">Select Max Age</option>
			  <?php for($i=5; $i<=40;$i++){ ?>	
			  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			  <?php } ?>	  
			</select>
			<p><small class="maxage_status errorfield" style="color:red;"></small></p>	
        </div>

        <div class="form-row">
			<label for="vimeo_video_type"> Recruiting teacher</label>
            <!--<p><input type="text" name="vimeo_video_techr" id="vimeo_video_techr" value=""></p>-->
            <input type="hidden" name="childpath" value="<?php echo plugins_url('', dirname(__FILE__) );  ?>">
				<?php 
					$args = array( 'post_type' => 'instructor', 'posts_per_page' => -1 );
					$the_query = new WP_Query( $args ); 
				?>  
				<select class="form-select" name="vimeo_video_techr1" id="vimeo_video_techr1" >
				<option value="">Select Instructor</option>				
				<?php if ( $the_query->have_posts() ) : ?>                          
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 				
						$exploreimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); 
				?>
				
				<option value="<?php the_title();?>" data="<?php echo the_ID();?>"><?php the_title();?></option>
				
				<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					<?php else:  ?>
					<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
			    <?php endif; ?>
				</select>
				<p><small class="teacher_status errorfield" style="color:red;"></small></p>	
        </div>
		
		 <div class="form-row">
			<label for="vimeo_video_classes"> Classes</label>
            <!--<p><input type="text" name="vimeo_video_techr" id="vimeo_video_techr" value=""></p>-->
            <input type="hidden" name="childpath" value="<?php echo plugins_url('', dirname(__FILE__) );  ?>">
				<?php 
					$args = array( 'post_type' => 'classes', 'posts_per_page' => -1 );
					$the_query = new WP_Query( $args ); 
				?>  
				<select class="form-select" name="vimeo_video_classes1" id="vimeo_video_classes1" >
				<option value="">Select Classes</option>				
				<?php if ( $the_query->have_posts() ) : ?>                          
					<?php while ( $the_query->have_posts() ) : $the_query->the_post(); 				
						//$exploreimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); 
				?>
				
				<option value="<?php the_title();?>" data="<?php echo the_ID();?>"><?php the_title();?></option>
				
				<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
					<?php else:  ?>
					<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
			    <?php endif; ?>
				</select>
				
        </div>
		
        <div class="form-row">
            <label for="vimeo_video_link"><?php _e( 'Video File Link', 'wp-vimeo-videos' ); ?></label>
            <input type="text" name="vimeo_video_link" id="vimeo_video_link" placeholder="Enter video link">
			<p><small class="video_link_status errorfield" style="color:red;"></small></p>	
           
        </div>
        <div class="form-row with-border">
            <div class="dgv-loader" style="display:none;"></div>
            <button type="submit" class="button-primary" name="existing_link_submit" value="1">
				<?php _e( 'Upload', 'wp-vimeo-videos' ); ?>
            </button>
        </div>
    </form>
</div>
	  
	  
	  </div>
      
    </div>
  </div>
</div>
</div>
<!-------------------------------------------------------------------------------->



<p>
    <a href="<?php echo admin_url( 'upload.php?page=' . WP_DGV_Admin::PAGE_VIMEO ); ?>"><?php _e( '< Back to library', 'wp-vimeo-videos' ); ?></a>
</p>
