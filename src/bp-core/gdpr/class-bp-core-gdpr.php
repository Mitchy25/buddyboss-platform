<?php
/**
 * Core component classes.
 *
 * @package BuddyBoss
 * @since BuddyBoss 3.1.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class BP_Core_Gdpr
 */
class BP_Core_Gdpr {

	public function __construct() {
		$this->load_on_bp_dependency();
	}

	/**
	 * Function to load all the dependency of GDPR classes.
	 *
	 * @package BuddyBoss
	 * @since BuddyBoss 3.1.1
	 */
	function load_on_bp_dependency() {

		if ( bp_is_active( "xprofile" ) ) {
			BP_Xprofile_Export::instance();
		}
		if ( bp_is_active( "activity" ) ) {
			BP_Activity_Export::instance();
		}
		if ( bp_is_active( "notifications" ) ) {
			BP_Notification_Export::instance();
		}
		if ( bp_is_active( "messages" ) ) {
			BP_Message_Export::instance();
		}
		if ( bp_is_active( "groups" ) ) {
			BP_Group_Export::instance();
			BP_Group_Membership_Export::instance();
		}
		if ( bp_is_active( 'friends' ) ) {
			BP_Friendship_Export::instance();
		}
		if ( bp_is_active( "settings" ) ) {
			BP_Settings_Export::instance();
		}
		if ( bp_is_active( "forums" ) ) {
			new BP_Bbp_Gdpr_Forums();
			new BP_Bbp_Gdpr_Replies();
			new BP_Bbp_Gdpr_Topics();
		}

	}
}