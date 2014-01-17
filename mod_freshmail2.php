<?php
/**
 * @package     Freshmail.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

// Initialize variables
$app = JFactory::getApplication();
$input = $app->input;

// Check required params
if (!$params->get('FMapiKey')
	|| !$params->get('FMapiSecret')
	|| !$params->get('FMlistHash'))
{
	$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_ERROR_PARAMS_MISSING'), 'error');

	return;
}

// Get parameters
$control			= 'mod_' . $module->id;
$inputData			= $input->post->get($control, null, 'array');

// POSTed data (Valid, Added, Notified)
if (!empty($inputData)
	&& ModFreshmail2Helper::validate($inputData, $params)
	&& ModFreshmail2Helper::addContact($inputData, $params)
	&& ModFreshmail2Helper::sendEmail($inputData, $params))
{
	// All OK
}


// Get TOS link
$tosLink			= ModFreshmail2Helper::getMenuLink($params->get('tos_menuitem'));


// Get list of custom fields
$customFields 		= ModFreshmail2Helper::getCustomFields($params);

// Escape Modeuleclass Suffix
$moduleclass_sfx 	= htmlspecialchars($params->get('moduleclass_sfx'));

// Load layout
require JModuleHelper::getLayoutPath('mod_freshmail2', $params->get('layout', 'twbs23'));
