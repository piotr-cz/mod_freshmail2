<?php
/**
 * @package     Freshmail2.Site
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
	 * Check if can skip module rendering
	 *
	 * @param   string     $control  Form domain
	 * @param   JRegistry  $params   Extension parameters
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

		// Check time limit against last shown
		if ($limit_time)
		{
			if ($data->time && $limit_time <= (time() - $data->time) / 60)
			{
				return true;
			}
			elseif (!$data->time)
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
	 * Execute Rest API command wrapper
	 *
	 * @param   string   $apiKey          FreshMail API Key
	 * @param   string   $apiSecret       FreshMail API Secret
	 * @param   string   $command         Command
	 * @param   array    $params          Command parameters
	 * @param   string   $key             Result set key
	 * @param   boolean  $cacheCheckTime  Check cache time
	 *
	 * @return  array  Result set
	 *
	 * @throws  RestException
	 * @throws  Exception
	 */
	public static function executeCommand($apiKey, $apiSecret, $command, $params = array(), $key = null, $cacheCheckTime = true)
	{
		// Initialize cache
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

		// Build cache key
		$cacheId = $apiKey . ':' . $command . ':' . serialize($params);

		// Load response from cache
		$response = $cache->get($cacheId);

		// No cached items
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

			// Store response in cache
			$cache->store($response, $cacheId);
		}

		// Access result set by key
		if ($key && isset($response[$key]))
		{
			return $response[$key];
		}

		return $response;
	}

	/**
	 * Get and prepare custom fields
	 * [frontend]
	 *
	 * @param   JRegistry  $params  Module parameters
	 * @param   array      $values  Values [Optional]
	 *
	 * @return  array
	 */
	public static function getProcessedCustomFields(JRegistry $params, $values = array())
	{
		$items = array();
		$lists = (array) $params->get('FMlistHash');

		$tags = array();
		$uniqueFields = array();
		$allFields = array();

		// Item: hash, name, tag, type
		foreach ($lists as $listHash)
		{
			try
			{
				$allFields = array_merge(
					$allFields,
					static::executeCommand(
						$params->get('FMapiKey'),
						$params->get('FMapiSecret'),
						'subscribers_list/getFields',
						array('hash' => $listHash),
						'fields',
						false
					)
				);
			}
			catch (Exception $e)
			{
				return $items;
			}
		}

		// Save from pivot fatal
		if (empty($allFields))
		{
			return $items;
		}

		// Pivot by unique tag
		foreach ($allFields as $field)
		{
			if (!in_array($field['tag'], $tags))
			{
				$uniqueFields[$field['tag']] = $field;
			}
		}

		// Get params
		$displayFields = $params->get('FMdisplayFields', array());
		$requiredFields = $params->get('FMrequiredFields', array());

		// Filter in only display fields
		$uniqueFields = (array_intersect_key($uniqueFields, array_flip($displayFields)));

		// Add each item to collection
		foreach ($uniqueFields as $tag => $field)
		{
			// Add extra item data
			$field['required'] = (in_array($tag, $requiredFields));
			$field['value'] = (isset($values[$tag])) ? $values[$tag] : null;

			$items[] = (object) $field;
		}

		return $items;
	}

	/**
	 * Get and prepare subscribers list
	 * [frontend]
	 *
	 * @param   JRegistry  $params  Module parameters
	 * @param   array      $values  Values [Optional]
	 *
	 * @return  array
	 *
	 * @since   1.1
	 */
	public static function getProcessedLists(JRegistry $params, array $values = array())
	{
		$items = array();

		// Retrieve lists
		try
		{
			$allLists = static::executeCommand(
				$params->get('FMapiKey'),
				$params->get('FMapiSecret'),
				'subscribers_list/lists',
				null,
				'lists'
			);
		}
		catch (Exception $e)
		{
			return $items;
		}

		// Save from pivot fatal
		if (empty($allLists))
		{
			return $list;
		}

		// Pivot by listHash
		$allLists = JArrayHelper::pivot($allLists, 'subscriberListHash');

		// Get params
		$displayLists = (array) $params->get('FMlistHash');

		// Filter in only display data
		$allLists = (array_intersect_key($allLists, array_flip($displayLists)));

		$isSingleList = (count($allLists) == 1);

		// Add each item to collection
		foreach ($allLists as $hash => $list)
		{
			// Add extra item data
			$list['selected'] = ($isSingleList)
				? true
				: in_array($hash, $values);

			$items[] = (object) $list;
		}

		return $items;
	}

	/**
	 * Ajax event
	 * Ajaxified version of mod_freshmail2
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
	 * @note   Need javascript client to work.
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

		// Get processed lists set
		$selectedLists = (isset($inputData['list'])) ? (array) $inputData['list'] : array();
		$lists = static::getProcessedLists($params, $selectedLists);

		// Process POSTed data (Valid, Added, Notified)
		if (!empty($inputData) && static::validate($inputData, $params))
		{
			// Success flag
			$addContactsSuccess = false;

			// Loop trough lists and process selected ones
			foreach ($lists as $list)
			{
				if ($list->selected)
				{
					// Validate, add contact
					if (!static::addContact($inputData, $params, $list))
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
				static::sendEmail($inputData, $params)
					&& static::postHook($control, $params);

				// All is OK
				return JText::_('MOD_FRESHMAIL2_SUCCESS');
			}
		}

		return new UnexpectedValueException('Cannot subscribe');
	}

	// Actions

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

		// In case of multiple lists, check at least one is selected
		// Note: should check set lists against submitted ones
		if (count((array) $params->get('FMlistHash')) > 1)
		{
			if (empty($data['list']))
			{
				$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_ERROR_LIST_NOT_SELECTED'), 'error');

				return false;
			}
		}

		// Validate custom fields
		$validates = true;

		// Load up custom fields
		$customFields = static::getProcessedCustomFields($params);

		foreach ($customFields as $field)
		{
			if ($field->required && empty($data['custom_fields'][$field->tag]))
			{
				$app->enqueueMessage(JText::sprintf('MOD_FRESHMAIL2_ERROR_FIELD', $field->name), 'notice');

				// Invalidate
				$validates = false;
			}
		}

		return $validates;
	}

	/**
	 * Add contact to list
	 *
	 * @param   array      $data    Data to add
	 * @param   JRegistry  $params  Module parameters
	 * @param   stdClass   $list    List object
	 *
	 * @return  boolean  True on success false on failure
	 */
	public static function addContact(array $data, JRegistry $params, stdClass $list)
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		// Process messages differently
		$isSingle = (count($params->get('FMlistHash')) == 1);

		// Instanitate Client
		$client = new JFmRestApi;

		$client->setApiKey($params->get('FMapiKey'));
		$client->setApiSecret($params->get('FMapiSecret'));

		// Set timeout
		if (method_exists($client, 'setTimeout'))
		{
			$client->setTimeout($params->get('FMapiTimeout'));
		}

		/* Build payload
		 * Note: When `confirm` value is set to null, Double opt-in lists
		 * don't send auto confirmations.
		 * Inconsistency in API docs: http://freshmail.pl/developer-api/subskrybenci-zarzadzanie-subskrybentami/
		 */
		$payload = array(
			'email'			=> $data['email'],
			'list'			=> $list->subscriberListHash,
			'custom_fields'	=> array(),
		//	'state'			=> null,
		//	'confirm'		=> null,
		);

		// Set Kody Statusow Subskrybentow
		if ($params->get('FMdefaultState'))
		{
			$payload['state'] = $params->get('FMdefaultState');
		}

		// Set Confirmation email
		if ($params->get('FMdefaultConfirm'))
		{
			$payload['confirm'] = $params->get('FMdefaultConfirm');
		}

		// Add custom fields
		if (isset($data['custom_fields']))
		{
			foreach ($params->get('FMdisplayFields', array()) as $customField)
			{
				// If filled in, set using tag as key
				if (isset($data['custom_fields'][$customField]))
				{
					$payload['custom_fields'][$customField] = $data['custom_fields'][$customField];
				}
			}
		}

		// Send payload
		try
		{
			/* @throws RestException */
			$client->doRequest('subscriber/add', $payload);
		}
		catch (Exception $e)
		{
			// Pop translated exception message if available
			$langKey = 'MOD_FRESHMAIL2_ERROR_' . $e->getCode();
			$message = ($lang->hasKey($langKey)) ? JText::_($langKey) : $e->getMessage();

			// Attach list name
			if (!$isSingle)
			{
				$message .= sprintf(' (%s)', $list->name);
			}

			$app->enqueueMessage($message, 'error');

			return false;
		}

		$response = $client->getResponse();

		// OK
		if ($response['status'] === 'OK')
		{
			if ($isSingle)
			{
				$app->enqueueMessage(JText::_('MOD_FRESHMAIL2_SUCCESS'), 'success');
			}

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
			$message = ($lang->hasKey($langKey)) ? JText::_($langKey) : $error['message'];

			// Attach list name
			if (!$isSingle)
			{
				$message .= sprintf(' (%s)', $list->name);
			}

			$app->enqueueMessage($message, 'error');
		}

		return false;
	}

	/**
	 * Send notification email in enabled.
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
		if (isset($data['custom_fields']))
		{
			$customFields = static::getProcessedCustomFields($params, $data['custom_fields']);

			foreach ($customFields as $field)
			{
				$body	.= "\r\n" . JText::sprintf('MOD_FRESHMAIL2_NOTIFICATION_FIELD', $field->name, $field->value);
			}
		}
		elseif (isset($data['lists']))
		{
			$lists = static::getProcessedLists($params, (array) $data['lists']);
			$body .= "\r\n" . JText::_('MOD_FRESHMAIL2_NOTIFICATION_LISTS');

			foreach ($lists as $list)
			{
				$body	.= "\r\n" . $list->name;
			}
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
	 * Post submit hook
	 *
	 * @param   string     $control  Form domain
	 * @param   JRegistry  $params   Extension parameters
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
}
