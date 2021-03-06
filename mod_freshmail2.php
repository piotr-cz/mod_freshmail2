<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2018 Piotr Konieczny. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

// Initialize variables
$app = JFactory::getApplication();
$input = $app->input;
$jversion = new JVersion();

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

// Get posted data
$inputData			= $input->post->get($control, null, 'array');

// Default form values
$stateValues		= array(
	'email'	=> '',
	'tos' => false,
	'custom_fields' => array(),
);

// Limits (not on POST)
if (empty($inputData) && ModFreshmail2Helper::canSkip($control, $params))
{
	return;
}


// Get processed lists set
$selectedLists = (isset($inputData['list'])) ? (array) $inputData['list'] : array();
$lists = ModFreshmail2Helper::getProcessedLists($params, $selectedLists);

// Process POSTed data (Valid, Added, Notified)
if (!empty($inputData) && ModFreshmail2Helper::validate($inputData, $params))
{
	// Success flag
	$addContactsSuccess = false;

	// Loop trough lists and process selected ones
	foreach ($lists as $list)
	{
		if ($list->selected)
		{
			// Validate, add contact
			if (!ModFreshmail2Helper::addContact($inputData, $params, $list))
			{
				$addContactsSuccess = false;
				break;
			}

			$addContactsSuccess = true;
		}
	}

	// Run post hooks
	if ($addContactsSuccess)
	{
		ModFreshmail2Helper::sendEmail($inputData, $params)
			&& ModFreshmail2Helper::postHook($control, $params);

		if ($params->get('redirectMenuitem'))
		{
			ModFreshmail2Helper::redirect($params);
		}
	}
	// Hand over form data to layout so user may try again
	else
	{
		$stateValues = $inputData;
	}
}


// Get TOS link
$tosLink			= ModFreshmail2Helper::getMenuLink($params->get('tos_menuitem'));

// Determine Ajax support
$isAjaxEnabled		= (is_dir(JPATH_SITE . '/components/com_ajax') && $params->get('ajax_enabled', 0));

if ($isAjaxEnabled)
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'system/core.js', false, true);
	JHtml::_('script', 'mod_freshmail2/submit.js', false, true);
}

// Get list of custom fields
$customFields		= ModFreshmail2Helper::getProcessedCustomFields($params);


/*
 * Hack to check if inside com_content context (injected using {loadposition/ loadmodule } shortcode).
 * There is a risk that emailcloak plugin is on (by default it is) so let's disable it for current article.
 * Another solution would be to skip setting state.
 * Bug report: https://github.com/joomla/joomla-cms/issues/5080
 */
$isEmailCloakActive	= (isset($scope) && $scope == 'com_content' && JPluginHelper::isEnabled('content', 'emailcloak'));

// Escape Modeuleclass Suffix
$moduleclass_sfx	= htmlspecialchars($params->get('moduleclass_sfx'));

// Capcha
$captcha = ModFreshmail2Helper::getCaptcha($params);

// Load layout
require JModuleHelper::getLayoutPath('mod_freshmail2', $params->get('layout', 'twbs23'));
