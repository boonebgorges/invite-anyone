<?php

class Invite_Anyone_User_Query_Tests extends BP_UnitTestCase {
	public function test_search_by_display_name() {
		$u1 = self::factory()->user->create( array( 'display_name' => 'foo' ) );
		$u2 = self::factory()->user->create( array( 'display_name' => 'bar' ) );

		$q = new Invite_Anyone_User_Query( array(
			'search' => 'bar',
		) );

		$this->assertSame( array( $u2 ), wp_list_pluck( $q->results, 'ID' ) );
	}
}
