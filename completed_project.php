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
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
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
$document_root = str_replace('modules/' . $directory_name . '/completed_project.php', '', $script_name);

// Optional tagging support (only if Sprockets module installed)
$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
$projects_tag_name = '';

if (icms_get_module_status("sprockets"))
{
	icms_loadLanguageFile("sprockets", "common");
	$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	$criteria = icms_buildCriteria(array('label_type' => '0'));
	$sprockets_tag_buffer = $sprockets_tag_handler->getList($criteria, TRUE, TRUE);
}

// Assign common logo preferences to template
$icmsTpl->assign('display_project_logos', icms::$module->config['display_project_logos']);
$icmsTpl->assign('projects_freestyle_logo_dimensions', icms::$module->config['projects_freestyle_logo_dimensions']);
$icmsTpl->assign('projects_logo_display_width', icms::$module->config['projects_logo_index_display_width']);
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
	// Update hit counter
	if (!icms_userIsAdmin(icms::$module->getVar('dirname')))
	{
		$projects_project_handler->updateCounter($projectObj);
	}
	
	// Convert object to array for easy insertion into templates
	$project = $projectObj->toArray();
	
	// Add SEO friendly string to URL
	if (!empty($project['short_url']))
	{
		$project['itemUrl'] .= "&amp;title=" . $project['short_url'];
	}
	
	// Check if the hit counter should be displayed or not
	if (icms::$module->config['projects_show_view_counter'] == FALSE)
	{
		unset($project['counter']);
	}

	// Adjust logo path for template
	if (!empty($project['logo']))
	{
		$project['logo'] = $document_root . 'uploads/' . $directory_name . '/project/' . $project['logo'];
	}
	
	// Prepare tags for display
	if (icms_get_module_status("sprockets"))
	{
		$project['tag'] = array();
		$project_tag_array = $sprockets_taglink_handler->getTagsForObject($projectObj->getVar('project_id'), $projects_project_handler, 0);
		foreach ($project_tag_array as $key => $value)
		{
			if ($value == 0) {
				$project['tag'][$value] = '<a href="' . PROJECTS_URL 
						. 'completed_project.php?tag_id=untagged">' . _CO_PROJECTS_UNTAGGED . '</a>';
			} else {
				$project['tag'][$value] = '<a href="' . PROJECTS_URL 
						. 'completed_project.php?tag_id=' . $value . '">' 
						. $sprockets_tag_buffer[$value] . '</a>';
			}
		}
		$project['tag'] = implode(', ', $project['tags']);
	}

	// If the project is completed, add the completed flag to the breadcrumb title
	if ($projectObj->getVar('complete', 'e') == 1)
	{
		$icmsTpl->assign("projects_completed_path", _CO_PROJECTS_PROJECT_COMPLETE);
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
	// Get a select box (if preferences allow, and only if Sprockets module installed)
	if (icms_get_module_status("sprockets") && icms::$module->config['projects_show_tag_select_box'] == TRUE)
	{
		// Initialise
		$projects_tag_name = '';
		$tagList = array();
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
				'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
				$sprocketsModule->getVar('dirname'), 'sprockets');

		// Append the tag to the breadcrumb title
		if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0))
		{
			$projects_tag_name = $sprockets_tag_buffer[$clean_tag_id];
			$icmsTpl->assign('projects_tag_name', $projects_tag_name);
			$icmsTpl->assign('projects_category_path', $sprockets_tag_buffer[$clean_tag_id]);
		}

		// Load the tag navigation select box
		// $action, $selected = null, $zero_option_message = '---', 
		// $navigation_elements_only = TRUE, $module_id = null, $item = null,
		if ($untagged_content) {
			$tag_select_box = $sprockets_tag_handler->getTagSelectBox('completed_project.php',
					'untagged', _CO_PROJECTS_PROJECT_ALL_TAGS, TRUE, icms::$module->getVar('mid'), 
					'project', TRUE);
		} else {
			$tag_select_box = $sprockets_tag_handler->getTagSelectBox('completed_project.php',
					$clean_tag_id, _CO_PROJECTS_PROJECT_ALL_TAGS, TRUE, icms::$module->getVar('mid'),
					'project', TRUE);
		}	
		$icmsTpl->assign('projects_tag_select_box', $tag_select_box);
	}
	
	///////////////////////////////////////////////////////////////////
	////////// View projects as list of summary descriptions //////////
	///////////////////////////////////////////////////////////////////
	if (icms::$module->config['projects_index_display_mode'] == TRUE)
	{
		$project_summaries = $linked_project_ids = array();
		
		// Append the tag name to the module title (if preferences allow, and only if Sprockets module installed)
		if (icms_get_module_status("sprockets") && icms::$module->config['projects_show_breadcrumb'] == FALSE)
		{
			if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0))
			{
				$projects_tag_name = $sprockets_tag_buffer[$clean_tag_id];
				$icmsTpl->assign('projects_tag_name', $projects_tag_name);
			} elseif ($untagged_content) {
				$icmsTpl->assign('projects_tag_name', _CO_PROJECTS_UNTAGGED);
			}
		} else {
			if ($untagged_content) {
				$icmsTpl->assign('projects_category_path', _CO_PROJECTS_UNTAGGED);
			} else {
				$icmsTpl->assign('projects_category_path', $sprockets_tag_buffer[$clean_tag_id]);
			}
		}
				
		// Retrieve projects for a given tag
		if (($clean_tag_id || $untagged_content) && icms_get_module_status("sprockets"))
		{
			/**
			 * Retrieve a list of projects JOINED to taglinks by project_id/tag_id/module_id/item
			 */

			$query = $rows = $project_count = '';
			$linked_project_ids = array();
			
			// First, count the number of projects for the pagination control
			$project_count = '';
			$group_query = "SELECT count(*) FROM " . $projects_project_handler->table . ", "
					. $sprockets_taglink_handler->table
					. " WHERE `project_id` = `iid`"
					. " AND `online_status` = '1'"
					. " AND `complete` = '1'"
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
					. " AND `complete` = '1'"
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
			$criteria->add(new icms_db_criteria_Item('complete', '1'));
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
		
		// Prepare tags. A list of project IDs is used to retrieve relevant taglinks. The taglinks
		// are sorted into a multidimensional array, using the project ID as the key to each subarray.
		// Then its just a case of assigning each subarray to the matching project.
		 
		// Prepare a list of project_id, this will be used to create a taglink buffer, which is used
		// to create tag links for each project
		$linked_project_ids = array();
		foreach ($project_summaries as $key => $value) {
			$linked_project_ids[] = $value['project_id'];
		}
		
		if (icms_get_module_status("sprockets") && !empty($linked_project_ids))
		{
			$tagList = $sprockets_tag_handler->getTagBuffer(TRUE);
			$taglink_buffer = $sprockets_taglink_handler->getTagsForObjects($linked_project_ids, 'project');
			foreach ($project_summaries as &$project) {
				$tagLinks = $icons = array();
					if ($taglink_buffer[$project['project_id']]) {
						foreach ($taglink_buffer[$project['project_id']] as $tag) {
							if ($tag == '0') {
								$tagLinks[] = '<a href="' . PROJECTS_URL 
										. 'completed_project.php?tag_id=untagged">' 
										. $tagList[$tag]->getVar('title') . '</a>';
							} else {
								$tagLinks[] = '<a href="' . PROJECTS_URL 
										. 'completed_project.php?tag_id=' . $tag . '">' 
										. $tagList[$tag]->getVar('title') . '</a>';
								$icons[] = '<a href="' . PROJECTS_URL
										. 'completed_project.php?tag_id=' . $tag . '">'
										. $tagList[$tag]->getVar('icon') . '</a>';
							}
						}
						$project['tag'] = implode(", ", $tagLinks);
						unset($tagLinks);
					}
				$tag_icons[$project['project_id']] = $icons;
				unset($icons);
			}
		}
		
		// Prepare project for display
		foreach ($project_summaries as &$project)
		{			
			// Ajust logo paths to allow dynamic image resizing
			if (!empty($project['logo']))
			$project['logo'] = $document_root . 'uploads/' . $directory_name . '/project/'
				. $project['logo'];
			
			// Add SEO friendly string to URL
			if (!empty($project['short_url']))
			{
				$project['itemUrl'] .= "&amp;title=" . $project['short_url'];
			}
			
			// Alter the itemUrl to point at the completed projects page, rather than the projects page
			$project['itemUrl'] = str_replace('project.php', 'completed_project.php', $project['itemUrl']);
		}
		$icmsTpl->assign('project_summaries', $project_summaries);
		
		// Adjust pagination for tag, if present
		if (!empty($clean_tag_id))
		{
			$extra_arg = 'tag_id=' . $clean_tag_id;
		}
		else
		{
			$extra_arg = TRUE;
		}
		
		// Pagination control
		$pagenav = new icms_view_PageNav($project_count, icms::$module->config['number_of_projects_per_page'],
				$clean_start, 'start', $extra_arg);
		$icmsTpl->assign('projects_navbar', $pagenav->renderNav());
	}
	else 
	{
		//////////////////////////////////////////////////////////////////////////////
		////////// View projects in compact table, optionally filter by tag //////////
		//////////////////////////////////////////////////////////////////////////////
		
		$tagged_project_list = '';
		
		if (($clean_tag_id || $untagged_content) && icms_get_module_status("sprockets")) 
		{
			// get a list of project IDs belonging to this tag
			$criteria = new icms_db_criteria_Compo();
			if ($untagged_content) {
				$criteria->add(new icms_db_criteria_Item('tid', 0));
			} else {
				$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
			}
			$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('item', 'project'));
			$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
			foreach ($taglink_array as $taglink) {
				$tagged_project_list[] = $taglink->getVar('iid');
			}
			$tagged_project_list = "('" . implode("','", $tagged_project_list) . "')";			
		}
		$criteria = new icms_db_criteria_Compo();
		if (!empty($tagged_project_list))
		{
			$criteria->add(new icms_db_criteria_Item('project_id', $tagged_project_list, 'IN'));
		}
		$criteria->add(new icms_db_criteria_Item('complete', '1'));
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$criteria->setSort('weight');
		$criteria->setOrder('ASC');
		
		// Retrieve the table
		$objectTable = new icms_ipf_view_Table($projects_project_handler, $criteria, array());
		$objectTable->isForUserSide();
		$objectTable->addQuickSearch('title');
		$objectTable->addColumn(new icms_ipf_view_Column("title", _GLOBAL_LEFT, FALSE, addSEOStringToItemUrl));
		$objectTable->addColumn(new icms_ipf_view_Column("date"));
		$objectTable->addColumn(new icms_ipf_view_Column("last_update"));
		$icmsTpl->assign("projects_project_table", $objectTable->fetch());
	}
}

// Breadcrumb
if (icms::$module->config['projects_show_breadcrumb'])
{
	$icmsTpl->assign("projects_show_breadcrumb", icms::$module->config['projects_show_breadcrumb']);
	$icmsTpl->assign("projects_module_home", '<a href="' . ICMS_URL . "/modules/" 
			. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
	if ($projects_tag_name)
	{
		$icmsTpl->assign("projects_completed_path", '<a href="' . ICMS_URL . "/modules/" 
				. icms::$module->getVar("dirname") . '/completed_project.php">' 
				. _CO_PROJECTS_PROJECT_COMPLETE . "</a>");
	}
	else
	{
		$icmsTpl->assign("projects_completed_path", _CO_PROJECTS_PROJECT_COMPLETE);
	}
}

// Page title
$icmsTpl->assign("projects_page_title", _CO_PROJECTS_COMPLETE_PROJECTS);

include_once "footer.php";