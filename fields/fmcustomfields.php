<?php
/**
 * @package     Joomla.Module
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Import dependencies
JFormHelper::loadFieldClass('FmLists');
JLoader::register('ModFreshmail2Helper', JPATH_ROOT . '/modules/mod_freshmail2/helper.php');

/**
 * Custom fields field class for mod_freshmail2
 *
 * @package     Joomla.Module
 * @subpackage  mod_freshmail2
 *
 * @since       1.0
 *
 * @note        API output
 * <code>
 *		[status] => OK
 *		[fields] => Array
 *		(
 *			[hash]	=>
 *			[name]	=>
 *			[tag]	=>
 *			[type]	+>
 *		)
 * </code>
 */
class JFormFieldFmCustomfields extends JFormFieldFmLists
{
	/**
	 * {@inheritdoc}
	 */
	public $type = 'FMCustomFields';

	/**
	 * List Hash
	 *
	 * @var    string
	 */
	protected $listHash;

	/**
	 * {@inheritdoc}
	 */
	public function setForm(JForm $form)
	{
		$this->listHash		= $form->getValue('FMlistHash', 'params');

		return parent::setForm($form);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInput()
	{
		// Stop when there's no List hash
		if (!$this->listHash)
		{
			return static::wrapLabel(JText::_('MOD_FRESHMAIL2_FIELD_APISTATUS_MISSING_LIST_HASH'), 'info');
		}

		// Try out the api
		return parent::getInput();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getOptions()
	{
		// Options
		$showType = (!empty($this->element['display_type']));

		$options = array();

		// Get Items using API
		$items = ModFreshmail2Helper::executeCommand(
			$this->apiKey,
			$this->apiSecret,
			'subscribers_list/getFields',
			array('hash' => $this->listHash),
			'fields'
		);

		// Create options
		foreach ($items as $field)
		{
			// Create a new option object based values
			$tmp = JHtml::_(
				/* @type string $key */
				'select.option',
				/* @type string $value */
				$field['tag'],
				/* @type string $text */
				$field['name'] . ($showType ? ' (' . $field['type'] . ')' : ''),
				/* @type string $optKey */
				'value',
				/* @type string $optText */
				'text',
				/* @type boolean $disable */
				false
			);

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
