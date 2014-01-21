<?php
/**
 * @package     Freshmail.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Import dependencies
JLoader::register('JFmRestApi', __DIR__ . '/libraries/Freshmail/FmRestApi.php');

/**
 * Helper for mod_freshmail2
 *
 * @package     Freshmail.Site
 * @subpackage  mod_freshmail2
 * @since       1.0
 */
class ModFreshmail2Helper
{
	/**
	 * Validate user input
	 *
	 * @param   array      $data    Data to validate
	 * @param   JRegistry  $params  Module parameters
	 *
	 * @return  boolean  True on success false on failure
	 */
	public static function validate(array $data, JRegistry $params)
	{
		$app = JFactory::getApplication();

		// CRSF token
		if (!JSession::checkToken('post'))
		{
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');

			return false;
		}

		// Terms of Service
		if ($params->get('tos_menuitem') && empty($data['tos']))
		{
			$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_ERROR_TOS'), 'error');

			return false;
		}

		// Email
		if (!JMailHelper::isEmailAddress($data['email']))
		{
			$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_ERROR_EMAIL'), 'error');

			return false;
		}

		// Validate custom fields
		$validates = true;
		$customFields = static::getCustomFields($params);

		foreach ($customFields as $field)
		{
			if ($field->required && empty($data['custom_fields'][$field->tag]))
			{
				$app->enqueueMessage(JText::sprintf('MOD_FRESHMAIL2_ERROR_FIELD', $field->name), 'notice');

				$validates = false;
			}
		}

		return $validates;
	}

	/**
	 * Check if can skip module rendering
	 *
	 * @param   string     $control
	 * @param   JRegistry  $params
	 *
	 * @return  boolean  True to skip module rendering
	 *
	 * @note    Doesn't use on cookie: Possible read/write conflict with
	 *          multiple modules as JInput doesn't throttle IO operations 
	 *          till request EOL.
	 */
	public static function canSkip($control, JRegistry $params)
	{
		// Get parameters
		$limit_time = $params->get('limit_time', 0);
		$limit_count = $params->get('limit_count', 0);
		$limit_registered = $params->get('limit_registered', 0);

		// No cookies required
		if (!$limit_time && !$limit_count && !$limit_registered)
		{
			return false;
		}

		// Read cookie
		$inputCookie = JFactory::getApplication()->input->cookie;
		$dataString = $inputCookie->get('freshmail2_' . $control, null, 'string');

		// Decode data
		$data = ($dataString) ? json_decode($dataString) : (object) array('time' => 0, 'count' => 0, 'state' => 0);

		// Limit multi-registrations
		if ($limit_registered && $data->registered)
		{
			return true;
		}

		// Check time limit vs last shown
		if ($limit_time)
		{
			if ($data->time && $limit_time <= (time() - $data->time) / 60)
			{
				return true;
			}
			else if (!$data->time)
			{
				$data->time = time();
			}
		}

		// Check count limit
		if ($limit_count)
		{
			if ($data->count && $limit_count <= $data->count)
			{
				return true;
			}
			else
			{
				++$data->count;
			}
		}

		// Store data
		$inputCookie->set('freshmail2_' . $control, json_encode($data), time() + 30 * 24 * 3600);

		return false;
	}

	/**
	 * Post sumbmit hook
	 *
	 * @param   string     $control
	 * @param   JRegistry  $params
	 *
	 * @return  Boolean  True
	 */
	public static function postHook($control, JRegistry $params)
	{
		// Save state in cookie
		if ($params->get('limit_registered', 0))
		{
			$inputCookie = JFactory::getApplication()->input->cookie;
			$dataString = $inputCookie->get('freshmail2_' . $control, null, 'string');

			$data = ($dataString) ? json_decode($dataString) : (object) array('time' => 0, 'count' => 0, 'state' => 0);
			$data->state = true;

			$inputCookie->set('freshmail2_' . $control, json_encode($data), time() + 30 * 24 * 3600);
		}

		return true;
	}

	/**
	 * Add contact to list
	 *
	 * @param   array      $data    Data to add
	 * @param   JRegistry  $params  Module parameters
	 *
	 * @return  boolean  True on success false on failure
	 */
	public static function addContact(array $data, JRegistry $params)
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		// Instanitate Client
		$client = new JFmRestApi;

		$client->setApiKey($params->get('FMapiKey'));
		$client->setApiSecret($params->get('FMapiSecret'));

		// Set timeout
		if (method_exists($client, 'setTimeout'))
		{
			$client->setTimeout($params->get('FMapiTimeout'));
		}

		// Build payload
		$data = array(
			'email'		=> $data['email'],
			'list'		=> $params->get('FMlistHash'),
			'custom_fields'	=> array(),
			'state'		=> null,
			'confirm'	=> null,
		);

		// Kody Statusow Subskrybentow
		if ($params->get('FMdefaultState'))
		{
			$data['state'] = $params->get('FMdefaultState');
		}

		// Confirmation email
		if ($params->get('FMdefaultConfirm'))
		{
			$data['confirm'] = $params->get('FMdefaultConfirm');
		}

		// Add custom fields
		foreach ($params->get('FMdisplayFields', array()) as $customField)
		{
			// If filled in
			if (isset($data['custom_fields'][$customField]))
			{
				$data['custom_fields'][$customField] = $data['custom_fields'][$customField];
			}
		}

		/* @throws RestException */
		try
		{
			$client->doRequest('subscriber/add', $data);
		}
		catch (Exception $e)
		{
			// Pop translated exception message if available
			$langKey = 'MOD_FRESHMAIL2_ERROR_' . $e->getCode();
			$app->enqueueMessage($lang->hasKey($langKey) ? JText::_($langKey) : $e->getMessage(), 'error');

			return false;
		}

		$response = $client->getResponse();

		// OK
		if ($response['status'] === 'OK')
		{
			$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_SUCCESS'), 'success');

			return true;
		}

		// Undefined
		if ($response['status'] != 'ERROR')
		{
			return null;
		}

		// Render error message
		foreach ($response['errors'] as $error)
		{
			// Pop translated error message if available
			$langKey = 'MOD_FRESHMAIL2_ERROR_' . $error['code'];
			$app->enqueueMessage(($lang->hasKey($langKey) ? JText::_($langKey) : $error['message']), 'error');
		}

		return false;
	}

	/**
	 * Send notifcation email in enabled.
	 *
	 * @param   array      $data    Data to send
	 * @param   JRegisytr  $params  Module parameters
	 *
	 * @return  boolean  True on success false on failure
	 */
	public static function sendEmail(array $data, JRegistry $params)
	{
		// Initialise variables
		$app = JFactory::getApplication();
		$mailer = JFactory::getMailer();

		// Nofication sent to
		$to = $params->get('notificationTo');

		// Notification disabled
		if (!$params->get('notificationOn') || empty($to))
		{
			return true;
		}

		$mailFrom = $app->getCfg('mailfrom');
		$fromName = $app->getCfg('fromname');
		$siteName = $app->getCfg('sitename');

		// Clean email data
		$from		= JMailHelper::cleanAddress($data['email']);
		$subject	= JMailHelper::cleanSubject(JText::_('MOD_FRESHMAIL2_NOTIFICATION_SUBJECT'));

		// Prepare email body
		$body 		= "\r\n" . JText::sprintf('MOD_FRESHMAIL2_NOTIFICATION_BODY', $from);

		// Attach custom fields
		$customFields = static::getCustomFields($params, $data['custom_fields']);

		foreach ($customFields as $field)
		{
			$body	.= "\r\n" . JText::sprintf('MOD_FRESHMAIL2_NOTIFICATION_FIELD', $field->name, $field->value);
		}

		// Construct mailer
		$mailer
			->addRecipient($to)
			->addReplyTo(array($from))
			->setSender(array($mailFrom, $fromName))
			->setSubject($siteName . ': ' . $subject)
			->setBody($body);

		return $mailer->Send();
	}

	/**
	 * Execute Rest API command
	 *
	 * @param   string   $apiKey          FreshMail API Key
	 * @param   string   $apiSecret       FreshMail API Secret
	 * @param   string   $command         Command
	 * @param   array    $params          Command parameters
	 * @param   string   $key             Rresult set key
	 * @param   boolean  $cacheCheckTime  Check cache time
	 *
	 * @return  array  Result set
	 *
	 * @throws  RestException
	 * @throws  Exception
	 */
	public static function executeCommand($apiKey, $apiSecret, $command, $params = array(), $key = null, $cacheCheckTime = true)
	{
		$cache = JCache::getInstance(
			/* @type string $type */
			null,
			/* @type array $options */
			array(
				'language'		=> 'en-GB',
				'defaultgroup' 	=> 'mod_freshmail2',
				'checkTime'		=> $cacheCheckTime,
				'caching'		=> true,
			)
		);

		$cacheId = $apiKey . ':' . $command . ':' . serialize($params);

		$response = $cache->get($cacheId);

		// No items in cache
		if ($response === false)
		{
			$client = new JFmRestApi;
			$client->setApiKey($apiKey);
			$client->setApiSecret($apiSecret);

			if (method_exists($client, 'setTimeout'))
			{
				$client->setTimeout(30);
			}

			/* @throws RestException */
			$client->doRequest($command, (is_array($params) ? $params : null));

			// Get Items from response
			$response = $client->getResponse();

			$cache->store($response, $cacheId);
		}

		// Keyed
		if ($key && isset($response[$key]))
		{
			return $response[$key];
		}

		return $response;
	}

	/**
	 * Get custom fields
	 *
	 * @param   JRegistry  $params  Module parameters
	 * @param   array      $values  Values [Optional]
	 *
	 * @return  array
	 */
	public static function getCustomFields(JRegistry $params, $values = array())
	{
		$items = array();

		// Item: hash, name, tag, type
		try
		{
			$allFields = static::executeCommand(
				$params->get('FMapiKey'),
				$params->get('FMapiSecret'),
				'subscribers_list/getFields',
				array('hash' => $params->get('FMlistHash')),
				'fields',
				false
			);
		}
		catch (Exception $e)
		{
			return $items;
		}

		// Save from pivot fatal
		if (empty($allFields))
		{
			return $items;
		}

		// Pivot by name
		$allFields = JArrayHelper::pivot($allFields, 'tag');

		// Get params
		$displayFields = $params->get('FMdisplayFields', array());
		$requiredFields = $params->get('FMrequiredFields', array());

		// Filter in only display fields
		$allFields = (array_intersect_key($allFields, array_flip($displayFields)));

		foreach ($allFields as $tag => $field)
		{
			$field['required'] = (in_array($tag, $requiredFields));
			$field['value'] = (isset($values[$tag])) ? $values[$tag] : null;

			$items[] = (object) $field;
		}

		return $items;
	}

	/**
	 * Get menu link by Id
	 *
	 * @param   integer  $itemId  Menu item ID
	 *
	 * @return  string  Menu item URI
	 */
	public static function getMenuLink($itemId = null)
	{
		$menu 	= JFactory::getApplication()->getMenu();
		$item	= $menu->getItem((int) $itemId);

		if (!$item)
		{
			return null;
		}

		return $item->link . '&Itemid=' . $itemId;
	}

	/**
	 * Ajax event
	 * Need js client to work.
	 *
	 * @return   mixed
	 *
	 * @note   Example use: ?option=com_ajax
	 *                      &format=json
	 *                      &module=freshmail2
	 *                      &method=post
	 *                      &ignoreMessages=0
	 *                      &control=[control]
	 *                      &[control]=[form]
	 *
	 * @since  3.1
	 */
	public static function postAjax()
	{
		// Get JInput object
		$input = JFactory::getApplication()->input;

		// Load module language file
		$lang = JFactory::getLanguage();
		$lang->load('mod_freshmail2', JPATH_SITE)
			|| $lang->load('mod_freshmail2', __DIR__);

		// Get module id
		$control = $input->get('control', '');
		if (!$control)
		{
			return new LogicException('No module id');
		}

		// Load module
		$moduleId = substr($control, 4);
		$table = JTable::getInstance('module');

		if (!$table->load(array('id' => $moduleId)))
		{
			return new LogicException('No module');
		}

		// Decode mnodule params
		$params = new JRegistry;
		$params->loadString($table->params);

		// Read POSTed data
		$inputData = $input->post->get($control, null, 'array');

		// Process POSTed data (Valid, Added, Notified)
		if (!empty($inputData)
			&& static::validate($inputData, $params)
			&& static::addContact($inputData, $params)
			&& static::sendEmail($inputData, $params)
			&& static::postHook($control, $params))
		{
			// All is OK
			return JText::_('MOD_FRESHMAIL2_SUCCESS');
		}

		return new UnexpectedValueException('Cannot add contact');
	}
}
