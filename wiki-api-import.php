<?php
/**
 * Plugin Name: Wiki API Import
 * Plugin URI: netpower.no
 * Description: This plugin supports to import available data of Wikipedia into wordpress single posts/pages.
 * Version: 1.0.0
 * Author: NetPower
 * Author URI:  netpower.no
 * License: Netpower
 */

/**
 * Add menu and submenu
 * @return void
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function wiki_api_import_css() {
	wp_enqueue_style('style-name', get_stylesheet_uri());
	wp_enqueue_script('script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true);
}

include 'icl/setting.php';

$wiki_api_setting = get_option('wiki_api_setting_save');
$wiki_api_setting_customfield = get_option('wiki_api_setting_customfield');


define(WIKIPEDIA_API_ENDPOINT, $wiki_api_setting['api_endpoint']);
define(WIKIPEDIA_INFOBOX_CLASS, $wiki_api_setting['infobox_class']);
define(WIKIPEDIA_TERM_CATEGORY, $wiki_api_setting['term_category']);
define(WIKIPEDIA_TERM_BIRTHDAY, $wiki_api_setting['term_birthday']);
define(WIKIPEDIA_TERM_DEADDAY, $wiki_api_setting['term_deadday']);
define(WIKIPEDIA_TERM_MUNICIPALITY, $wiki_api_setting['term_municipality']);
define(WIKIPEDIA_TERM_MALE, $wiki_api_setting['term_male']);
define(WIKIPEDIA_TERM_FEMALE, $wiki_api_setting['term_female']);
define(LAST_UPDATED_AUTHORS_PAGE_ID, $wiki_api_setting['last_updated_author']);


define(LOG_FILE_PATH, $wiki_api_setting['log_file_path']);
define(CRON_IMPORT_PAGE_SIZE, $wiki_api_setting['import_page_size']);
define(AUTHOR_CATEGORY_ID, $wiki_api_setting['author_category_id']);

define(FIELD_ID_THUMBNAIL, $wiki_api_setting['id_thumbnail']);
define(FIELD_ID_PAGE_ID, $wiki_api_setting['id_page_id']);
define(FIELD_ID_INFOBOX, $wiki_api_setting['id_infobox']);
define(FIELD_ID_SECTIONS, $wiki_api_setting['id_sections']);
define(FIELD_ID_MUNICIPALITY, $wiki_api_setting['id_municipality']);
define(FIELD_ID_REVID, $wiki_api_setting['id_revid']);
define(FIELD_ID_BIRTHYEAR, $wiki_api_setting['id_birthyear']);
define(FIELD_ID_DEATHYEAR, $wiki_api_setting['id_deathyear']);
define(FIELD_ID_GENRE, $wiki_api_setting['id_genre']);
define(FIELD_ID_FIRSTNAME, $wiki_api_setting['id_firstname']);
define(FIELD_ID_LASTNAME, $wiki_api_setting['id_lastname']);
define(FIELD_ID_BOOK_FIRSTNAME, $wiki_api_setting['id_bookfirstname']);
define(FIELD_ID_BOOK_LASTNAME, $wiki_api_setting['id_booklastname']);


if (!function_exists(wiki_api_admin_default_setup)) {
	function wiki_api_admin_default_setup() {
		add_options_page(__('Wiki API Import', 'wiki_api_import'), __('Wiki API Import', 'wiki_api_import'), 'manage_options', 'wiki_api_import_default_form', 'wiki_api_import_default_form');
	}
}

/**
 * Function to add plugin scripts
 * @return void
 */
if (!function_exists(wiki_api_register_script)) {
	function wiki_api_register_script() {
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wiki_api_import_default_form') {
			wp_enqueue_script('wiki_api_script', plugins_url('js/script.js', __FILE__), array('jquery'));
		}
	}
}

/**
 * Function to add plugin css
 * @return void
 */
if (!function_exists(wiki_api_register_css)) {
	function wiki_api_register_css() {
		if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wiki_api_import_default_form') {
			wp_enqueue_style('wiki_api_style', plugins_url('css/style.css', __FILE__));
		}
	}
}

/**
 * Generate complete wiki api endpoint uri
 * @param $wiki_host : the root url of wiki
 * @param $wiki_folder :
 * @param $api_name :
 * @param $key_word :
 * @param $filter_type :
 * @param $filter_option :
 * @return text or errors
 */
if (!function_exists(wiki_api_endpoint_uri_generate)) {
	function wiki_api_endpoint_uri_generate($wiki_host, $wiki_folder, $api_name, $key_word, $filter_type, $filter_option) {
		$errors            = '';
		$wiki_api_options  = get_option('wiki_api_options');
		$wiki_host_default = $wiki_api_options["wiki_host"];
		if ($wiki_host_default != $wiki_host) {
			$wiki_host_default = $wiki_host;
		}

		$wiki_api_full_uri = $wiki_host_default;
		if (!empty($wiki_folder)) {
			$wiki_api_full_uri = $wiki_api_full_uri . '/' . $wiki_folder;
		}

		if (!empty($api_name)) {
			$wiki_api_full_uri = $wiki_api_full_uri . '/' . $api_name . '?action=query';
		}

		if (!empty($key_word)) {
			$wiki_api_full_uri = $wiki_api_full_uri . '&titles=' . $key_word;
		}

		if (!empty($filter_type) && !empty($filter_option)) {
			// $filter_type could be 'list', 'prop', or 'meta'.
			$wiki_api_full_uri = $wiki_api_full_uri . '&' . $filter_type . '=' . $filter_option;
		}

		$wiki_api_full_uri = $wiki_api_full_uri . '&format=json';
		return $wiki_api_full_uri;
	}
}

/**
 * Function to init the wiki search form for importing
 * @return void
 */
if (!function_exists(wiki_api_import_default_form)) {
	function wiki_api_import_default_form() {
		do_action('wiki_api_enqueue_script');
		do_action('wiki_api_enqueue_css');
		$wiki_api_uri = WIKIPEDIA_API_ENDPOINT;

		?>
		<div class="wiki-api-search-form wrapper">
			<div class="wiki-api-search-title">
				<h1><?php _e("Import from Wikipedia : ", 'wiki_api_import')?></h1>
			</div>
			<table class="form-table" id="wikiSearchForm">
				<tbody>
					<tr>
						<th scope="row"><?php _e("Search : ", 'wiki_api_import')?></td>
						<td><input type="text" class="regular-text" id="wikiApiSearchText" /></td>
					</tr>
					<tr class="hidden" id="wikiAdvanceSearchField">
						<td>
							<select name="" id="wikiAdvanceSearchCondition" class="wiki-advance-search-selectbox">
								<option value="And">And</option>
								<option value="Or">Or</option>
								<option value="Not in">Not in</option>
							</select>
						</td>
						<td><input type="text" class="regular-text" id="wikiApiSearchExtraText" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e("Search Type: ", 'wiki_api_import')?></td>
						<td>
								<input id="search-type-name" checked class="search-type" type="checkbox" name="search-type[]" /> <?php _e("Name", 'wiki_api_import')?>
								<input id="search-type-category" class="search-type" type="checkbox" name="search-type[]" /> <?php _e("Category", 'wiki_api_import')?>
								<!-- <input id="search-type-sub-categories" class="search-type" type="checkbox" name="search-type[]" /> <?php _e("Sub categories", 'wiki_api_import')?>	 -->
								<input type="button" class="button wiki-advance-search-button" id="wiki-api-import-advance-search" value="<?php _e("Advance search", 'wiki_api_import')?>"/>
						</td>
					</tr>
					<tr >
						<td colspan="2">
								<input  class="button button-primary"  type="button" id="wiki-api-search-button" value="<?php _e("Search", 'wiki_api_import')?>" wiki-api-uri="<?php _e($wiki_api_uri, 'wiki_api_import');?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div class="card  wiki-api-search-results" style="height: 300px;    overflow: scroll;">
								<!-- Fetch data results in here -->
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="wiki-api-import-action">
							<input type="button" class="button button-primary" id="wiki-api-import-select-all" value="<?php _e("Select All", 'wiki_api_import')?>"/>
							<input type="button"  class="button button-primary"  id="wiki-api-import-button" value="<?php _e("Import", 'wiki_api_import')?>"/>
							<span class="import-loading hidden" id="importLoading"><img src="/wp-admin/images/wpspin_light.gif" alt="loading"><span></span></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
}
}

if (!function_exists(wiki_api_save_file)) {
	function wiki_api_save_file($name, $filename, $url) {

		$uploaddir  = wp_upload_dir();
		$uploadfile = $uploaddir['path'] . '/' . basename($filename);

		echo 'Upload File to: ' . $uploadfile . "\r\n";

		$contents = file_get_contents($url);
		$savefile = fopen($uploadfile, 'w');
		fwrite($savefile, $contents);
		fclose($savefile);

		$img_title = preg_replace('/\.[^.]+$/', '', $name);
		$wp_filetype = wp_check_filetype(basename($filename), null);

		$attachment = array(
			'guid'           => $uploaddir['url'] . '/' . basename($filename),
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => $img_title ,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id = wp_insert_attachment($attachment, $uploadfile);

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$imagenew = get_post($attach_id);

		$fullsizepath = get_attached_file($imagenew->ID);
		$attach_data  = wp_generate_attachment_metadata($attach_id, $fullsizepath);
		wp_update_attachment_metadata($attach_id, $attach_data);

		return $attach_id;
	}
}

if (!function_exists(wiki_api_import)) {
	function wiki_api_import($request_data, $force_update = true) {
		$queryPageIds      = array();
		$result            = array();
		$result['success'] = array();
		$result['fail']    = array();
		$wikiCategoryGroup = array();
		$list_id 			= array();
		foreach ($request_data as $pageId => $page) {
			if (!$page) {
				continue;
			}

			$query                = array();
			$query['action']      = 'query';
			$query['prop']        = 'categories|categoryinfo|extracts|links|pageimages|imageinfo|categorieshtml';
			$query['pithumbsize'] = 500;
			$query['format']      = 'json';
			$query['pageids']     = $page;
			$query['clshow']	  = '!hidden'; //increases the limit of categoires to 50
			$query['cllimit']	  = 50; //increases the limit of categoires to 50
			$query['pllimit']	  = 500; //increases the limit of links to show to 500

			$resultAPI = wiki_api_curl('GET', $query);

			$data = json_decode($resultAPI, true);

			if (empty($data)) {
				$result['fail'][] = "{$page}. Can not parse data";
				continue;
			}
			if (!isset($data['query'])) {
				$result['fail'][] = "{$page}. Query is empty";
				continue;
			}

			$i = 1;
			if (!isset($data['query']['pages'])) {
				$result['fail'][] = "{$page}. Query Page is empty";
				continue;
			}

			$wikiCategoryGroup = getWikiCategory();

			foreach ($data['query']['pages'] as $pageId => $pageContent) {				
				$isNewImport = false;
				$postTitle  = sanitize_text_field($pageContent['title']);
				$pageid     = $pageId;
				$categories = $pageContent['categories'];
				$thumbnail  = null;
				$pageimage  = null;
				$queryPosts = get_posts(array(
					'numberposts' => -1,
					'post_type'   => 'post',
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'     => 'page_id',
							'value'   => $pageid,
							'compare' => '=',
						),
					),
				));

				// Don't update when Auto update  is false
				if (isset($queryPosts[0]) && !get_field(FIELD_ID_AUTO_UPDATE, $queryPosts[0]->ID) && !$force_update) {
					continue;
				}

				if (isset($pageContent['thumbnail'])) {
					$thumbnail = $pageContent['thumbnail'];
					$pageimage = $pageContent['pageimage'];
				}

				$query                = array();
				$query['action']      = 'parse';
				$query['prop']        = 'sections|parsetree|revid|text|categories|categorieshtml';
				$query['format']      = 'json';
				$query['pageids']     = implode("|", $queryPageIds);
				$query['clshow']	  = '!hidden'; //increases the limit of categoires to 50
				$query['cllimit']	  = 50; //increases the limit of categoires to 50
				$query['pllimit']	  = 500; //increases the limit of links to show to 500 
				$query['pageid']      = $pageid;
				$query['generatexml'] = '';
				$parseResult          = wiki_api_curl('GET', $query);
				$parseData            = json_decode($parseResult, true);
				
				if(isset($_GET["test"])){
					print_r($parseData);
				}
				// Do Import To Wordpress Here

				$post = array(
					'post_title'     => $postTitle,
					'post_status'    => 'publish',
					'post_author'    => 1,
					'comment_status' => 'open',
				);
				if (count($queryPosts) > 0) {
					$postData              = $queryPosts[0];
					$post_id               = $postData->ID;
					$post['ID']            = $post_id;
					$revid                 = get_field(FIELD_ID_REVID, $post_id);
					$post['post_category'] = wp_get_post_categories($postData->ID);
					$post['post_date']	   = get_the_date("Y-m-d H:i:s",$postData->ID);
					// update
				} else {
					$post['post_category'] = [AUTHOR_CATEGORY_ID];
					$revid                 = -1;
					// new
					$isNewImport = true;
				}
				if (!empty($parseData) && isset($parseData['parse']) && ($force_update || $revid != $parseData['parse']['revid'])) {
	
					$description = $parseData['parse']['text']["*"];
					if (!is_null($description)) {
						$descriptionDOM = new DOMDocument();
						$descriptionDOM->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES'));
						$xpath           = new DomXPath($descriptionDOM);
						$infobox_results = $xpath->query("//table[contains(@class, '" . WIKIPEDIA_INFOBOX_CLASS . "')]");
						if ($infobox = $infobox_results->item(0)) {
							//remove the node the same way
							$infobox->parentNode->removeChild($infobox);
						}
						$dl_results = $xpath->query("//dl[position()=1]");
						if ($dl = $dl_results->item(0)) {
							if ($dl->getLineNo() == 1) {
								//only remove dl tag if it's the first element on the dom after remove infobox
								//remove the node the same way
								$dl->parentNode->removeChild($dl);
							}
						}
						$toc_results = $xpath->query("//div[contains(@class, 'toc')]");
						if ($toc = $toc_results->item(0)) {
							//remove the node the same way
							$toc->parentNode->removeChild($toc);
						}
						$navboks_results = $xpath->query("//table[contains(@class, 'navboks')]");
						if ($navboks = $navboks_results->item(0)) {
							//remove the node the same way
							$navboks->parentNode->removeChild($navboks);
						}
						/*
						$thumb_results = $xpath->query("//div[contains(@class, 'thumb')]");
						if ($thumb = $thumb_results->item(0)) {
							//remove the node the same way
							$thumb->parentNode->removeChild($thumb);
						}
						*/
						$editsection_results = $xpath->query("//span[contains(@class, 'mw-editsection')]");
						foreach ($editsection_results as $editsection) {
							$editsection->parentNode->removeChild($editsection);
						}
						$wikiLinks_results = $xpath->query("//a[contains(@href, 'wiki') and not(contains(@href, 'nn.wikipedia')) and not(contains(@href, 'no.wikipedia')) ]");
						foreach ($wikiLinks_results as $wikiLink) {
							while ($wikiLink->hasChildNodes()) {
								$child = $wikiLink->removeChild($wikiLink->firstChild);
								$wikiLink->parentNode->insertBefore($child, $wikiLink);
							}
							$wikiLink->parentNode->removeChild($wikiLink);
						}
						$wikiLinks_results = $xpath->query("//a[contains(@href, '/w/')]");
						foreach ($wikiLinks_results as $wikiLink) {
							while ($wikiLink->hasChildNodes()) {
								$child = $wikiLink->removeChild($wikiLink->firstChild);
								$wikiLink->parentNode->insertBefore($child, $wikiLink);
							}
							$wikiLink->parentNode->removeChild($wikiLink);
						}
						$bodyContent          = $descriptionDOM->documentElement->lastChild;
						$post['post_content'] = $descriptionDOM->saveHTML($bodyContent);

						//Save excerpt for post
						$anchorLinks_results = $xpath->query("//a[contains(@href, '#')]");
						foreach ($anchorLinks_results as $anchorLink) {
							$anchorLink->parentNode->removeChild($anchorLink);
						}

						$rightClass_results = $xpath->query("//div[contains(@class, 'tright')]");
						foreach ($rightClass_results as $rightClass) {
							$rightClass->parentNode->removeChild($rightClass);
						}

						$postExcerpt          = strip_tags($descriptionDOM->saveHTML());
						$postExcerpt          = explode("Bibliografi", $postExcerpt)[0];
						$postExcerpt          = html_entity_decode($postExcerpt);
						$postExcerpt          = str_replace("\n", " ", $postExcerpt);
						$postExcerpt          = (strlen($postExcerpt) > 350) ? mb_substr($postExcerpt, 0, 350) . " [...]" : $postExcerpt;
						$post['post_excerpt'] = $postExcerpt;
					}

					$post_id = wp_insert_post($post, $wp_error = true);
					// Update Custom fields
					update_field(FIELD_ID_PAGE_ID, $pageid, $post_id);
					update_field(FIELD_ID_REVID, $parseData['parse']['revid'], $post_id);

					//Update FIRSTNAME and LASTNAME field from postTitle
					$currentFirstName = get_field("first_name", $post_id);
					$currentLastName  = get_field("last_name", $post_id);
					
					$currentBookFirstName = get_field(FIELD_ID_BOOK_FIRSTNAME, $post_id);
					$currentBookLastName  = get_field(FIELD_ID_BOOK_LASTNAME, $post_id);
					
					if ($currentFirstName == "" && $currentLastName == "") {
						$titleArray = explode(" ", $postTitle);
						update_field(FIELD_ID_LASTNAME, end($titleArray), $post_id); // last name
						if(!$currentBookLastName){
							update_field(FIELD_ID_BOOK_LASTNAME, end($titleArray), $post_id); // book last name
						}
						
						if (count($titleArray) > 1) {
							array_pop($titleArray); //remove last name
							update_field(FIELD_ID_FIRSTNAME, implode(" ", $titleArray), $post_id); // first name
							if(!$currentBookFirstName){
								update_field(FIELD_ID_BOOK_FIRSTNAME, implode(" ", $titleArray), $post_id); // book first name
							}
							
						}
					}else{
						if(!$currentBookLastName){
							update_field(FIELD_ID_BOOK_LASTNAME, end($titleArray), $post_id); // book last name
						}
						if(!$currentBookFirstName){
							update_field(FIELD_ID_BOOK_FIRSTNAME, implode(" ", $titleArray), $post_id); // book first name
						}
					}

					//$htmlStr = mb_convert_encoding($parseData['parse']['text']['*'], 'UTF-8');

					$dom = new DOMDocument();
					$dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES'));
					$finder = new DomXPath($dom);

					$infoBoxHtml = "";
					$sectionHtml = "";
					$nodes       = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . WIKIPEDIA_INFOBOX_CLASS . " ')]");
					if ($nodes->length > 0) {
						$allTrTag = $nodes->item(0)->getElementsByTagName('tr');
						foreach ($allTrTag as $trTag) {
							$allTdTag = $trTag->getElementsByTagName('td');
							$allthTag = $trTag->getElementsByTagName('th');
							
							$th_title = $allthTag->item(0);
							// Why need to validate == 2 ????
							if(!$th_title){
								$title = $allTdTag->item(0)->nodeValue;
								$value = $allTdTag->item(1)->nodeValue;
								$title = preg_replace( "/\r|\n/", "", $title );
        						$title = preg_replace( '/\d{4}/','$0 ', $title );
								
								$value = preg_replace( "/\r|\n/", "", $value );
        						$value = preg_replace( '/\d{4}/','$0 ', $value );
        						$r = '~(\))(\w)~';
        						$value = preg_replace($r, '$1 $2', $value);
        						if($value){
        							$infoBoxHtml .= "<p>" . $title . ": " . $value . "</p>";
        						}
							}else{
								$title = $allthTag->item(0)->nodeValue;
								$value = $allTdTag->item(0)->nodeValue;
								$title = preg_replace( "/\r|\n/", "", $title );
        						$title = preg_replace( '/\d{4}/','$0 ', $title );
								
								$value = preg_replace( "/\r|\n/", "", $value );
        						$value = preg_replace( '/\d{4}/','$0 ', $value );
        						$r = '~(\))(\w)~';
        						$value = preg_replace($r, '$1 $2', $value);
        						if($value){
        							$infoBoxHtml .= "<p>" . $title . ": " . $value . "</p>";
        						}
							}
						}
					}
					$sections = $parseData['parse']['sections'];
					foreach ($sections as $section) {
						if ($section["level"] === "2") {
							// section["level"] return string
							$sectionHtml .= '<p><a href="#' . $section["anchor"] . '">' . $section["line"] . '</a></p>';
						}
					}

					update_field(FIELD_ID_SECTIONS, $sectionHtml, $post_id); // sections
					update_field(FIELD_ID_INFOBOX, $infoBoxHtml, $post_id); // infobox

					$tags = array();
					$municipalityList = get_field(FIELD_ID_MUNICIPALITY, $post_id);
					if ($municipalityList == "") {
						$municipalityList = array();
					}
		
					$categories_data = $parseData['parse']['categories'];
					$categorieshtml = $parseData['parse']['categorieshtml'];
					
					//Make a list of current category
					$new_arr_title = '';
					foreach ($categories as $category) {
						$new_arr_title[] = $category['title'];
					}
					
					//Try to add missing category to current
					foreach($categories_data as $cat){
						if(isset($cat['hidden']) ){ continue; };
						$cate_name = str_replace("_"," ",$cat['*']);
						$cate_name = 'Kategori:'. $cate_name;

						if(!in_array($cate_name, $new_arr_title)){
							$categories[] = array('ns' => 14, 'title' =>$cate_name);
						}
					}
					//Process category update
					foreach ($categories as $category) {
						$subject = mb_convert_encoding($category["title"], 'UTF-8');
						$subject = str_replace(WIKIPEDIA_TERM_CATEGORY . ":", "", $subject);
						if ($subject == "Norske lyrikere") {
							// var_dump(array_key_exists($subject, $wikiCategoryGroup));
							continue;
						}
						if (array_key_exists($subject, $wikiCategoryGroup)) {
							$subject = $wikiCategoryGroup[$subject];
						}
						$tags []= $subject;
						//Update other field
						if (($subject == WIKIPEDIA_TERM_MALE) || ($subject == WIKIPEDIA_TERM_FEMALE)) {
							update_field(FIELD_ID_GENDER, $subject, $post_id);
							continue;
						}
						preg_match('/' . WIKIPEDIA_TERM_MUNICIPALITY . '/', $subject, $municipalityMatches);
						if (count($municipalityMatches) > 1) {
							array_push($municipalityList, $municipalityMatches[1]);
							continue;
						}
						preg_match('/' . WIKIPEDIA_TERM_BIRTHDAY . ' (.*)/', $subject, $birthYearMatches);
						if (count($birthYearMatches) > 1) {
							update_field(FIELD_ID_BIRTHYEAR, $birthYearMatches[1], $post_id);
							continue;
						}
						preg_match('/' . WIKIPEDIA_TERM_DEADDAY . ' (.*)/', $subject, $deathYearMatches);
						if (count($deathYearMatches) > 1) {
							update_field(FIELD_ID_DEATHYEAR, $deathYearMatches[1], $post_id);
							continue;
						}
					}

					if (count($tags) > 0) {
						wp_set_post_tags($post_id, implode(",", $tags), true);
					}

					if (count($municipalityList) > 0) {
						update_field(FIELD_ID_MUNICIPALITY, serialize($municipalityList), $post_id);
					}
				

					//||  $thumbnail['source'] &&
					if ( (isset($thumbnail) && $post_id && $thumbnail['source'] !== get_field(FIELD_ID_THUMBNAIL, $post_id) ) || ( $thumbnail['source'] && !has_post_thumbnail( $post_id ))  ) {

						$filename_notconverthtf8 = $pageimage;
						$filename = wiki_api_parse_utf8($pageimage);
						$filename = str_replace($utf8char, $non_utf8char, $filename);

						$attach_id = wiki_api_save_file($pageimage, $filename, $thumbnail['source']);
						if ($attach_id) {
							set_post_thumbnail($post_id, $attach_id);
						}
					}else{ //If wiki return no image, delete post image if exist
						if($post_id ){
							delete_post_thumbnail( $post_id );
						}						
					}
					if($isNewImport){ //Set auto update field to true as default so that the post is updated by daily cron
						update_field(FIELD_ID_AUTO_UPDATE, true, $post_id); 
					}
					$crr_date = date("Y.m.d");
					$last_date = get_field("author_update_log", $post_id); 
					$crr_date = $last_date. '<br>' . $crr_date;
					$up = update_field("author_update_log", $crr_date, $post_id);
					
					$crr_date_time = date("Y.m.d");
					update_field("author_last_updated", $crr_date_time, $post_id);
					$result['success'][] = "$pageId . [$postTitle] is inserted!";

					/*Update list id of author*/
					$list_id[] = $post_id. ' - ' . $postTitle;

					$i++;
				} else {
					$result['fail'][] = "$pageId not changed!";
				}

				if(!$force_update){
					/*update log of list author was updated*/
					$list_id_content = implode("<br>", $list_id);
					$my_post = array();
					$my_post['ID'] = LAST_UPDATED_AUTHORS_PAGE_ID;
					$my_post['post_content'] = $list_id_content;
					wp_update_post( $my_post );
				}
				
			}

		}

		return $result;
	}

}

if (!function_exists(getWikiCategory)) {
	function getWikiCategory() {
		$result       = array();
		$parentGroups = get_terms('groups', array('hide_empty' => 0, "parent" => 0));
		foreach ($parentGroups as $group) {
			$groupId     = $group->term_id;
			$childGroups = get_terms('groups', array('hide_empty' => 0, "parent" => $groupId));
			foreach ($childGroups as $childGroup) {
				$result[$childGroup->name] = $group->name;
			}
		}
		return $result;
	}
}

if (!function_exists(wiki_api_curl)) {
	function wiki_api_curl($method, $data) {
		$url = WIKIPEDIA_API_ENDPOINT;
		$url .= '?' . http_build_query($data);
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $url,
			CURLOPT_USERAGENT      => 'Wordpress Wiki cURL Request',
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
		));
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		if ($resp === FALSE) {
			var_dump(curl_error($curl));
			die();
		}
		// Close request to clear up some resources
		curl_close($curl);

		return $resp;
	}
}

if (!function_exists(wiki_activation)) {
	function wiki_activation() {
		wp_schedule_event(strtotime('23:59:00'), 'daily', 'wiki_cron_daily_event');
	}
}

if (!function_exists(wiki_deactivation)) {
	function wiki_deactivation() {
		wp_clear_scheduled_hook('wiki_cron_daily_event');
	}
}

if (!function_exists(wiki_api_parse_utf8)) {
	function wiki_api_parse_utf8($str) {
		$utf8char     = array(" ", "å", "ø", "æ", "Å", "Ø", "Æ");
		$non_utf8char = array(" ", "aa", "oo", "ae", "AA", "OO", "AE");

		return str_replace($utf8char, $non_utf8char, $str);
	}
}

if (!function_exists(wiki_api_nopriv_import_action)) {
	function wiki_api_nopriv_import_action() {
		if ($_REQUEST && isset($_REQUEST['data'])) {
			$result = wiki_api_import($_REQUEST['data'], false);
			if (isset($result['success'])) {
				echo implode("\r\n", $result['success']);
			}

			if (isset($result['fail'])) {
				echo implode("\r\n", $result['fail']);
			}

		} else {
			echo 'Request Data is empty';
		}
		die();
	}
}
if (!function_exists(wiki_api_import_action)) {
	function wiki_api_import_action() {
		if ($_REQUEST && isset($_REQUEST['data'])) {
			$result = wiki_api_import($_REQUEST['data']);
			if (isset($result['success'])) {
				echo implode("\r\n", $result['success']);
			}

			if (isset($result['fail'])) {
				echo implode("\r\n", $result['fail']);
			}

		} else {
			echo 'Request Data is empty';
		}
		die();
	}
}

if (!function_exists(wiki_cron_daily_action)) {
	function wiki_cron_daily_action() {
		wiki_log("Start Cron");
		$queryPosts = get_posts(array(
			'numberposts' => -1,
			'post_type'   => 'post',
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'     => 'page_id',
					'value'   => '',
					'compare' => '!=',
				),
			),
		));
		if (count($queryPosts) <= 0) {
			wiki_log("queryPosts < = 0");
			return;
		}else{
			wiki_log("queryPosts > 0 - go ahead. Count result" . count($queryPosts));
		}

		$pageids[] = array();
		$i         = 0;
		foreach ($queryPosts as $post) {
			//$pageid = get_field(FIELD_ID_PAGE_ID, $post); //Return false in some pages, not sure why
			$pageid = get_post_meta($post->ID, FIELD_ID_PAGE_ID,true);
			wiki_log("Before break page size - " . $pageid);
			if (!empty($pageid)) {
				wiki_log("Count list $pageid - " . count($pageids ) );
				if (!is_array($pageids[round($i / CRON_IMPORT_PAGE_SIZE)])) {
					$pageids[round($i / CRON_IMPORT_PAGE_SIZE)] = array();
				}
				$pageids[round($i / CRON_IMPORT_PAGE_SIZE)][] = $pageid;
				$i++;
			}else{
				wiki_log("$pageid empty - can not update");
			}
		}
		
		wiki_log("Before import");
		
		foreach ($pageids as $data) {
			wiki_log("Import [" . implode(",", $data) . "]");
			import_by_batch($data);
		}

	}

	function import_by_batch($pageids) {
		$data = array(
			'action' => 'wiki_api_import',
			'data'   => $pageids,
			'type'   => 'cron',
		);

		$url = get_site_url() . "/wp-admin/admin-ajax.php";
		$url .= '?' . http_build_query($data);
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 0,
			CURLOPT_URL            => $url,
			CURLOPT_USERAGENT      => 'Wordpress Wiki cURL Request',
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
		));
		// Send the request & save response to $resp
		curl_exec($curl);
		curl_close($curl);
	}
}

if (!function_exists(wiki_log)) {
	function wiki_log($data) {
		$data = date("Y-m-d h:i:s") . " : {$data}\r\n";

		$path     = ABSPATH . "wp-content/uploads/log";
		$pathFile = $path . "/" . LOG_FILE_PATH;
		if (!file_exists($path)) {
			mkdir($path, 0700);
		}
		file_put_contents($pathFile, $data, FILE_APPEND);
	}
}
/**
 * Add all hooks
 */
add_action('admin_menu', 'wiki_api_admin_default_setup');
add_action('wiki_api_enqueue_script', 'wiki_api_register_script');
add_action('wiki_api_enqueue_css', 'wiki_api_register_css');
add_action('wiki_api_form', 'wiki_api_import_default_form');

add_action('wp_ajax_wiki_api_import', 'wiki_api_import_action');
add_action('wp_ajax_nopriv_wiki_api_import', 'wiki_api_nopriv_import_action');
add_action('wp_ajax_wiki_api_search', 'wiki_api_search_search');

register_activation_hook(__FILE__, 'wiki_activation');
register_deactivation_hook(__FILE__, 'wiki_deactivation');

add_action('wiki_cron_daily_event', 'wiki_cron_daily_action');
