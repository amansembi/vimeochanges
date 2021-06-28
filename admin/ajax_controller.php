<?php
require_once('../../../../wp-load.php');
if($_POST['type'] == 'add_giftcart')
{
	
}else{
	if ($_POST) {
		
		$videoID = $_POST['videoID'];
		$vimeo_video_link = $_POST['vimeo_video_link'];
		
		function getVimeoStats($id) {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, "http://vimeo.com/api/v2/video/$id.php");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_TIMEOUT, 30);
						$output = unserialize(curl_exec($ch));
						$output = $output[0];
						curl_close($ch);
						return $output;
					}
		if($videoID != ''){
			$videoID = preg_replace( '/\D/', '', html_entity_decode($videoID) );
		}
		if($vimeo_video_link != ''){
			$videoID = preg_replace( '/\D/', '', html_entity_decode($vimeo_video_link) );
			$uploadedContent = getVimeoStats($videoID); 
			$title = $uploadedContent['title'];
			$description = $uploadedContent['description'];
			$post_id = wp_insert_post(array (
			   'post_type' => 'dgv-upload',
			   'post_title' => $title,
			   'post_content' => $description,
			   'post_status' => 'publish',
			   'comment_status' => 'closed',   
			   'ping_status' => 'closed',     
			));
			update_post_meta($post_id,'dgv_response','/videos/'.$videoID);
			if($post_id){
				echo "updated";
			}else{
				echo "error";
			}
		}
		//$videoID = preg_replace( '/\D/', '', html_entity_decode($videoID) );
		$type = $_POST['type'];
		$teacherId = $_POST['teacherId'];
		$classesName = $_POST['classesName'];
		$classesId = $_POST['classesId'];
		$vimeo_video_techr = $_POST['vimeo_video_techr'];		
		$vimeo_video_min_age = $_POST['vimeo_video_min_age'];
		$vimeo_video_max_age = $_POST['vimeo_video_max_age'];
		/***************teacher_video***********/
		$final_teacher_video = get_post_meta( $teacherId, 'teacher_video', true );
		if(!is_array($final_teacher_video) || $final_teacher_video == ''){
			$final_teacher_video = array();	
		}
		if(!in_array($videoID,$final_teacher_video)){
			$final_teacher_video[$videoID] = $videoID;	
		}
		/***************classes_video***********/
		$final_classes_video = get_post_meta( $classesId, 'classes_video', true );
		if(!is_array($final_classes_video) || $final_classes_video == ''){
			$final_classes_video = array();	
		}
		if(!in_array($videoID,$final_classes_video)){
			$final_classes_video[$videoID] = $videoID;	
		}	
		update_post_meta( $classesId, 'classes_video', $final_classes_video);		
		update_post_meta( $teacherId, 'teacher_video', $final_teacher_video);		
		update_post_meta( $teacherId, 'teacher_video_duplicate', $final_teacher_video);		
	    update_option('teacher_id_'.$videoID, $teacherId);
	    update_option('dgv_response_min_age_'.$videoID, $vimeo_video_min_age);
	    update_option('dgv_response_max_age_'.$videoID, $vimeo_video_max_age);
		update_option('dgv_response_type_'.$videoID, $type);
		update_option('dgv_response_teacher_'.$videoID,$vimeo_video_techr);
	}
}
if($_POST['deletetype'] == 'deletetype')
{
	$vidId = $_POST['vidId'];
	$vimeoVedioID = get_post_meta( $vidId, 'dgv_response', true );
	$result = wp_delete_post( $vidId, true);
	$vimeoVideoID = preg_replace( '/\D/', '', html_entity_decode($vimeoVedioID) );	
	$delTeacherId = get_option( 'teacher_id_'.$vimeoVideoID);
	$teacherVideoArray = get_post_meta( $delTeacherId, 'teacher_video', true );	
	if(in_array($vimeoVideoID,$teacherVideoArray)){
			unset($teacherVideoArray[$vimeoVideoID]);
			
		}		
		update_post_meta( $delTeacherId, 'teacher_video', $teacherVideoArray);	
		delete_option('teacher_id_'.$vimeoVideoID);
	if(!empty($result)){
		echo 'deleted';
	}
	//$classesVideoArray = get_post_meta('classes_video');
	//print_r($classesVideoArray);
	/*if(in_array($vimeoVideoID,$teacherVideoArray)){
			unset($teacherVideoArray[$vimeoVideoID]);			
		}		
		update_post_meta( $delTeacherId, 'teacher_video', $teacherVideoArray);	
		delete_option('teacher_id_'.$vimeoVideoID);
	if(!empty($result)){
		echo 'deleted';
	}*/
}


?>