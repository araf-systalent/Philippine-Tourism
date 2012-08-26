<?php
$table = $table_prefix."amenities";
wp_reset_query();


function getAmenities($id=0){
	$table = $table_prefix."amenities";
	if($id<=0){
		echo $sql = "SELECT * FROM ".$table;
		//$mylink = $wpdb->get_results($sql); 
		

		/*
		$arrData = '';
		foreach($mylink as $data){
			$arrId = $data->id;
			$arrName = $data->amenity_name;
			$arrData .= '<input type="checkbox" value="am-'.$arrId.'" id="am-'.$arrId.'" name="amenities[]"><label for="am-'.$arrId.'">'.$arrName.'</label><br />';
			//$arrData .= "<li>".$data->amenity_name . '</li>';
		}
		return $arrData;
		 * 
		 */
	}else{
		
	}
}



class Dashboard{
	public $wpdb;
	public $table_prefix;
	public $current_user;
	
	public function __construct($wpdb,$table_prefix,$current_user){
		$this->wpdb = $wpdb;
		$this->table_prefix = $table_prefix;
		$this->current_user = $current_user;
	}
	
	public function testWPDB(){
		return $this->wpdb;
	}
	
	public function testTablePrefix(){
		return $this->table_prefix;
	}
	
	public function getAmenity($id=0){
		if($id<=0){
			//get all
			$sql = "SELECT * FROM ".$this->table_prefix."amenities as amenity ORDER BY amenity.amenity_name ASC";
		}else{
			//get specific
			$sql = "SELECT * FROM ".$this->table_prefix."amenities as amenity WHERE amenity.id = '".$id."' ORDER BY amenity_name ASC";
		}
		$result = $this->wpdb->get_results($sql);
		return ($result);
	}
	
	public function managePost($action="addnew", $data){
		$postTable = $this->table_prefix."posts";
		$amenityTable = $this->table_prefix."my_amenities";
		$infoTable = $this->table_prefix."info";
		$otherInfoTable = $this->table_prefix."other_info";
		$userPosts = $this->table_prefix."user_posts";
		$imagePosts = $this->table_prefix."images";
		
		$author_id = $this->current_user->ID;
		
		$postData = array("post_author"=>$author_id,
						  "post_category"=>$data['inputCategory'],
						  "post_content"=>$data['postcontent'],
						  "post_name"=>wp_strip_all_tags( $data['post_title'] ),
						  "post_title"=> wp_strip_all_tags( $data['post_title'] ),
						  "post_type"=>"post",
						  "post_status" => "publish",
						  "tags_input"=>$data['tags']
						  );
		$postID = wp_insert_post( $postData, $wp_error );
		
		/**
		 * Insert Photos
		 */
		foreach($data['uploadedfiles'] as $img){
			$imagePostData = array("post_id"=>$postID,
								   "thumb"=>$img,
							       "original"=>$img);
			$this->wpdb->insert( $imagePosts, $imagePostData, null );
			unset($imagePostData);
		}
		
		/**
		 * Now we have created a post, we can now add-up some data :)
		 */
		
		$otherInfoData = array("post_id"=>$postID,
							   "location_address"=>$data['post_address'],
							   "contact_number"=>$data['post_phonenumber'],
							   "email"=>$data['post_email'],
							   "website"=>$data['post_website'],
							   "contact_person"=>$data['post_contactname']
							   );
		
		$this->wpdb->insert( $otherInfoTable, $otherInfoData, null );
		
		foreach($data['inputTags'] as $am){
			$amenityData = array( "amenity_id"=>$am,
								  "post_id"=>$postID );
			$this->wpdb->insert( $amenityTable, $amenityData, null );				  
		}
		
		$userPostsData = array("user_id"=>$author_id,"post_id"=>$postID);
		
		$this->wpdb->insert( $userPosts, $userPostsData, null );	
		
		if(!$wp_error){
			return "success";
		}	
	}
	
	public function getUserPosts($id=0){
		$userPosts = $this->table_prefix."user_posts";
		$author_id = $this->current_user->ID;
		
		$sql = "SELECT * FROM ".$userPosts . " WHERE user_id = '".$author_id."'";
		$results = $this->wpdb->get_results($sql);

		foreach($results as $resData){
			$articleID = $resData->post_id;
			$data[] = $this->getPostDetails($articleID);
		}
		return ($data);
	}
	
	private function getPostDetails($post_id){
		$postTable = $this->table_prefix."posts";
		$sql = "SELECT * FROM cutsandp_destination_posts WHERE ID=".$post_id." AND post_type='post'";
		$result = $this->wpdb->get_row( $this->wpdb->prepare( $sql ) );
		return($result);
	}
	
	
	public function addCalendarEvent($data){
		$dates = explode("|",$data["eventDate"]);
		$dataArray = array("dateOfActivity"=>$dates[1],
						   "titleOfActivity"=>wp_strip_all_tags( $data['eventTitle']),
						   "descriptionOfActivity"=>wp_strip_all_tags( $data['eventDesc']),
						   "activityTags"=>wp_strip_all_tags( $data['eventTag']),
						   "eventDateInt"=>$dates['2']
						   );
		$result = $this->wpdb->insert($this->table_prefix."calendar", $dataArray);
								   
		return $result;
	}
	
	public function hasEvent($time){
		$sql = "SELECT * FROM ". $this->table_prefix."calendar WHERE eventDateInt ='".$time."' LIMIT 1";
		$result = $this->wpdb->get_results($sql);
		return $result;
	}
	
}
?>