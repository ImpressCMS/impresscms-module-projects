<?php
/**
 * Random projects block file
 *
 * This file holds the functions needed for the random projects block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
 * Modified for use in the Projects module by Madfish
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepare random projects block for display
 * 
 * Options:
 * 0. Number of projects to display
 * 1. Randomise?
 * 2. Display logos or simple list
 * 3. tag_id
 * 4. Show current projects
 * 5. Dynamic tag filtering
 *
 * @param array $options
 * @return array 
 */
function show_random_projects($options)
{
	$projectsModule = icms::handler("icms_module")->getByDirname('projects');
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
	$untagged_content = FALSE;
		
	include_once(ICMS_ROOT_PATH . '/modules/' . $projectsModule->getVar('dirname') . '/include/common.php');
	$projects_project_handler = icms_getModuleHandler('project', $projectsModule->getVar('dirname'), 'projects');
	
	// Check for dynamic tag filtering, including by untagged content
	if ($options[5] == 1 && isset($_GET['tag_id'])) {
		$untagged_content = ($_GET['tag_id'] == 'untagged') ? TRUE : FALSE;
		$options[3] = (int)trim($_GET['tag_id']);
	}
	
	if (icms_get_module_status("sprockets"))
	{
		icms_loadLanguageFile("sprockets", "common");
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	}
	
	$criteria = new icms_db_criteria_Compo();
	$projectList = $projects = array();

	// Get a list of projects filtered by tag
	if (icms_get_module_status("sprockets") && ($options[3] != 0 || $untagged_content))
	{
		$query = "SELECT `project_id` FROM " . $projects_project_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `project_id` = `iid`";
		if ($untagged_content) {
			$options[3] = 0;
		}
		$query .= " AND `tid` = '" . $options[3] . "'"
			. " AND `mid` = '" . $projectsModule->getVar('mid') . "'"
			. " AND `item` = 'project'"
			. " AND `online_status` = '1'";
		
		// Check whether to display current projects, completed projects, or both
		switch ($options[4])
		{
			case "0":
				$query .= " AND `complete` = '0'";
				break;
			case "1":
				" AND `complete` = '1'";
				break;
			default:
				// Complete is not used as a criteria; both current and completed projects will be returned
		}
		
		$query .= " ORDER BY `weight` ASC";

		$result = icms::$xoopsDB->query($query);

		if (!$result)
		{
			echo 'Error: Random projects block';
			exit;

		}
		else
		{
			$rows = $projects_project_handler->convertResultSet($result, TRUE, FALSE);
			foreach ($rows as $key => $row) 
			{
				$project_list[$key] = $row['project_id'];
			}
		}
	}
	// Otherwise just get a list of all projects
	else 
	{
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		
		// Check whether to display current projects, completed projects, or both
		switch ($options[4])
		{
			case "0":
				$criteria->add(new icms_db_criteria_Item('complete', '0'));
				break;
			case "1":
				$criteria->add(new icms_db_criteria_Item('complete', '1'));
				break;
			default:
				// Complete is not used as a criteria; both current and completed projects will be returned
		}
		$criteria->setSort('weight');
		$criteria->setOrder('ASC');
		$project_list = $projects_project_handler->getList($criteria);
		$project_list = array_flip($project_list);
	}
	
	// Pick random projects from the list, if the block preference is so set
	if ($options[1] == TRUE) 
	{
		shuffle($project_list);
	}
	
	// Cut the project list down to the number of required entries and set the IDs as criteria
	$project_list = array_slice($project_list, 0, $options[0], TRUE);
	$criteria->add(new icms_db_criteria_Item('project_id', '(' . implode(',', $project_list) . ')', 'IN'));
	$criteria->setSort('weight');
	$criteria->setOrder('ASC');
			
	// Retrieve the projects and assign them to the block
	$projects = $projects_project_handler->getObjects($criteria, TRUE, FALSE);
	
	// Need to shuffle them again as the DB will return them in order whether you like it or not
	if ($options[1] == TRUE)
	{
		shuffle($projects);
	}

	// Check if an 'updated' notice should be displayed. this works by comparing the time since the
	// project was last updated against the length of time that an updated notice should be shown 
	// (as set in the module preferences)
	
	if (icms_getConfig('projects_show_last_updated', $projectsModule->getVar('dirname')))
	{
		$update_periods = array(
			0 => 0,
			1 => 86400,		// Show updated notice for 1 day
			2 => 259200,	// Show updated notice for 3 days
			3 => 604800,	// Show updated notice for 1 week
			4 => 1209600,	// Show updated notice for 2 weeks
			5 => 1814400,	// Show updated notice for 3 weeks
			6 => 2419200	// Show updated notice for 4 weeks
			);
		$updated_notice_period = $update_periods[icms_getConfig('projects_updated_notice_period', $projectsModule->getVar('dirname'))];
	}
	
	// Check if updated notices and view counter should be shown
	// Update logo paths
	// Add SEO string to URL
	// Show view counter
	foreach ($projects as $key => &$object)
	{
		// Update notices
		if (icms_getConfig('projects_show_last_updated', $projectsModule->getVar('dirname')))
		{
			$updated = strtotime($object['date']);
			if ((time() - $updated) < $updated_notice_period)
			{
				$object['date'] = date(icms_getConfig('projects_date_format', $projectsModule->getVar('dirname')), $updated);
				$object['updated'] = TRUE;
			}
		}
		
		// Logo paths
		if (icms_getConfig('display_project_logos', $projectsModule->getVar('dirname')) == TRUE && !empty ($object['logo']))
		{
			$object['logo'] = ICMS_URL . '/uploads/' . $projectsModule->getVar('dirname') . '/project/' . $object['logo'];
		}
		else
		{
			unset($object['logo']);
		}
		
		// Add SEO friendly string to URL
		if (!empty($object['short_url']))
		{
			$object['itemUrl'] .= "&amp;title=" . $object['short_url'];
		}
		
		// View counter
		if (icms_getConfig('projects_show_view_counter') == FALSE)
		{
			unset($object['counter']);
		}
	}

	// Prepare tags. A list of project IDs is used to retrieve relevant taglinks. The taglinks
	// are sorted into a multidimensional array, using the project ID as the key to each subarray.
	// Then its just a case of assigning each subarray to the matching project.

	// Prepare a list of project_id, this will be used to create a taglink buffer, which is used
	// to create tag links for each project
	$linked_project_ids = '';
	foreach ($projects as $key => $value) {
		$linked_project_ids[] = $value['project_id'];
	}
	
	if (icms_get_module_status("sprockets") && !empty($linked_project_ids))
	{
		$linked_project_ids = '(' . implode(',', $linked_project_ids) . ')';
		
		// Get a reference array of tags
		$criteria = icms_buildCriteria(array('label_type' => '0'));
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_tag_buffer = $sprockets_tag_handler->getTagBuffer();

		// Prepare multidimensional array of tag_ids with project_id (iid) as key
		$taglink_buffer = $project_tag_id_buffer = array();
		$criteria = new  icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('mid', $projectsModule->getVar('mid')));
		$criteria->add(new icms_db_criteria_Item('item', 'project'));
		$criteria->add(new icms_db_criteria_Item('iid', $linked_project_ids, 'IN'));
		$taglink_buffer = $sprockets_taglink_handler->getObjects($criteria, TRUE, TRUE);
		unset($criteria);

		// Build tags, with URLs for navigation
		foreach ($taglink_buffer as $key => $taglink)
		{
			if (!array_key_exists($taglink->getVar('iid'), $project_tag_id_buffer))
			{
				$project_tag_id_buffer[$taglink->getVar('iid')] = array();
			}
			if ($taglink->getVar('tid') == 0) {
				$project_tag_id_buffer[$taglink->getVar('iid')][] = '<a href="' . ICMS_URL . '/modules/' 
					. $projectsModule->getVar('dirname') . '/project.php?tag_id=untagged">' 
					. $sprockets_tag_buffer[$taglink->getVar('tid')]
					. '</a>';
			} else {
				$project_tag_id_buffer[$taglink->getVar('iid')][] = '<a href="' . ICMS_URL . '/modules/' 
					. $projectsModule->getVar('dirname') . '/project.php?tag_id=' 
					. $taglink->getVar('tid') . '">' 
					. $sprockets_tag_buffer[$taglink->getVar('tid')]
					. '</a>';
			}
		}

		// Convert the tag arrays into strings for easy handling in the template
		foreach ($project_tag_id_buffer as $key => &$value) 
		{
			$value = implode(', ', $value);
		}

		// Assign each subarray of tags to the matching projects, using the item id as marker
		foreach ($projects as $key => &$value) 
		{
			if (!empty($project_tag_id_buffer[$value['project_id']]))
			{
				$value['tags'] = $project_tag_id_buffer[$value['project_id']];
			}
		}
	}
	
	// Assign to template
	$block['random_projects'] = $projects;
	$block['show_logos'] = $options[2];
	$block['projects_logo_block_display_width'] = icms_getConfig('projects_logo_block_display_width', $projectsModule->getVar('dirname'));
	if (icms_getConfig('project_logo_position', $projectsModule->getVar('dirname')) == 1) // Align right
	{
		$block['project_logo_position'] = 'float:right; margin: 0em 0em 1em 1em;';
	}
	else // Align left
	{
		$block['project_logo_position'] = 'float:left; margin: 0em 1em 1em 0em;';
	}
	$block['projects_freestyle_logo_dimensions'] = icms_getConfig('projects_freestyle_logo_dimensions', $projectsModule->getVar('dirname'));

	return $block;
}

/**
 * Edit recent projects block options
 *
 * @param array $options
 * @return string 
 */
function edit_random_projects($options) 
{
	$projectsModule = icms::handler("icms_module")->getByDirname('projects');
	include_once(ICMS_ROOT_PATH . '/modules/' . $projectsModule->getVar('dirname') . '/include/common.php');
	$projects_project_handler = icms_getModuleHandler('project', $projectsModule->getVar('dirname'), 'projects');
	
	// Select number of random projects to display in the block
	$form = '<table>';
	$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[0]" value="' . $options[0] . '"/></td></tr>';
	
	// Randomise the projects? NB: Only works if you do not cache the block
	$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_OR_FIXED . '</td>';
	$form .= '<td><input type="radio" name="options[1]" value="1"';
	if ($options[1] == 1) 
	{
		$form .= ' checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_YES;
	$form .= '<input type="radio" name="options[1]" value="0"';
	if ($options[1] == 0) 
	{
		$form .= 'checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_NO . '</td></tr>';	
	
	// Show project logos, or just a simple list?
	$form .= '<tr><td>' . _MB_PROJECTS_LOGOS_OR_LIST . '</td>';
	$form .= '<td><input type="radio" name="options[2]" value="1"';
	if ($options[2] == 1) 
	{
		$form .= ' checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_YES;
	$form .= '<input type="radio" name="options[2]" value="0"';
	if ($options[2] == 0) 
	{
		$form .= 'checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_NO . '</td></tr>';		
	
	// Optionally display results from a single tag - but only if sprockets module is installed
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");

	if (icms_get_module_status("sprockets"))
	{
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
		
		// Get only those tags that contain content from this module
		$criteria = '';
		$relevant_tag_ids = array();
		$criteria = icms_buildCriteria(array('mid' => $projectsModule->getVar('mid')));
		$projects_module_taglinks = $sprockets_taglink_handler->getObjects($criteria, TRUE, TRUE);
		foreach ($projects_module_taglinks as $key => $value)
		{
			$relevant_tag_ids[] = $value->getVar('tid');
		}
		$relevant_tag_ids = array_unique($relevant_tag_ids);
		$relevant_tag_ids = '(' . implode(',', $relevant_tag_ids) . ')';
		unset($criteria);

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $relevant_tag_ids, 'IN'));
		$criteria->add(new icms_db_criteria_Item('label_type', '0'));
		$tagList = $sprockets_tag_handler->getList($criteria);

		$tagList = array(0 => _MB_PROJECTS_RANDOM_ALL) + $tagList;
		$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_TAG . '</td>';
		// Parameters icms_form_elements_Select: ($caption, $name, $value = null, $size = 1, $multiple = TRUE)
		$form_select = new icms_form_elements_Select('', 'options[3]', $options[3], '1', FALSE);
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
	}
	
	// Display current projects, completed projects, or both?
	$show_type_options = array(0 => _MB_PROJECTS_RANDOM_SHOW_CURRENT, 
		1 => _MB_PROJECTS_RANDOM_SHOW_COMPLETED, 
		2 => _MB_PROJECTS_RANDOM_SHOW_BOTH);
	
	$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_SHOW_TYPE . '</td>';
	$form_select = new icms_form_elements_Select('', 'options[4]', $options[4], '1', FALSE);
	$form_select->addOptionArray($show_type_options);
	$form .= '<td>' . $form_select->render() . '</td></tr>';
	
	// Dynamic tagging (overrides static tag filter)
	$form .= '<tr><td>' . _MB_PROJECTS_DYNAMIC_TAG . '</td>';			
	$form .= '<td><input type="radio" name="options[5]" value="1"';
	if ($options[5] == 1) {
		$form .= ' checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_PROJECT_YES;
	$form .= '<input type="radio" name="options[5]" value="0"';
	if ($options[5] == 0) {
		$form .= 'checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_PROJECT_NO . '</td></tr>';
	$form .= '</table>';
	
	return $form;
}