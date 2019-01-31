<div class="bboss_ajax_search_item bboss_ajax_search_item_forum">
	<a href="<?php echo esc_url(add_query_arg( array( 'no_frame' => '1' ), bbp_get_forum_permalink( get_the_ID()) )); ?>">
		<div class="item-avatar">
			<img
				src="<?php echo bbp_get_forum_thumbnail_src( get_the_ID() ) ?: buddypress()->plugin_url . "bp-core/images/mystery-forum.png"; ?>"
				class="avatar forum-avatar"
				height="150"
				width="150"
				alt=""
			/>
		</div>
		<div class="item">
			<div class="item-title"><?php bbp_forum_title(get_the_ID()); ?></div>
		</div>
	</a>
</div>