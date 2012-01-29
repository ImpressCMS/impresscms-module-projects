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
 * Modified for use in the Podcast module by Madfish
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepare random projects block for display
 *
 * @param array $options
 * @return array 
 */
function show_random_projects($options)
{
	$projectsModule = icms_getModuleInfo('projects');
	$sprocketsModule = icms_getModuleInfo('sprockets');
	include_once(ICMS_ROOT_PATH . '/modules/' . $projectsModule->getVar('dirname') . '/include/common.php');
	$projects_project_handler = icms_getModuleHandler('project', $projectsModule->getVar('dirname'), 'projects');
	
	if ($sprocketsModule)
	{
		icms_loadLanguageFile("sprockets", "common");
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	}
	
	$criteria = new icms_db_criteria_Compo();
	$projectList = $projects = array();

	// Get a list of projects filtered by tag
	if ($sprocketsModule && $options[1] != 0)
	{
		$query = "SELECT `project_id` FROM " . $projects_project_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `project_id` = `iid`"
			. " AND `tid` = '" . $options[1] . "'"
			. " AND `mid` = '" . $projectsModule->getVar('mid') . "'"
			. " AND `item` = 'project'"
			. " AND `online_status` = '1'"
			. " AND `complete` = '0'"
			. " ORDER BY `weight` ASC";

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
		$criteria->add(new icms_db_criteria_Item('complete', '0'));
		$criteria->setSort('weight');
		$criteria->setOrder('ASC');
		$project_list = $projects_project_handler->getList($criteria);
		$project_list = array_flip($project_list);
	}
	
	// Pick random projects from the list, if the block preference is so set
	if ($options[2] == TRUE) 
	{
		shuffle($project_list);
	}
	
	// Cut the project list down to the number of required entries and set the IDs as criteria
	$project_list = array_slice($project_list, 0, $options[0], TRUE);
	$criteria->add(new icms_db_criteria_Item('project_id', '(' . implode(',', $project_list) . ')', 'IN'));
			
	// Retrieve the projects and assign them to the block
	$projects = $projects_project_handler->getObjects($criteria, TRUE, FALSE);
	
	// Need to shuffle them again as the DB will return them in order whether you like it or not
	if ($options[2] == TRUE)
	{
		shuffle($projects);
	}

	// Check if an 'updated' notice should be displayed. this works by comparing the time since the
	// project was last updated against the length of time that an updated notice should be shown 
	// (as set in the module preferences)
	
	if (icms_getConfig('show_last_updated', $projectsModule->getVar('dirname')))
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
		$updated_notice_period = $update_periods[icms_getConfig('updated_notice_period', $projectsModule->getVar('dirname'))];
	}
	
	// Check if updated notices and view counter should be shown; update logo paths;
	foreach ($projects as $key => &$object)
	{
		// Update notices
		if (icms_getConfig('show_last_updated', $projectsModule->getVar('dirname')))
		{
			$updated = strtotime($object['date']);
			if ((time() - $updated) < $updated_notice_period)
			{
				$object['date'] = date(icms_getConfig('date_format', $projectsModule->getVar('dirname')), $updated);
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
		
		// View counter
		if (icms_getConfig('show_view_counter') == FALSE)
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
	
	if ($sprocketsModule && !empty($linked_project_ids))
	{
		$linked_project_ids = '(' . implode(',', $linked_project_ids) . ')';
		
		// Get a reference array of tags
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_tag_buffer = $sprockets_tag_handler->getObjects(NULL, TRUE, FALSE);

		// Prepare multidimensional array of tag_ids with project_id (iid) as key
		$taglink_buffer = $project_tag_id_buffer = array();
		$criteria = new  icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
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
			$project_tag_id_buffer[$taglink->getVar('iid')][] = '<a href="' . ICMS_URL . '/modules/' 
					. $projectsModule->getVar('dirname') . '/project.php?tag_id=' 
					. $taglink->getVar('tid') . '">' 
					. $sprockets_tag_buffer[$taglink->getVar('tid')]['title']
					. '</a>';
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
	$block['show_logos'] = $options[3];
	$block['logo_block_display_width'] = icms_getConfig('logo_block_display_width', $projectsModule->getVar('dirname'));
	if (icms_getConfig('project_logo_position', $projectsModule->getVar('dirname') == 1)) // Align right
	{
		$block['project_logo_position'] = 'float:right; margin: 0em 0em 1em 1em;';
	}
	else // Align left
	{
		$block['project_logo_position'] = 'float:left; margin: 0em 1em 1em 0em;';
	}
	$block['freestyle_logo_dimensions'] = icms_getConfig('freestyle_logo_dimensions', $projectsModule->getVar('dirname'));

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
	$projectsModule = icms_getModuleInfo('projects');
	include_once(ICMS_ROOT_PATH . '/modules/' . $projectsModule->getVar('dirname') . '/include/common.php');
	include_once(ICMS_ROOT_PATH . '/class/xoopsform/formselect.php');
	$projects_project_handler = icms_getModuleHandler('project', $projectsModule->getVar('dirname'), 'projects');
	
	// Select number of random projects to display in the block
	$form = '<table><tr>';
	$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[0]" value="' . $options[0] . '"/></td>';
	
	// Optionally display results from a single tag - but only if sprockets module is installed
	$sprocketsModule = icms_getModuleInfo('sprockets');
	if ($sprocketsModule)
	{
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_TAG . '</td>';
		// Parameters XoopsFormSelect: ($caption, $name, $value = null, $size = 1, $multiple = false)
		$form_select = new XoopsFormSelect('', 'options[1]', $options[1], '1', FALSE);
		$tagList = $sprockets_tag_handler->getList();
		$tagList = array(0 => _MB_PROJECTS_RANDOM_ALL) + $tagList;
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
	}
	
	// Randomise the projects? NB: Only works if you do not cache the block
	$form .= '<tr><td>' . _MB_PROJECTS_RANDOM_OR_FIXED . '</td>';
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
	
	// Show project logos, or just a simple list?
	$form .= '<tr><td>' . _MB_PROJECTS_LOGOS_OR_LIST . '</td>';
	$form .= '<td><input type="radio" name="options[3]" value="1"';
	if ($options[3] == 1) 
	{
		$form .= ' checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_YES;
	$form .= '<input type="radio" name="options[3]" value="0"';
	if ($options[3] == 0) 
	{
		$form .= 'checked="checked"';
	}
	$form .= '/>' . _MB_PROJECTS_RANDOM_NO . '</td></tr>';
	$form .= '</table>';
	
	return $form;
}