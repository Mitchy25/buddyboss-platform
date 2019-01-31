<li class="bboss_search_item bboss_search_item_reply">
	<div class="list-wrap">
		<div class="item-avatar">
			<a href="<?php bbp_reply_url( get_the_ID() ); ?>">
				<img
					src="<?php echo bbp_get_forum_thumbnail_src( bbp_get_forum_id( get_the_ID() ) ) ?: buddypress()->plugin_url . "bp-core/images/mystery-forum.png"; ?>"
					class="avatar forum-avatar"
					height="150"
					width="150"
					alt=""
				/>
			</a>
		</div>

		<div class="item">
			<a href="<?php bbp_reply_url(get_the_ID()); ?>">
				<div class="item">
					<div class="item-desc"><?php echo buddyboss_global_search_reply_intro( 100 );?></div>
				</div>
			</a>
		</div>
	</div>
</li>