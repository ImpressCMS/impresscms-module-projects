<?php
/**
 * Classes responsible for managing projects project objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_projects_ProjectHandler extends icms_ipf_Handler
{
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db database connection object
	 */
	public function __construct(&$db)
	{
		parent::__construct($db, "project", "project_id", "title", "description", "projects");
		$this->enableUpload(array("image/gif", "image/jpeg", "image/pjpeg", "image/png"), 512000, 800, 600);
	}

	/**
	 * Toggles a true/false field and updates the object
	 *
	 * @return null
	 */
	public function changeStatus($id, $field)
	{
		$status = '';
		$projectObj = $this->get($id);
		if ($projectObj->getVar($field, 'e') == true) {
			$projectObj->setVar($field, 0);
			$status = 0;
		} else {
			$projectObj->setVar($field, 1);
			$status = 1;
		}
		$this->insert($projectObj, true);
		
		return $visibility;
	}
	
	/**
	 * Converts complete value to human readable text
	 *
	 * @return array
	 */
	public function complete_filter()
	{
		return array(0 => _AM_PROJECTS_PROJECT_NO, 1 => _AM_PROJECTS_PROJECT_YES);
	}

	/**
	 * Converts status value to human readable text
	 *
	 * @return array
	 */
	public function online_status_filter()
	{
		return array(0 => _AM_PROJECTS_PROJECT_OFFLINE, 1 => _AM_PROJECTS_PROJECT_ONLINE);
	}
	
	/**
	 * Provides the global search functionality for the Projects module
	 *
	 * @param array $queryarray
	 * @param string $andor
	 * @param int $limit
	 * @param int $offset
	 * @param int $userid
	 * @return array 
	 */
	public function getProjectsForSearch($queryarray, $andor, $limit, $offset, $userid)
	{		
		$criteria = new icms_db_criteria_Compo();
		$criteria->setStart($offset);
		$criteria->setLimit($limit);
		$criteria->setSort('title');
		$criteria->setOrder('ASC');

		if ($userid != 0) 
		{
			$criteria->add(new icms_db_criteria_Item('submitter', $userid));
		}
		
		if ($queryarray) 
		{
			$criteriaKeywords = new icms_db_criteria_Compo();
			for ($i = 0; $i < count($queryarray); $i++) {
				$criteriaKeyword = new icms_db_criteria_Compo();
				$criteriaKeyword->add(new icms_db_criteria_Item('title', '%' . $queryarray[$i] . '%',
					'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('description', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset ($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		
		$criteria->add(new icms_db_criteria_Item('online_status', true));
		
		return $this->getObjects($criteria, true, true);
	}
	
	/**
	 * Stores tags when a project is inserted or updated
	 *
	 * @param object $obj ProjectsProject object
	 * @return bool
	 */
	protected function afterSave(& $obj)
	{
		
		$sprockets_taglink_handler = '';
		$sprocketsModule = icms_getModuleInfo('sprockets');
		
		if ($sprocketsModule) 
		{
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->storeTagsForObject($obj);
		}

		return true;
	}
	
	/**
	 * Deletes taglinks when a project is deleted
	 *
	 * @param object $obj ProjectsProject object
	 * @return bool
	 */
	protected function afterDelete(& $obj) 
	{	
		$sprocketsModule = $notification_handler = $module_handler = $module = $module_id
				= $category = $item_id = '';
		
		$sprocketsModule = icms_getModuleInfo('sprockets');
		
		if ($sprocketsModule)
		{
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->deleteAllForObject(&$obj);
		}

		return true;
	}
}