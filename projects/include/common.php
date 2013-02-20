<?php
/**
 * Common file of the module included on all pages of the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		projects
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

if (!defined("PROJECTS_DIRNAME")) define("PROJECTS_DIRNAME", $modversion["dirname"] = basename(dirname(dirname(__FILE__))));
if (!defined("PROJECTS_URL")) define("PROJECTS_URL", ICMS_URL."/modules/".PROJECTS_DIRNAME."/");
if (!defined("PROJECTS_ROOT_PATH")) define("PROJECTS_ROOT_PATH", ICMS_ROOT_PATH."/modules/".PROJECTS_DIRNAME."/");
if (!defined("PROJECTS_IMAGES_URL")) define("PROJECTS_IMAGES_URL", PROJECTS_URL."images/");
if (!defined("PROJECTS_ADMIN_URL")) define("PROJECTS_ADMIN_URL", PROJECTS_URL."admin/");

// Include the common language file of the module
icms_loadLanguageFile("projects", "common");