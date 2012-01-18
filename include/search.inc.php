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

function projects_search($queryarray, $andor, $limit, $offset, $userid)
{
	$projects_project_handler = icms_getModuleHandler("project", basename(dirname(dirname(__FILE__))), "projects");
	$projectArray = $projects_project_handler->getProjectsForSearch($queryarray, $andor, $limit, $offset, $userid);
	$ret = array();

	foreach ($projectArray as $project) 
	{
		$item['image'] = "images/project.png";
		$item['link'] = $project->getItemLink(TRUE);
		$item['title'] = $project->getVar("title");
		$item['time'] = $project->getVar("date", "e");
		$item['uid'] = $project->getVar("creator");
		$ret[] = $item;
		unset($item);
	}

	return $ret;
}