<?php
/**
 * Admin page to manage projects
 *
 * List, add, edit and delete project objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

/**
 * Edit a Project
 *
 * @param int $project_id project to be edited
*/
function editproject($project_id = 0)
{
	global $projects_project_handler, $icmsModule, $icmsAdminTpl;

	$projectObj = $projects_project_handler->get($project_id);

	if (!$projectObj->isNew())
	{
		$projectObj->loadTags();
		$icmsModule->displayAdminMenu(0, _AM_PROJECTS_PROJECTS . " > " . _CO_ICMS_EDITING);
		$sform = $projectObj->getForm(_AM_PROJECTS_PROJECT_EDIT, "addproject");
		$sform->assign($icmsAdminTpl);
	}
	else
	{
		$icmsModule->displayAdminMenu(0, _AM_PROJECTS_PROJECTS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $projectObj->getForm(_AM_PROJECTS_PROJECT_CREATE, "addproject");
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display("db:projects_admin_project.html");
}

include_once "admin_header.php";

$projects_project_handler = icms_getModuleHandler("project", basename(dirname(dirname(__FILE__))), "projects");
/** Use a naming convention that indicates the source of the content of the variable */
$clean_op = "";
/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ("mod", "changedField", "addproject", "del", "view", "changeWeight", "changeComplete", "visible", "");

if (isset($_GET["op"])) $clean_op = htmlentities($_GET["op"]);
if (isset($_POST["op"])) $clean_op = htmlentities($_POST["op"]);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_project_id = isset($_GET["project_id"]) ? (int)$_GET["project_id"] : 0 ;

/**
 * in_array() is a native PHP function that will determine if the value of the
 * first argument is found in the array listed in the second argument. Strings
 * are case sensitive and the 3rd argument determines whether type matching is
 * required
*/
if (in_array($clean_op, $valid_op, TRUE))
{
	switch ($clean_op)
	{
		case "mod":
		case "changedField":
			icms_cp_header();
			editproject($clean_project_id);
			break;

		case "addproject":
			$controller = new icms_ipf_Controller($projects_project_handler);
			$controller->storeFromDefaultForm(_AM_PROJECTS_PROJECT_CREATED, _AM_PROJECTS_PROJECT_MODIFIED);
			break;

		case "del":
			$controller = new icms_ipf_Controller($projects_project_handler);
			$controller->handleObjectDeletion();
			break;

		case "view":
			$projectObj = $projects_project_handler->get($clean_project_id);
			icms_cp_header();
			$projectObj->displaySingleObject();
			break;
		
		case "changeWeight":
			foreach ($_POST['mod_projects_Project_objects'] as $key => $value)
			{
				$changed = false;
				$itemObj = $projects_project_handler->get($value);

				if ($itemObj->getVar('weight', 'e') != $_POST['weight'][$key])
				{
					$itemObj->setVar('weight', intval($_POST['weight'][$key]));
					$changed = true;
				}
				if ($changed)
				{
					$projects_project_handler->insert($itemObj);
				}
			}
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/project.php';
			redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_WEIGHTS_UPDATED);
			break;
			
		case "visible":
			$visibility = $projects_project_handler->changeStatus($clean_project_id, 'online_status');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/project.php';
			if ($visibility == 0)
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_INVISIBLE);
			} 
			else
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_VISIBLE);
			}
			break;
			
		case "changeComplete":
			$completionStatus = $projects_project_handler->changeStatus($clean_project_id, 'complete');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/project.php';
			if ($visibility == 0)
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_COMPLETED);
			}
			else 
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_ACTIVE);
			}
			break;

		default:
			icms_cp_header();
			$icmsModule->displayAdminMenu(0, _AM_PROJECTS_PROJECTS);
			$objectTable = new icms_ipf_view_Table($projects_project_handler);
			$objectTable->addColumn(new icms_ipf_view_Column("complete", "center", TRUE));
			$objectTable->addColumn(new icms_ipf_view_Column("title"));
			$objectTable->addColumn(new icms_ipf_view_Column('weight', 'center', TRUE, 'getWeightControl'));
			$objectTable->addColumn(new icms_ipf_view_Column("online_status", "center", TRUE));
			$objectTable->addIntroButton("addproject", "project.php?op=mod", _AM_PROJECTS_PROJECT_CREATE);
			$objectTable->addActionButton("changeWeight", FALSE, _SUBMIT);
			$objectTable->addFilter('complete', 'complete_filter');
			$objectTable->addFilter('online_status', 'online_status_filter');
			$icmsAdminTpl->assign("projects_project_table", $objectTable->fetch());
			$icmsAdminTpl->display("db:projects_admin_project.html");
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */