<?php
/* Invite Anyone database functions */



function invite_anyone_create_table() {
	global $wpdb;
	
	$table_name = $wpdb->base_prefix . 'bp_invite_anyone';
   
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		$sql[] = "CREATE TABLE {$wpdb->base_prefix}bp_invite_anyone (
	  		    id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    inviter_id bigint(20) NOT NULL,
			    email varchar(75) NOT NULL,
			    message longtext NOT NULL,
			    group_invitations longtext,
			    date_invited datetime NOT NULL,
			    is_joined tinyint(1) NOT NULL,
			    date_joined datetime
			    ) {$charset_collate};";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option( 'bp_invite_anyone_ver', BP_INVITE_ANYONE_VER );

   }
}



function invite_anyone_record_invitation( $inviter_id, $email, $message, $groups ) {
	global $wpdb, $bp;
	
	$group_invitations = maybe_serialize( $groups );
	$date_invited = gmdate( "Y-m-d H:i:s" );
	$is_joined = 0;
	
	$sql = $wpdb->prepare( "INSERT INTO {$bp->invite_anyone->table_name} ( inviter_id, email, message,  group_invitations, date_invited, is_joined ) VALUES ( %d, %s, %s, %s, %s, %d )", $inviter_id, $email, $message, $group_invitations, $date_invited, $is_joined );
			
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;
}

function invite_anyone_get_invitations_by_inviter_id( $id ) {
	global $wpdb, $bp;
		
	$sql = $wpdb->prepare( "SELECT * FROM {$bp->invite_anyone->table_name} WHERE inviter_id = %s", $id );
	
	$results = $wpdb->get_results($sql);
	return $results;	

}

function invite_anyone_get_invitations_by_invited_email( $email ) {
	global $wpdb, $bp;
		
	$sql = $wpdb->prepare( "SELECT * FROM {$bp->invite_anyone->table_name} WHERE email = %s", $email );
	
	$results = $wpdb->get_results($sql);
	return $results;	
}

function invite_anyone_mark_as_joined( $email ) {
	global $wpdb, $bp;
	
	$is_joined = 1;
	$date_joined = gmdate( "Y-m-d H:i:s" );
		
	$sql = $wpdb->prepare( "UPDATE {$bp->invite_anyone->table_name} SET is_joined = %d, date_joined = %s WHERE email = %s", $is_joined, $date_joined, $email ); 
	
	if ( !$wpdb->query($sql) )
		return false;
	
	return true;
}



?>