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
	 * Toggles a TRUE/TRUE field and updates the object
	 *
	 * @return null
	 */
	public function changeStatus($id, $field)
	{
		$status = '';
		
		// Load the object that will be manipulated
		$projectObj = $this->get($id);
		$projectObj->loadTags();
		
		// Change the relevant field
		if ($projectObj->getVar($field, 'e') == 1) {
			$projectObj->setVar($field, 0);
			$status = 0;
		} else {
			$projectObj->setVar($field, 1);
			$status = 1;
		}
		$this->insert($projectObj, TRUE);
		
		return $status;
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
		
		$criteria->add(new icms_db_criteria_Item('online_status', TRUE));
		
		return $this->getObjects($criteria, TRUE, TRUE);
	}
	
	/**
	 * Stores tags when a project is inserted or updated
	 *
	 * @param object $obj ProjectsProject object
	 * @return bool
	 */
	protected function afterSave(& $obj)
	{
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");

		if (icms_get_module_status("sprockets")) 
		{		
			$sprockets_taglink_handler = '';
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->storeTagsForObject($obj);
		}
		return TRUE;
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
		
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
		if (icms_get_module_status("sprockets"))
		{
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->deleteAllForObject($obj);
		}

		return TRUE;
	}
}
