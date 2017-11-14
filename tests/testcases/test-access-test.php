<?php

/**
 * @covers ::invite_anyone_access_test()
 */
class Invite_Anyone_Access_Test_Tests extends BP_UnitTestCase {
	static $user_id;

	public static function setUpBeforeClass() {
		$f = new BP_UnitTest_Factory();
		self::$user_id = $f->user->create( array(
			'role' => 'subscriber',
		) );
	}

	public static function tearDownAfterClass() {
		self::delete_user( self::$user_id );
	}

	public function test_anon_user_should_not_have_access() {
		$this->assertSame( 0, bp_loggedin_user_id() );
		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_admin_user_should_have_access() {
		$admin_id = $this->factory->user->create();
		$this->grant_bp_moderate( $admin_id );

		$this->set_current_user( $admin_id );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_user_viewing_others_profile_should_not_have_access() {
		$this->set_current_user( self::$user_id );

		$other_user_id = $this->factory->user->create();
		$this->go_to( bp_core_get_user_domain( $other_user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_user_viewing_own_profile_should_have_access_in_absence_of_other_limits() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'no_limit';
		bp_update_option( 'invite_anyone', $iaoptions );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_email_since_toggle_failure() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'yes';
		$iaoptions['days_since'] = 3;
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$two_days_ago = date( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) );
		$updated = wp_update_user( array(
			'ID' => self::$user_id,
			'user_registered' => $two_days_ago,
		) );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_email_since_toggle_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'yes';
		$iaoptions['days_since'] = 1;
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$two_days_ago = date( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) );
		$updated = wp_update_user( array(
			'ID' => self::$user_id,
			'user_registered' => $two_days_ago,
		) );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_minimum_role_subscriber() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Subscriber';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->remove_role( 'subscriber' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_minimum_role_subscriber_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Subscriber';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_minimum_role_contributor() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Contributor';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->remove_role( 'contributor' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_minimum_role_contributor_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Contributor';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->add_role( 'contributor' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_minimum_role_author() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Author';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->remove_role( 'author' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_minimum_role_author_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Author';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->add_role( 'author' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_minimum_role_editor() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Editor';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->remove_role( 'editor' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_minimum_role_editor_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Editor';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->add_role( 'editor' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_minimum_role_administrator() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Editor';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->remove_role( 'administrator' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_minimum_role_administrator_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'yes';
		$iaoptions['minimum_role'] = 'Administrator';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$user = new WP_User( self::$user_id );
		$user->add_role( 'administrator' );

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}

	public function test_blacklist_failure() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'no';
		$iaoptions['email_blacklist_toggle'] = 'yes';
		$iaoptions['email_blacklist'] = '300,' . self::$user_id . ',400';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertFalse( invite_anyone_access_test() );
	}

	public function test_blacklist_success() {
		$iaoptions = invite_anyone_options();
		$iaoptions['email_visibility_toggle'] = 'limit';
		$iaoptions['email_since_toggle'] = 'no';
		$iaoptions['email_role_toggle'] = 'no';
		$iaoptions['email_blacklist_toggle'] = 'yes';
		$iaoptions['email_blacklist'] = '';
		bp_update_option( 'invite_anyone', $iaoptions );
		$GLOBALS['iaoptions'] = $iaoptions;

		$this->set_current_user( self::$user_id );
		$this->go_to( bp_core_get_user_domain( self::$user_id ) );

		$this->assertTrue( invite_anyone_access_test() );
	}
}
