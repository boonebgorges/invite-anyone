<?php

class Invite_Anyone_Tests extends BP_UnitTestCase {

	/**
	 * @group invite_anyone_group_invite_access_test
	 */
	public function test_group_access_test_no_group() {
		$this->assertSame( 'noone', invite_anyone_group_invite_access_test() );
	}

	/**
	 * @group invite_anyone_group_invite_access_test
	 */
	public function test_group_access_test_no_group_during_group_creation() {
		$cc = bp_current_component();
		$ca = bp_current_action();
		buddypress()->current_component = buddypress()->groups->id;
		buddypress()->current_action = 'create';

		$u = $this->create_user();
		$this->assertSame( 'anyone', invite_anyone_group_invite_access_test( 0, $u ) );

		buddypress()->current_component = $cc;
		buddypress()->current_action = $ca;
	}

	/**
	 * @group invite_anyone_group_invite_access_test
	 */
	public function test_group_access_test_logged_out() {
		$old_current_user = get_current_user_id();
		$this->set_current_user( 0 );

		$g = $this->factory->group->create();

		$this->assertSame( 'noone', invite_anyone_group_invite_access_test( $g ) );

		$this->set_current_user( $old_current_user );
	}
}

