<?php
	/* Register option */
	function wiki_api_register_setting()
	{
		register_setting('wiki_api_setting_save', 'wiki_api_setting_save');
		register_setting('wiki_api_setting_customfield', 'wiki_api_setting_customfield');
	}
	add_action('admin_init', 'wiki_api_register_setting');


	function np_register_form_option() {
        add_menu_page('Wiki API Setting', __('Wiki API Setting', 'netpower'), 'activate_plugins', 'wiki-api-settings', 'np_setting', '', 66);
		add_submenu_page('wiki-api-settings', __('Setting Custom Field', 'wiki_api_import'), __('Setting Custom Field', 'wiki_api_import'), 'activate_plugins', 'wiki-api-custom-field', 'wiki_custom_field');
	}
	add_action('admin_menu', 'np_register_form_option');

	function np_setting(){
		?>
		<h2>General Settings</h2>
		<div id="delLogNotification" style="display: none"></div>

		<form action="options.php" method="POST">
			<?php echo settings_fields('wiki_api_setting_save');
			$wiki_api_setting_save = get_option('wiki_api_setting_save');
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA API ENDPOINT') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[api_endpoint]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['api_endpoint']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA INFOBOX CLASS') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[infobox_class]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['infobox_class']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA TERM CATEGORY') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_category]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_category']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA TERM BIRTHDAY') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_birthday]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_birthday']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA TERM DEADDAY') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_deadday]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_deadday']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 220px;"><label for="mode"><?php _e('WIKIPEDIA TERM MUNICIPALITY') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_municipality]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_municipality']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA TERM MALE') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_male]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_male']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('WIKIPEDIA TERM FEMALE') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[term_female]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['term_female']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('LAST UPDATED AUTHORS PAGE ID') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[last_updated_author]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['last_updated_author']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('LOG FILE PATH') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[log_file_path]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['log_file_path']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('CRON IMPORT PAGE SIZE') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[import_page_size]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['import_page_size']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('AUTHOR CATEGORY ID') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_save[author_category_id]" id="mode" type="text" value="<?php echo $wiki_api_setting_save['author_category_id']?>">
						</td>
					</tr>
				</tbody>
			</table>
			<button class="button button-primary" id="save_change" >Save</button>
		</form>
<?php
	} // End General Setting page.
	//Start custom field setting page
	function wiki_custom_field(){
?>
		<h2>Custom field Settings</h2>
		<div id="delLogNotification" style="display: none"></div>

		<form action="options.php" method="POST">
			<?php echo settings_fields('wiki_api_setting_customfield');
			$wiki_api_setting_customfield = get_option('wiki_api_setting_customfield');
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD ID THUMBNAIL') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_thumbnail]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_thumbnail']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_PAGE_ID') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_page_id]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_page_id']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_INFOBOX') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_infobox]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_infobox']?>">
						</td>
					</tr>
					
					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_SECTIONS') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_sections]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_sections']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_MUNICIPALITY') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_municipality]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_municipality']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_REVID') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_revid]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_revid']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_BIRTHYEAR') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_birthyear]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_birthyear']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_DEATHYEAR') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_deathyear]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_deathyear']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_GENRE') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_genre]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_genre']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_AUTO_UPDATE') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_auto_update]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_auto_update']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_GENDER') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_gender]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_gender']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_FIRSTNAME') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_firstname]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_firstname']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_LASTNAME') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_lastname]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_lastname']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_BOOK_FIRSTNAME') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_bookfirstname]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_bookfirstname']?>">
						</td>
					</tr>

					<tr>
						<th scope="row" style="width: 250px;"><label for="mode"><?php _e('FIELD_ID_BOOK_LASTNAME') ?></label></th>
						<td scope="row" style="padding-top: 0; vertical-align: bottom">
							<input style="width: 40%" name="wiki_api_setting_customfield[id_booklastname]" id="mode" type="text" value="<?php echo $wiki_api_setting_customfield['id_booklastname']?>">
						</td>
					</tr>
				</tbody>
			</table>
			<button class="button button-primary" id="save_change" >Save</button>
		</form>		
<?php
	} // End custom field Setting page.
?>