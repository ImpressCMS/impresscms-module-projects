<?php
/**
* Project index page - displays details of a single project, a list of project summary descriptions or a compact table of projects
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2012
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		projects
* @version		$Id$
*/

include_once "header.php";

$xoopsOption["template_main"] = "projects_project.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Sanitise input parameters
$clean_project_id = isset($_GET["project_id"]) ? (int)$_GET["project_id"] : 0 ;
$clean_tag_id = isset($_GET["tag_id"]) ? (int)$_GET["tag_id"] : 0 ;
$clean_start = isset($_GET["start"]) ? (int)($_GET["start"]) : 0;

// Get the requested project, or retrieve the index page. Only show online projects
$projects_project_handler = icms_getModuleHandler("project", basename(dirname(__FILE__)), "projects");
$criteria = icms_buildCriteria(array('online_status' => '1'));
$projectObj = $projects_project_handler->get($clean_project_id, TRUE, FALSE, $criteria);

// Get relative path to document root for this ICMS install. This is required to call the logos correctly if ICMS is installed in a subdirectory
$directory_name = basename(dirname(__FILE__));
$script_name = getenv("SCRIPT_NAME");
$document_root = str_replace('modules/' . $directory_name . '/project.php', '', $script_name);

// Assign common logo preferences to template
$icmsTpl->assign('display_project_logos', icms::$module->config['display_project_logos']);
$icmsTpl->assign('freestyle_logo_dimensions', icms::$module->config['freestyle_logo_dimensions']);
$icmsTpl->assign('logo_display_width', icms::$module->config['logo_index_display_width']);
if (icms::$module->config['project_logo_position'] == 1) // Align right
{
	$icmsTpl->assign('project_logo_position', 'projects_float_right');
}
else // Align left
{
	$icmsTpl->assign('project_logo_position', 'projects_float_left');
}

/////////////////////////////////////////
////////// VIEW SINGLE PROJECT //////////
/////////////////////////////////////////

if($projectObj && !$projectObj->isNew())
{
	$updated = $projectObj->getVar('date', 'e');	
	$project = $projectObj->toArray();
	
	// Adjust logo path for template
	if (!empty($project['logo']))
	{
		$project['logo'] = $document_root . 'uploads/' . $directory_name . '/project/' . $project['logo'];
	}
	
	// Check if an 'updated' notice should be displayed. This works by comparing the time since the 
	// project was last updated against the length of time that an updated notice should be shown
	// (as set in the module preferences).
	if (icms::$module->config['show_last_updated'] == TRUE)
	{
		$updated = strtotime($project['date']);
		$update_periods = array(
			0 => 0,
			1 => 86400,		// Show updated notice for 1 day
			2 => 259200,	// Show updated notice for 3 days
			3 => 604800,	// Show updated notice for 1 week
			4 => 1209600,	// Show updated notice for 2 weeks
			5 => 1814400,	// Show updated notice for 3 weeks
			6 => 2419200	// Show updated notice for 4 weeks
			);
		$updated_notice_period = $update_periods[icms::$module->config['updated_notice_period']];

		if ((time() - $updated) < $updated_notice_period)
		{
			$project['date'] = date(icms::$module->config['date_format'], $updated);
			$project['updated'] = TRUE;
		}
	}
	
	$icmsTpl->assign("projects_project", $project);

	$icms_metagen = new icms_ipf_Metagen($projectObj->getVar("title"), 
			$projectObj->getVar("meta_keywords", "n"), $projectObj->getVar("meta_description", "n"));
	$icms_metagen->createMetaTags();
}

////////////////////////////////////////
////////// VIEW PROJECT INDEX //////////
////////////////////////////////////////
else
{
	$icmsTpl->assign("projects_title", _MD_PROJECTS_ALL_PROJECTS);
	if (icms::$module->config['index_display_mode'] == TRUE)
	{
		// View projects as list of summary descriptions
		
		$sprocketsModule = icms_getModuleInfo('sprockets');
		$project_summaries = array();
		
		// Load Sprockets language file, if required, to stop nagging warning notices
		if ($sprocketsModule)
		{
			icms_loadLanguageFile("sprockets", "common");
		}
		
		// Get a select box (if preferences allow, and only if Sprockets module installed)
		if ($sprocketsModule && icms::$module->config['show_tag_select_box'] == TRUE)
		{
			// Initialise
			$projects_tag_name = '';
			$tag_buffer = $tagList = array();
			$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
					'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					$sprocketsModule->getVar('dirname'), 'sprockets');

			// Prepare buffer to reduce queries
			$tag_buffer = $sprockets_tag_handler->getObjects(null, TRUE, FALSE);

			// Append the tag to the breadcrumb title
			if (array_key_exists($clean_tag_id, $tag_buffer) && ($clean_tag_id !== 0))
			{
				$projects_tag_name = $tag_buffer[$clean_tag_id]['title'];
				$icmsTpl->assign('projects_tag_name', $projects_tag_name);
				$icmsTpl->assign('projects_category_path', $tag_buffer[$clean_tag_id]['title']);
			}
			
			// Load the tag navigation select box
			// $action, $selected = null, $zero_option_message = '---', $navigation_elements_only = true, $module_id = null, $item = null
			$tag_select_box = $sprockets_tag_handler->getTagSelectBox('project.php', $clean_tag_id, _CO_PROJECTS_PROJECT_ALL_TAGS, TRUE, icms::$module->getVar('mid'));
			$icmsTpl->assign('projects_tag_select_box', $tag_select_box);
		}
		
		// Append the tag name to the module title (if preferences allow, and only if Sprockets module installed)
		if ($sprocketsModule && icms::$module->config['show_breadcrumb'] == FALSE)
		{
			if (array_key_exists($clean_tag_id, $tag_buffer) && ($clean_tag_id !== 0))
			{
				$projects_tag_name = $tag_buffer[$clean_tag_id]['title'];
				$icmsTpl->assign('projects_tag_name', $projects_tag_name);
			}
		}
				
		// Retrieve projects for a given tag
		if ($clean_tag_id && $sprocketsModule)
		{
			/**
			 * Retrieve a list of projects JOINED to taglinks by project_id/tag_id/module_id/item
			 */

			$query = $rows = $project_count = '';
			$linked_project_ids = array();
			
			// First, count the number of articles for the pagination control
			$project_count = '';
			$group_query = "SELECT count(*) FROM " . $projects_project_handler->table . ", "
					. $sprockets_taglink_handler->table
					. " WHERE `project_id` = `iid`"
					. " AND `online_status` = '1'"
					. " AND `complete` = '0'"
					. " AND `tid` = '" . $clean_tag_id . "'"
					. " AND `mid` = '" . icms::$module->getVar('mid') . "'"
					. " AND `item` = 'project'";
			
			$result = icms::$xoopsDB->query($group_query);

			if (!$result)
			{
				echo 'Error';
				exit;	
			}
			else
			{
				while ($row = icms::$xoopsDB->fetchArray($result))
				{
					foreach ($row as $key => $count) 
					{
						$project_count = $count;
					}
				}
			}
			
			// Secondly, get the projects
			$query = "SELECT * FROM " . $projects_project_handler->table . ", "
					. $sprockets_taglink_handler->table
					. " WHERE `project_id` = `iid`"
					. " AND `online_status` = '1'"
					. " AND `complete` = '0'"
					. " AND `tid` = '" . $clean_tag_id . "'"
					. " AND `mid` = '" . icms::$module->getVar('mid') . "'"
					. " AND `item` = 'project'"
					. " ORDER BY `weight` ASC"
					. " LIMIT " . $clean_start . ", " . icms::$module->config['number_of_projects_per_page'];

			$result = icms::$xoopsDB->query($query);

			if (!$result)
			{
				echo 'Error';
				exit;
			}
			else
			{
				$rows = $projects_project_handler->convertResultSet($result, TRUE, FALSE);
				foreach ($rows as $key => $row) 
				{
					$project_summaries[$row['project_id']] = $row;
				}
			}
		}
				
		// Retrieve projects without filtering by tag
		else
		{
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('complete', '0'));
			$criteria->add(new icms_db_criteria_Item('online_status', '1'));

			// Count the number of online projects for the pagination control
			$project_count = $projects_project_handler->getCount($criteria);

			// Continue to retrieve projects for this page view
			$criteria->setStart($clean_start);
			$criteria->setLimit(icms::$module->config['number_of_projects_per_page']);
			$criteria->setSort('weight');
			$criteria->setOrder('ASC');
			$project_summaries = $projects_project_handler->getObjects($criteria, TRUE, FALSE);
		}
		
		// Add 'updated' notices and adjust the logo paths to allow dynamic resizing as per the resized_image Smarty plugin
		foreach ($project_summaries as &$project)
		{
			if (icms::$module->config['show_last_updated'] == TRUE)
			{
				$updated = strtotime($project['date']);
				$update_periods = array(
					0 => 0,
					1 => 86400,
					2 => 259200,
					3 => 604800,
					4 => 1209600,
					5 => 1814400,
					6 => 2419200
					);
				$updated_notice_period = $update_periods[icms::$module->config['updated_notice_period']];
				
				if ((time() - $updated) < $updated_notice_period)
				{
					$project['date'] = date(icms::$module->config['date_format'], $updated);
					$project['updated'] = TRUE;
				}
			}
			
			if (!empty($project['logo']))
			$project['logo'] = $document_root . 'uploads/' . $directory_name . '/project/'
				. $project['logo'];
		}
		$icmsTpl->assign('project_summaries', $project_summaries);
		
		// Adjust pagination for tag, if present
		if (!empty($clean_tag_id))
		{
			$extra_arg = 'tag_id=' . $clean_tag_id;
		}
		else
		{
			$extra_arg = false;
		}
		
		// Pagination control
		$pagenav = new icms_view_PageNav($project_count, icms::$module->config['number_of_projects_per_page'],
				$clean_start, 'start', $extra_arg);
		$icmsTpl->assign('projects_navbar', $pagenav->renderNav());
	}
	else 
	{
		// View projects in compact table
		$criteria = new icms_db_criteria_Compo();
		$criteria = new icms_db_criteria_Item('complete', '0');
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$objectTable = new icms_ipf_view_Table($projects_project_handler, $criteria, array());
		$objectTable->isForUserSide();
		$objectTable->addColumn(new icms_ipf_view_Column("title"));
		$icmsTpl->assign("projects_project_table", $objectTable->fetch());
	}
}

$icmsTpl->assign("show_breadcrumb", icms::$module->config['show_breadcrumb']);
$icmsTpl->assign("projects_module_home", '<a href="' . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
include_once "footer.php";