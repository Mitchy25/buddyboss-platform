<?php
/**
 * Functions related to the BuddyBoss Media component and the WP Cache.
 *
 * @package BuddyBoss\Media
 * @since BuddyBoss 1.1.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Clear a cached media item when that item is updated.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param BP_Media $media Media object.
 */
function bp_media_clear_cache_for_media( $media ) {
	wp_cache_delete( $media->id, 'bp_media' );
}
add_action( 'bp_media_after_save', 'bp_media_clear_cache_for_media' );

/**
 * Clear cached data for deleted media item.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param int $deleted_id IDs of deleted media item.
 */
function bp_media_clear_cache_for_deleted_media( $deleted_id ) {
	wp_cache_delete( $deleted_id, 'bp_media' );
}
add_action( 'bp_media_delete', 'bp_media_clear_cache_for_deleted_media' );

/**
 * Reset cache incrementor for the Media component.
 *
 * Called whenever an media item is created, updated, or deleted, this
 * function effectively invalidates all cached results of media queries.
 *
 * @since BuddyBoss 1.1.5
 *
 * @return bool True on success, false on failure.
 */
function bp_media_reset_cache_incrementor() {
	return bp_core_reset_incrementor( 'bp_media' );
}
add_action( 'bp_media_delete',    'bp_media_reset_cache_incrementor' );
add_action( 'bp_media_add',       'bp_media_reset_cache_incrementor' );

/**
 * Clear a user's cached media count.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param array $r  Media add arguments.
 */
function bp_media_clear_media_user_object_cache( $r ) {

	if ( ! empty( $r[0] ) && is_object( $r[0] ) ) {
		$user_id = ! empty( $r[0]->user_id ) ? $r[0]->user_id : false;
	} else {
		$user_id = ! empty( $r['user_id'] ) ? $r['user_id'] : false;
	}

	if ( $user_id ) {
		wp_cache_delete( 'bp_total_media_for_user_' . $user_id, 'bp' );
	}
}
add_action( 'bp_media_add',             'bp_media_clear_media_user_object_cache', 10 );
add_action( 'bp_media_after_delete',    'bp_media_clear_media_user_object_cache', 10 );

/**
 * Clear a group's cached media count.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param array $r  Media add arguments.
 */
function bp_media_clear_media_group_object_cache( $r ) {
	if ( ! empty( $r[0] ) && is_object( $r[0] ) ) {
		$group_id = ! empty( $r[0]->group_id ) ? $r[0]->group_id : false;
	} else {
		$group_id = ! empty( $r['group_id'] ) ? $r['group_id'] : false;
	}

	if ( $group_id ) {
		wp_cache_delete( 'bp_total_media_for_group_' . $group_id, 'bp' );
	}
}
add_action( 'bp_media_add',             'bp_media_clear_media_group_object_cache', 10 );
add_action( 'bp_media_after_delete',    'bp_media_clear_media_group_object_cache', 10 );

/**
 * Clear a cached album item when that item is updated.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param BP_Media_Album $album Album object.
 */
function bp_media_clear_cache_for_album( $album ) {
	wp_cache_delete( $album->id, 'bp_media_album' );
}
add_action( 'bp_media_album_after_save', 'bp_media_clear_cache_for_album' );

/**
 * Clear cached data for deleted album item.
 *
 * @since BuddyBoss 1.1.5
 *
 * @param int $deleted_id IDs of deleted album item.
 */
function bp_media_clear_cache_for_deleted_album( $deleted_id ) {
	wp_cache_delete( $deleted_id, 'bp_media_album' );
}
add_action( 'bp_album_delete', 'bp_media_clear_cache_for_deleted_album' );

/**
 * Reset cache incrementor for the Album.
 *
 * Called whenever an album item is created, updated, or deleted, this
 * function effectively invalidates all cached results of album queries.
 *
 * @since BuddyBoss 1.1.5
 *
 * @return bool True on success, false on failure.
 */
function bp_media_album_reset_cache_incrementor() {
	return bp_core_reset_incrementor( 'bp_media_album' );
}
add_action( 'bp_album_delete',    'bp_media_album_reset_cache_incrementor' );
add_action( 'bp_album_add',       'bp_media_album_reset_cache_incrementor' );