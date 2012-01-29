<?php
/**
 * English language constants related to module information
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

define("_MI_PROJECTS_MD_NAME", "projects");
define("_MI_PROJECTS_MD_DESC", "ImpressCMS Simple projects");
define("_MI_PROJECTS_PROJECTS", "Projects");
define("_MI_PROJECTS_TEMPLATES", "Templates");

// Blocks
define("_MI_PROJECTS_RANDOM", "Random projects");
define("_MI_PROJECTS_RANDOMDSC", "Display random projects");

// Preferences
define("_MI_PROJECTS_INDEX_DISPLAY_MODE", "Display index as list of summaries");
define("_MI_PROJECTS_INDEX_DISPLAY_MODE_DSC", "Toggles the display of the index page between a list of summaries (yes) and a compact list of tables (no).");
define("_MI_PROJECTS_NUMBER_PROJECTS_PER_PAGE", "Number of projects per page");
define("_MI_PROJECTS_NUMBER_PROJECTS_PER_PAGE_DSC", "Controls how many projects are shown on the index page, sane value is 5-10.");
define("_MI_PROJECTS_SHOW_TAG_SELECT_BOX", "Show tag select box");
define("_MI_PROJECTS_SHOW_TAG_SELECT_BOX_DSC", "Toggles the tag select box on/off for the projects index page (only if Sprockets module installed).");
define("_MI_PROJECTS_SHOW_BREADCRUMB", "Show breadcrumb");
define("_MI_PROJECTS_SHOW_BREADCRUMB_DSC", "Toggles the module breadcrumb on/off");
define("_MI_PROJECTS_SHOW_VIEW_COUNTER", "Show views counter?");
define("_MI_PROJECTS_SHOW_VIEW_COUNTER_DSC", "Toggles the visibility of the views counter field.");
define("_MI_PROJECTS_SHOW_LAST_UPDATED", "Show date last updated?");
define("_MI_PROJECTS_SHOW_LAST_UPDATED_DSC", "Labels a project as updated. Labels are good for one month");
define("_MI_PROJECTS_DISPLAY_PROJECT_LOGOS", "Display project logos");
define("_MI_PROJECTS_DISPLAY_PROJECT_LOGOS_DSC", "Toggles logos on or off.");
define("_MI_PROJECTS_PROJECT_LOGO_POSITION", "Logo position");
define("_MI_PROJECTS_PROJECT_LOGO_POSITION_DSC", "Display project logos on the left or right side of the page.");
define("_MI_PROJECTS_FREESTYLE_LOGO_DIMENSIONS", "Freestyle logo dimensions");
define("_MI_PROJECTS_FREESTYLE_LOGO_DIMENSIONS_DSC", "If enabled, logos will NOT be automatically resized. This setting is useful if your project logos vary in shape and want to manually resize your logos yourself.");
define("_MI_PROJECTS_LOGO_INDEX_DISPLAY_WIDTH", "Logo display width on the INDEX page (pixels)");
define("_MI_PROJECTS_LOGO_INDEX_DISPLAY_WIDTH_DSC", "Project logos will be dynamically resized according to this value. You can change the value any time you like. However, you should upload logos that are slightly LARGER than the maximum desired display size to avoid pixelation due to upscaling.");
define("_MI_PROJECTS_LOGO_SINGLE_DISPLAY_WIDTH", "Logo display width in SINGLE view (pixels)");
define("_MI_PROJECTS_LOGO_SINGLE_DISPLAY_WIDTH_DSC", "Project logos will be dynamically resized according to this value. You can change the value any time you like. However, you should upload logos that are slightly LARGER than the maximum desired display size to avoid pixelation due to upscaling.");
define("_MI_PROJECTS_LOGO_BLOCK_DISPLAY_WIDTH", "Logo display width in the Random Projects block (pixels)");
define("_MI_PROJECTS_LOGO_BLOCK_DISPLAY_WIDTH_DSC", "Project logos will be dynamically resized according to this value. You can change the value any time you like. However, you should upload logos that are slightly LARGER than the maximum desired display size to avoid pixelation due to upscaling.");
define("_MI_PROJECTS_LOGO_UPLOAD_HEIGHT", "Maximum height of logo files (pixels)");
define("_MI_PROJECTS_LOGO_UPLOAD_HEIGHT_DSC", "Logo files may not exceed this value.");
define("_MI_PROJECTS_LOGO_UPLOAD_WIDTH", "Maximum width of logo files (pixels)");
define("_MI_PROJECTS_LOGO_UPLOAD_WIDTH_DSC", "Logo files may not exceed this value.");
define("_MI_PROJECTS_LOGO_FILE_SIZE", "Maximum file size of logo files (bytes)");
define("_MI_PROJECTS_LOGO_FILE_SIZE_DSC", "Logo files may not exceed this value.");
define("_MI_PROJECTS_DATE_FORMAT", "Date format");
define("_MI_PROJECTS_DATE_FORMAT_DSC", "Controls the format of the date in project 'updated' notices. See the <a href='http://php.net/manual/en/function.date.php'>PHP manual</a> for formatting options.");
define("_MI_PROJECTS_UPDATED_NOTICE_PERIOD", "Display 'updated' notice time");
define("_MI_PROJECTS_UPDATED_NOTICE_PERIOD_DSC", "How long do you want to display 'updated' notices on projects?");

// Preference options
define("_MI_PROJECTS_LEFT", "Left");
define("_MI_PROJECTS_RIGHT", "Right");
define("_MI_PROJECTS_ONE_DAY", "One day");
define("_MI_PROJECTS_THREE_DAYS", "Three days");
define("_MI_PROJECTS_ONE_WEEK", "One week");
define("_MI_PROJECTS_TWO_WEEKS", "Two weeks");
define("_MI_PROJECTS_THREE_WEEKS", "Three weeks");
define("_MI_PROJECTS_FOUR_WEEKS", "Four weeks");

// Submenu
define("_MI_PROJECTS_COMPLETED", "Completed");