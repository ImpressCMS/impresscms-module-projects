<?php

// Test code only

include_once "header.php";

$xoopsOption["template_main"] = "projects_project.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Looking for a safe way to test if the Sprockets module has been installed. The issue is that 
// if installed but deactivated, IPF still tries to load the handlers and this is causing a fatal
// error. 

// Run this code with Sprockets installed but deactivated.

$sprocketsModule = null;

$sprocketsModule = icms_getModuleInfo("sprockets");
echo 'ok';

/*
try 
{
	$sprocketsModule = icms_getModuleInfo("sprockets");
} 
catch (Exception $e) 
{
	// Continue
}

// Result: Even though it is deactivated, $sprocketsModule is set.
if ($sprocketsModule)
{
	echo 'Module exists<br />';
}
else
{
	echo 'Module does not exist<br />';
}

if ($sprocketsModule &&  $sprocketsModule->getVar("isactive", "e") == 1)
{
	echo 'Module exists and is active<br />';
}
else
{
	echo 'Module exists and is deactivated<br />';
}
 */
	
include_once "footer.php";