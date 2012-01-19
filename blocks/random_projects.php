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
			
	// Retrieve the projects and assign them to the block - need to shuffle a second time
	$projects = $projects_project_handler->getObjects($criteria, TRUE, FALSE);
	shuffle($projects);

	// Adjust the logo paths
	foreach ($projects as $key => &$object)
	{
		$object['logo'] = ICMS_URL . '/uploads/' . $projectsModule->getVar('dirname') . '/project/' . $object['logo'];
	}
	
	// Assign to template
	$block['random_projects'] = $projects;
	$block['show_logos'] = $options[3];
	$block['logo_block_display_width'] = icms_getConfig('logo_block_display_width', $projectsModule->getVar('dirname'));

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