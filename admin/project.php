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
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");

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

$clean_op = "";
$projects_project_handler = icms_getModuleHandler("project", basename(dirname(dirname(__FILE__))), "projects");
$valid_op = array ("mod", "changedField", "addproject", "del", "view", "changeWeight", "changeComplete", "visible", "");

// Sanitise input parameters
$clean_project_id = isset($_GET["project_id"]) ? (int)$_GET["project_id"] : 0 ;
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET['tag_id']) ? (int)$_GET['tag_id'] : 0 ;
if (isset($_GET["op"])) $clean_op = htmlentities($_GET["op"]);
if (isset($_POST["op"])) $clean_op = htmlentities($_POST["op"]);

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
				$changed = TRUE;
				$itemObj = $projects_project_handler->get($value);

				if ($itemObj->getVar('weight', 'e') != $_POST['weight'][$key])
				{
					$itemObj->setVar('weight', intval($_POST['weight'][$key]));
					$changed = TRUE;
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
			$visibility = $projects_project_handler->toggleOnlineStatus($clean_project_id, 'online_status');
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
			$completionStatus = $projects_project_handler->toggleCompletion($clean_project_id, 'complete');
			$ret = '/modules/' . basename(dirname(dirname(__FILE__))) . '/admin/project.php';
			if ($completionStatus == 0)
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_ACTIVE);
			}
			else 
			{
				redirect_header(ICMS_URL . $ret, 2, _AM_PROJECTS_PROJECT_COMPLETED);
			}
			break;

		default:
			icms_cp_header();
			$icmsModule->displayAdminMenu(0, _AM_PROJECTS_PROJECTS);
			
			// Display a single project, if a project_id is set
			if ($clean_project_id)
			{
				$projectObj = $projects_project_handler->get($clean_project_id);
				$projectObj->displaySingleObject();
			}
			
			// Display a tag select filter (if the Sprockets module is installed)
			if (icms_get_module_status("sprockets")) {

				$tag_select_box = '';
				$taglink_array = $tagged_article_list = array();
				$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
				$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('project.php', $clean_tag_id,
					_AM_PROJECTS_PROJECT_ALL_PROJECTS, FALSE, icms::$module->getVar('mid'));
				
				if ($untagged_content) {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('project.php', 'untagged',
					_AM_PROJECTS_PROJECT_ALL_PROJECTS, FALSE, icms::$module->getVar('mid'),
						'project', TRUE);
				} else {
					$tag_select_box = $sprockets_tag_handler->getTagSelectBox('project.php',
							$clean_tag_id, _AM_PROJECTS_ALL_PROJECTS, FALSE,
							icms::$module->getVar('mid'), 'project', TRUE);
				}
				
				if (!empty($tag_select_box)) {
					echo '<h3>' . _AM_PROJECTS_PROJECT_FILTER_BY_TAG . '</h3>';
					echo $tag_select_box;
				}

				if ($untagged_content || $clean_tag_id) {

					// get a list of project IDs belonging to this tag
					$criteria = new icms_db_criteria_Compo();
					if ($untagged_content) {
						$criteria->add(new icms_db_criteria_Item('tid', 0));
					} else {
						$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
					}
					$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
					$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
					$criteria->add(new icms_db_criteria_Item('item', 'project'));
					$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
					foreach ($taglink_array as $taglink) {
						$tagged_project_list[] = $taglink->getVar('iid');
					}
					$tagged_project_list = "('" . implode("','", $tagged_project_list) . "')";

					// Use the list to filter the persistable table
					$criteria = new icms_db_criteria_Compo();
					$criteria->add(new icms_db_criteria_Item('project_id', $tagged_project_list, 'IN'));
				}
			}
			
			if (empty($criteria)) {
				$criteria = null;
			}
			
			$objectTable = new icms_ipf_view_Table($projects_project_handler, $criteria);
			$objectTable->addQuickSearch('title');
			$objectTable->addColumn(new icms_ipf_view_Column("complete", "center", TRUE));
			$objectTable->addColumn(new icms_ipf_view_Column("title"));
			$objectTable->addColumn(new icms_ipf_view_Column("date"));
			$objectTable->addColumn(new icms_ipf_view_Column("last_update"));
			$objectTable->addColumn(new icms_ipf_view_Column("counter"));
			$objectTable->addColumn(new icms_ipf_view_Column('weight', 'center', TRUE, 'getWeightControl'));
			$objectTable->addColumn(new icms_ipf_view_Column("online_status", "center", TRUE));
			$objectTable->setDefaultSort('date');
			$objectTable->setDefaultOrder('DESC');
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