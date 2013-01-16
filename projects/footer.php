<?php
/**
 * Footer page included at the end of each page on user side of the mdoule
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

$icmsTpl->assign("projects_adminpage", "<a href='" . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . "/admin/index.php'>" ._MD_PROJECTS_ADMIN_PAGE . "</a>");
$icmsTpl->assign("projects_is_admin", icms_userIsAdmin(PROJECTS_DIRNAME));
$icmsTpl->assign('projects_url', PROJECTS_URL);
$icmsTpl->assign('projects_images_url', PROJECTS_IMAGES_URL);

$xoTheme->addStylesheet(PROJECTS_URL . 'module' . ((defined("_ADM_USE_RTL") && _ADM_USE_RTL) ? '_rtl' : '') . '.css');

include_once ICMS_ROOT_PATH . '/footer.php';