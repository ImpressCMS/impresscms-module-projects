<?php
/**
 * projects version infomation
 *
 * This file holds the configuration information of this module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

/**  General Information  */
$modversion = array(
	"name"						=> _MI_PROJECTS_MD_NAME,
	"version"					=> 1.02,
	"description"				=> _MI_PROJECTS_MD_DESC,
	"author"					=> "Madfish (Simon Wilkinson)",
	"credits"					=> "Thanks to Rene Sato, qm-b, Phoenyx and skenow for testing and feedback. Module icon by David Lanham http://dlanham.com/",
	"help"						=> "",
	"license"					=> "GNU General Public License (GPL)",
	"official"					=> 0,
	"dirname"					=> basename(dirname(__FILE__)),
	"modname"					=> "projects",

/**  Images information  */
	"iconsmall"					=> "images/icon_small.png",
	"iconbig"					=> "images/icon_big.png",
	"image"						=> "images/icon_big.png", /* for backward compatibility */

/**  Development information */
	"status_version"			=> "1.02",
	"status"					=> "Final",
	"date"						=> "16/6/2012",
	"author_word"				=> "For ICMS 1.3+ and 2.0 only.",

/** Contributors */
	"developer_website_url"		=> "https://www.isengard.biz",
	"developer_website_name"	=> "Isengard.biz",
	"developer_email"			=> "simon@isengard.biz",

/** Administrative information */
	"hasAdmin"					=> 1,
	"adminindex"				=> "admin/index.php",
	"adminmenu"					=> "admin/menu.php",

/** Install and update informations */
	"onInstall"					=> "include/onupdate.inc.php",
	"onUpdate"					=> "include/onupdate.inc.php",

/** Search information */
	"hasSearch"					=> 1,
	"search"					=> array("file" => "include/search.inc.php", "func" => "projects_search"),

/** Comments information */
	"hasComments"				=> 0
	);

/** Menu information */
$modversion["hasMain"] = 1;
$modversion["sub"][0]["name"] = _MI_PROJECTS_COMPLETED;
$modversion["sub"][0]["url"] = "completed_project.php";

/** other possible types: testers, translators, documenters and other */
$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] = "<a href='http://wiki.impresscms.org/index.php?title=projects' target='_blank'>English</a>";

/** Database information */
$modversion['object_items'][1] = 'project';

$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Templates information */
$modversion['templates'] = array(
	array("file" => "projects_admin_project.html", "description" => "Project admin index."),
	array("file" => "projects_project.html", "description" => "Project index."),
	array("file" => "projects_header.html", "description" => "Module header."),
	array("file" => "projects_footer.html", "description" => "Module footer."),
	array("file" => "projects_requirements.html", "description" => "Alert if module requirements not met."));

/** Blocks */
$modversion['blocks'][1] = array(
	'file' => 'random_projects.php',
	'name' => _MI_PROJECTS_RANDOM,
	'description' => _MI_PROJECTS_RANDOMDSC,
	'show_func' => 'show_random_projects',
	'edit_func' => 'edit_random_projects',
	'options' => '5|1|1|0|0', // Number of articles per page, tag_id, randomise, summary or list view, show current projects
	'template' => 'projects_block_random.html'
);

/** Preferences */
$modversion['config'][1] = array(
  'name' => 'projects_index_display_mode',
  'title' => '_MI_PROJECTS_INDEX_DISPLAY_MODE',
  'description' => '_MI_PROJECTS_INDEX_DISPLAY_MODE_DSC',
  'formtype' => 'yesno',
  'valuetype' => 'int',
  'default' =>  '1');

$modversion['config'][] = array(
  'name' => 'number_of_projects_per_page',
  'title' => '_MI_PROJECTS_NUMBER_PROJECTS_PER_PAGE',
  'description' => '_MI_PROJECTS_NUMBER_PROJECTS_PER_PAGE_DSC',
  'formtype' => 'textbox',
  'valuetype' => 'int',
  'default' =>  '5');

$modversion['config'][] = array(
	'name' => 'projects_show_breadcrumb',
	'title' => '_MI_PROJECTS_SHOW_BREADCRUMB',
	'description' => '_MI_PROJECTS_SHOW_BREADCRUMB_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '1');

$modversion['config'][] = array(
	'name' => 'projects_show_view_counter',
	'title' => '_MI_PROJECTS_SHOW_VIEW_COUNTER',
	'description' => '_MI_PROJECTS_SHOW_VIEW_COUNTER_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '1');

$modversion['config'][] = array(
	'name' => 'projects_show_tag_select_box',
	'title' => '_MI_PROJECTS_SHOW_TAG_SELECT_BOX',
	'description' => '_MI_PROJECTS_SHOW_TAG_SELECT_BOX_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '1');

$modversion['config'][] = array(
	'name' => 'display_project_logos',
	'title' => '_MI_PROJECTS_DISPLAY_PROJECT_LOGOS',
	'description' => '_MI_PROJECTS_DISPLAY_PROJECT_LOGOS_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '1');

$modversion['config'][] = array(
	'name' => 'project_logo_position',
	'title' => '_MI_PROJECTS_PROJECT_LOGO_POSITION',
	'description' => '_MI_PROJECTS_PROJECT_LOGO_POSITION_DSC',
	'formtype' => 'select',
	'valuetype' => 'int',
	'options' => array('_MI_PROJECTS_LEFT' => 0, '_MI_PROJECTS_RIGHT' => 1),
	'default' => 1);

$modversion['config'][] = array(
	'name' => 'projects_freestyle_logo_dimensions',
	'title' => '_MI_PROJECTS_FREESTYLE_LOGO_DIMENSIONS',
	'description' => '_MI_PROJECTS_FREESTYLE_LOGO_DIMENSIONS_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '0');

$modversion['config'][] = array(
  'name' => 'projects_logo_index_display_width',
  'title' => '_MI_PROJECTS_LOGO_INDEX_DISPLAY_WIDTH',
  'description' => '_MI_PROJECTS_LOGO_INDEX_DISPLAY_WIDTH_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '150');

$modversion['config'][] = array(
  'name' => 'projects_logo_single_display_width',
  'title' => '_MI_PROJECTS_LOGO_SINGLE_DISPLAY_WIDTH',
  'description' => '_MI_PROJECTS_LOGO_SINGLE_DISPLAY_WIDTH_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '300');

$modversion['config'][] = array(
  'name' => 'projects_logo_block_display_width',
  'title' => '_MI_PROJECTS_LOGO_BLOCK_DISPLAY_WIDTH',
  'description' => '_MI_PROJECTS_LOGO_BLOCK_DISPLAY_WIDTH_DSC',
  'formtype' => 'text',
  'valuetype' => 'int',
  'default' =>  '150');

$modversion['config'][] = array(
	'name' => 'projects_logo_upload_height',
	'title' => '_MI_PROJECTS_LOGO_UPLOAD_HEIGHT',
	'description' => '_MI_PROJECTS_LOGO_UPLOAD_HEIGHT_DSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '500');

$modversion['config'][] = array(
	'name' => 'projects_logo_upload_width',
	'title' => '_MI_PROJECTS_LOGO_UPLOAD_WIDTH',
	'description' => '_MI_PROJECTS_LOGO_UPLOAD_WIDTH_DSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '500');

$modversion['config'][] = array(
	'name' => 'projects_logo_file_size',
	'title' => '_MI_PROJECTS_LOGO_FILE_SIZE',
	'description' => '_MI_PROJECTS_LOGO_FILE_SIZE_DSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '2097152'); // 2MB default max upload size

$modversion['config'][] = array(
	'name' => 'projects_show_last_updated',
	'title' => '_MI_PROJECTS_SHOW_LAST_UPDATED',
	'description' => '_MI_PROJECTS_SHOW_LAST_UPDATED_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => '1');

$modversion['config'][] = array(
  'name' => 'projects_date_format',
  'title' => '_MI_PROJECTS_DATE_FORMAT',
  'description' => '_MI_PROJECTS_DATE_FORMAT_DSC',
  'formtype' => 'textbox',
  'valuetype' => 'text',
  'default' =>  'j/n/Y');

$modversion['config'][] = array(
  'name' => 'projects_updated_notice_period',
  'title' => '_MI_PROJECTS_UPDATED_NOTICE_PERIOD',
  'description' => '_MI_PROJECTS_UPDATED_NOTICE_PERIOD_DSC',
  'formtype' => 'select',
  'valuetype' => 'int',
  'options' => array(
	  '_MI_PROJECTS_ONE_DAY' => 1,
	  '_MI_PROJECTS_THREE_DAYS' => 2,
	  '_MI_PROJECTS_ONE_WEEK' => 3,
	  '_MI_PROJECTS_TWO_WEEKS' => 4,
	  '_MI_PROJECTS_THREE_WEEKS' => 5,
	  '_MI_PROJECTS_FOUR_WEEKS' => 6),
  'default' => 3);

