<?php

class Invite_Anyone_Invitation_Tests extends BP_UnitTestCase {
	public function test_get_with_invitee_email() {
		$u1 = self::factory()->user->create();
		$u2 = self::factory()->user->create();

		$invitation_1 = new Invite_Anyone_Invitation();
		$i1 = $invitation_1->create( array(
			'inviter_id' => $u1,
			'invitee_email' => 'foo@example.com',
			'message' => 'foo',
			'subject' => 'foo',
		) );

		$invitation_2 = new Invite_Anyone_Invitation();
		$i2 = $invitation_2->create( array(
			'inviter_id' => $u2,
			'invitee_email' => 'bar@example.com',
			'message' => 'foo',
			'subject' => 'foo',
		) );

		$i = new Invite_Anyone_Invitation();
		$invitations = $i->get( array(
			'invitee_email' => 'bar@example.com',
		) );

		$found = wp_list_pluck( $invitations->posts, 'ID' );

		$this->assertEqualSets( $found, array( $i2 ) );
	}
}
