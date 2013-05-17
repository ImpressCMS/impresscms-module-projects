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

function projects_search($queryarray, $andor, $limit, $offset = 0, $userid = 0)
{
	global $icmsConfigSearch;
	
	$projectsArray = $ret = array();
	$count = $number_to_process = $projects_left = '';
	
	$projects_project_handler = icms_getModuleHandler("project", basename(dirname(dirname(__FILE__))), "projects");
	$projectsArray = $projects_project_handler->getProjectsForSearch($queryarray, $andor, $limit, $offset, $userid);
	
	// Count the number of records
	$count = count($projectsArray);
	
	// The number of records actually containing project objects is <= $limit, the rest are padding
	$projects_left = ($count - ($offset + $icmsConfigSearch['search_per_page']));
	if ($projects_left < 0) {
		$number_to_process = $icmsConfigSearch['search_per_page'] + $projects_left; // $projects_left is negative
	} else {
		$number_to_process = $icmsConfigSearch['search_per_page'];
	}
	
	// Process the actual projects (not the padding)
	for ($i = 0; $i < $number_to_process; $i++)
	{
		if (is_object($projectsArray[$i])) { // Required to prevent crashing on profile view
			$item['image'] = "images/project.png";
			$item['link'] = $projectsArray[$i]->getItemLink(TRUE);
			$item['title'] = $projectsArray[$i]->getVar("title");
			$item['time'] = $projectsArray[$i]->getVar("date", "e");
			$item['uid'] = $projectsArray[$i]->getVar("creator");
			$ret[] = $item;
			unset($item);
		}
	}

	// Restore the padding (required for 'hits' information and pagination controls). The offset
	// must be padded to the left of the results, and the remainder to the right or else the search
	// pagination controls will display the wrong results (which will all be empty).
	// Left padding = -($limit + $offset)
	$ret = array_pad($ret, -($offset + $number_to_process), 1);
	
	// Right padding = $count
	$ret = array_pad($ret, $count, 1);
	
	return $ret;
}