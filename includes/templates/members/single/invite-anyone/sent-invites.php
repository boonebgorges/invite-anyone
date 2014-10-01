<?php 
global $bp;

// Load the pagination helper
if ( !class_exists( 'BBG_CPT_Pag' ) )
	require_once( BP_INVITE_ANYONE_DIR . 'lib/bbg-cpt-pag.php' );
$pagination = new BBG_CPT_Pag;

$inviter_id = bp_loggedin_user_id();

if ( isset( $_GET['sort_by'] ) )
	$sort_by = $_GET['sort_by'];
else
	$sort_by = 'date_invited';

if ( isset( $_GET['order'] ) )
	$order = $_GET['order'];
else
	$order = 'DESC';

$base_url = $bp->displayed_user->domain . $bp->invite_anyone->slug . '/sent-invites/';

?>

<h4><?php _e( 'Sent Invites', 'bp-invite-anyone' ); ?></h4>

<?php $invites = invite_anyone_get_invitations_by_inviter_id( bp_loggedin_user_id(), $sort_by, $order, $pagination->get_per_page, $pagination->get_paged ) ?>

<?php $pagination->setup_query( $invites ) ?>

<?php if ( $invites->have_posts() ) : ?>
	<p id="sent-invites-intro"><?php _e( 'You have sent invitations to the following people.', 'bp-invite-anyone' ) ?></p>

	<div class="ia-pagination">
		<div class="currently-viewing">
			<?php $pagination->currently_viewing_text() ?>
		</div>

		<div class="pag-links">
			<?php $pagination->paginate_links() ?>
		</div>
	</div>

	<table class="invite-anyone-sent-invites zebra"
	summary="<?php _e( 'This table displays a list of all your sent invites.
	Invites that have been accepted are highlighted in the listings.
	You may clear any individual invites, all accepted invites or all of the invites from the list.', 'bp-invite-anyone' ) ?>">
		<thead>
			<tr>
			  <th scope="col"></th>
			  <th scope="col" <?php if ( $sort_by == 'email' ) : ?>class="sort-by-me"<?php endif ?>><a class="<?php echo $order ?>" title="Sort column order <?php echo $order ?>" href="<?php echo $base_url ?>?sort_by=email&amp;order=<?php if ( $sort_by == 'email' && $order == 'ASC' ) : ?>DESC<?php else : ?>ASC<?php endif; ?>"><?php _e( 'Invited email address', 'bp-invite-anyone' ) ?></a></th>
			  <th scope="col"><?php _e( 'Group invitations', 'bp-invite-anyone' ) ?></th>
			  <th scope="col" <?php if ( $sort_by == 'date_invited' ) : ?>class="sort-by-me"<?php endif ?>><a class="<?php echo $order ?>" title="Sort column order <?php echo $order ?>" href="<?php echo $base_url ?>?sort_by=date_invited&amp;order=<?php if ( $sort_by == 'date_invited' && $order == 'DESC' ) : ?>ASC<?php else : ?>DESC<?php endif; ?>"><?php _e( 'Sent', 'bp-invite-anyone' ) ?></a></th>
			  <th scope="col" <?php if ( $sort_by == 'date_joined' ) : ?>class="sort-by-me"<?php endif ?>><a class="<?php echo $order ?>" title="Sort column order <?php echo $order ?>" href="<?php echo $base_url ?>?sort_by=date_joined&amp;order=<?php if ( $order == 'DESC' ) : ?>ASC<?php else : ?>DESC<?php endif; ?>"><?php _e( 'Accepted', 'bp-invite-anyone' ) ?></a></th>
			</tr>
		</thead>

		<tfoot>
		<tr id="batch-clear">
		  <td colspan="5" >
		   <ul id="invite-anyone-clear-links">
		      <li> <a title="<?php _e( 'Clear all accepted invites from the list', 'bp-invite-anyone' ) ?>" class="confirm" href="<?php echo wp_nonce_url( $base_url . '?clear=accepted', 'invite_anyone_clear' ) ?>"><?php _e( 'Clear all accepted invitations', 'bp-invite-anyone' ) ?></a></li>
		      <li class="last"><a title="<?php _e( 'Clear all your listed invites', 'bp-invite-anyone' ) ?>" class="confirm" href="<?php echo wp_nonce_url( $base_url . '?clear=all', 'invite_anyone_clear' ) ?>"><?php _e( 'Clear all invitations', 'bp-invite-anyone' ) ?></a></li>
		  </ul>
		 </td>
		</tr>
		</tfoot>

		<tbody>
		<?php while ( $invites->have_posts() ) : $invites->the_post() ?>

		<?php
			$emails = wp_get_post_terms( get_the_ID(), invite_anyone_get_invitee_tax_name() );

			// Should never happen, but was messing up my test env
			if ( empty( $emails ) ) {
				continue;
			}

			// Before storing taxonomy terms in the db, we replaced "+" with ".PLUSSIGN.", so we need to reverse that before displaying the email address.
			$email	= str_replace( '.PLUSSIGN.', '+', $emails[0]->name );

			$post_id = get_the_ID();

			$query_string = preg_replace( "|clear=[0-9]+|", '', $_SERVER['QUERY_STRING'] );

			$clear_url = ( $query_string ) ? $base_url . '?' . $query_string . '&clear=' . $post_id : $base_url . '?clear=' . $post_id;
			$clear_url = wp_nonce_url( $clear_url, 'invite_anyone_clear' );
			$clear_link = '<a class="clear-entry confirm" title="' . __( 'Clear this invitation', 'bp-invite-anyone' ) . '" href="' . $clear_url . '">x<span></span></a>';

			$groups = wp_get_post_terms( get_the_ID(), invite_anyone_get_invited_groups_tax_name() );
			if ( !empty( $groups ) ) {
				$group_names = '<ul>';
				foreach( $groups as $group_term ) {
					$group = new BP_Groups_Group( $group_term->name );
					$group_names .= '<li>' . bp_get_group_name( $group ) . '</li>';
				}
				$group_names .= '</ul>';
			} else {
				$group_names = '-';
			}

			global $post;

			$date_invited = invite_anyone_format_date( $post->post_date );

			$accepted = get_post_meta( get_the_ID(), 'bp_ia_accepted', true );

			if ( $accepted ):
				$date_joined = invite_anyone_format_date( $accepted );
				$accepted = true;
			else:
				$date_joined = '-';
				$accepted = false;
			endif;

			?>

			<tr <?php if($accepted){ ?> class="accepted" <?php } ?>>
				<td><?php echo $clear_link ?></td>
				<td><?php echo esc_html( $email ) ?></td>
				<td><?php echo $group_names ?></td>
				<td><?php echo $date_invited ?></td>
				<td class="date-joined"><span></span><?php echo $date_joined ?></td>
			</tr>
		<?php endwhile ?>
	 </tbody>
	</table>

	<div class="ia-pagination">
		<div class="currently-viewing">
			<?php $pagination->currently_viewing_text() ?>
		</div>

		<div class="pag-links">
			<?php $pagination->paginate_links() ?>
		</div>
	</div>


<?php else : ?>

<p id="sent-invites-intro"><?php _e( "You haven't sent any email invitations yet.", 'bp-invite-anyone' ) ?></p>

<?php endif; ?>