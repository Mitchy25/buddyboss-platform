<?php

add_action( 'current_screen', 'bp_profile_search_redirect_admin_screens' );
function bp_profile_search_redirect_admin_screens () {
    $current_screen = get_current_screen();
    if ( 'edit-bp_ps_form' == $current_screen->id ) {
        $main_form = bp_profile_search_main_form();
        //create a form if not created already
        if ( !$main_form ) {
            bp_profile_search_add_main_form();
            $main_form = bp_profile_search_main_form();
        }
        
        //redirect to edit that form
        if ( $main_form ) {
            wp_redirect( admin_url( 'post.php?post=' . $main_form . '&action=edit' ) );
            die();
        }
    }
}

add_action ('add_meta_boxes', 'bp_ps_add_meta_boxes');
function bp_ps_add_meta_boxes () {
	add_meta_box ('bp_ps_fields_box', __('Form Fields', 'buddyboss'), 'bp_ps_fields_box', 'bp_ps_form', 'normal');
}

add_action( 'post_submitbox_start', 'bp_ps_edit_form_preview_button' );
function bp_ps_edit_form_preview_button ( $post ) {
    if ( $post->post_type == 'bp_ps_form' ) {
        $members_directory = trailingslashit( bp_get_root_domain() . '/' . bp_get_members_root_slug() );
        echo "<a href='". esc_attr( $members_directory ) ."' class='button button-secondary' target='_blank'>". __( 'Preview', 'buddyboss' ) ."</a>";
    }
}

function bp_ps_fields_box ( $post ) {
	$bp_ps_options = bp_ps_meta ( $post->ID );

	list ($groups, $fields) = bp_ps_get_fields ();
	echo '<script>var bp_ps_groups = ['. json_encode ($groups). '];</script>';
?>

	<div id="field_box" class="field_box">
		<p>
			<span class="bp_ps_col1"></span>
			<span class="bp_ps_col2"><strong>&nbsp;<?php _e('Field', 'buddyboss'); ?></strong></span>&nbsp;
			<span class="bp_ps_col3"><strong>&nbsp;<?php _e('Label', 'buddyboss'); ?></strong></span>&nbsp;
			<span class="bp_ps_col4"><strong>&nbsp;<?php _e('Description', 'buddyboss'); ?></strong></span>&nbsp;
			<span class="bp_ps_col5"><strong>&nbsp;<?php _e('Search Mode', 'buddyboss'); ?></strong></span>
		</p>
<?php

	foreach ($bp_ps_options['field_code'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$field = $fields[$id];
		$label = esc_attr ($bp_ps_options['field_label'][$k]);
		$default = esc_attr ($field->name);
		$showlabel = empty ($label)? "placeholder=\"$default\"": "value=\"$label\"";
		$desc = esc_attr ($bp_ps_options['field_desc'][$k]);
		$default = esc_attr ($field->description);
		$showdesc = empty ($desc)? "placeholder=\"$default\"": "value=\"$desc\"";
?>

		<div id="field_div<?php echo $k; ?>" class="sortable">
			<span class="bp_ps_col1" title="<?php _e('drag to reorder fields', 'buddyboss'); ?>">&nbsp;&#x21C5;</span>
<?php
			_bp_ps_field_select ($groups, "bp_ps_options[field_name][$k]", "field_name$k", $id);
?>
			<input class="bp_ps_col3" type="text" name="bp_ps_options[field_label][<?php echo $k; ?>]" id="field_label<?php echo $k; ?>" <?php echo $showlabel; ?> />
			<input class="bp_ps_col4" type="text" name="bp_ps_options[field_desc][<?php echo $k; ?>]" id="field_desc<?php echo $k; ?>" <?php echo $showdesc; ?> />
<?php
			_bp_ps_filter_select ($field, "bp_ps_options[field_mode][$k]", "field_mode$k", $bp_ps_options['field_mode'][$k]);
?>
			<a href="javascript:remove('field_div<?php echo $k; ?>')" class="delete"><?php _e('Remove', 'buddyboss'); ?></a>
		</div>
<?php
	}
?>
	</div>
	<input type="hidden" id="field_next" value="<?php echo count ($bp_ps_options['field_code']); ?>" />
	<p><a href="javascript:add_field()"><?php _e('Add Field', 'buddyboss'); ?></a></p>
<?php
}

function _bp_ps_field_select ($groups, $name, $id, $value)
{
	echo "<select class='bp_ps_col2' name='$name' id='$id'>\n";
	foreach ($groups as $group => $fields)
	{
		$group = esc_attr ($group);
		echo "<optgroup label='$group'>\n";
		foreach ($fields as $field)
		{
			$selected = $field['id'] == $value? " selected='selected'": '';
			echo "<option value='$field[id]'$selected>$field[name]</option>\n";
		}
		echo "</optgroup>\n";
	}
	echo "</select>\n";
}

function _bp_ps_filter_select ($f, $name, $id, $value)
{
	$filters = bp_ps_Fields::get_filters ($f);

	echo "<select class='bp_ps_col5' name='$name' id='$id'>\n";
	foreach ($filters as $key => $label)
	{
		$selected = $value == $key? " selected='selected'": '';
		echo "<option value='$key'$selected>$label</option>\n";
	}
	echo "</select>\n";
}

add_action ('save_post', 'bp_ps_update_meta', 10, 2);
function bp_ps_update_meta ($form, $post)
{
	if ($post->post_type != 'bp_ps_form' || $post->post_status != 'publish')  return false;
	if (empty ($_POST['options']) && empty ($_POST['bp_ps_options']))  return false;

	$old_meta = bp_ps_meta ($form);

	$meta = array ();
	$meta['field_code'] = array ();
	$meta['field_label'] = array ();
	$meta['field_desc'] = array ();
	$meta['field_mode'] = array ();

	list ($x, $fields) = bp_ps_get_fields ();

	$codes = array ();
	$posted = isset ($_POST['bp_ps_options'])? $_POST['bp_ps_options']: array ();
	if (isset ($posted['field_name']))  foreach ($posted['field_name'] as $k => $code)
	{
		if (empty ($fields[$code]))  continue;
		if (in_array ($code, $codes))  continue;

		$codes[] = $code;
		$meta['field_code'][] = $code;
		$meta['field_label'][] = isset ($posted['field_label'][$k])? stripslashes ($posted['field_label'][$k]): '';
		$meta['field_desc'][] = isset ($posted['field_desc'][$k])? stripslashes ($posted['field_desc'][$k]): '';
		$meta['field_mode'][] = bp_ps_Fields::valid_filter ($fields[$code], isset ($posted['field_mode'][$k])? $posted['field_mode'][$k]: 'none');

	}

	update_post_meta ($form, 'bp_ps_options', $meta);

	return true;
}

function bp_ps_set_option ($name, $value){
	$settings = get_option ('bp_ps_settings');
	if ($settings === false)
		$settings = new stdClass;

	$settings->{$name} = $value;
	update_option ('bp_ps_settings', $settings);
}

function bp_ps_get_option ($name, $default)
{
	$settings = get_option ('bp_ps_settings');
	return isset ($settings->{$name})? $settings->{$name}: $default;
}