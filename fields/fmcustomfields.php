<?php
/**
 * @package     Freshmail2.Site
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
 * @package     Freshmail2.Site
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
 *			[type]	=>
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
		$this->listHash		= (array) $form->getValue('FMlistHash', 'params');

		return parent::setForm($form);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInput()
	{
		// Field setup
		$hideMessages = (!empty($this->element['hide_messages']));

		// Stop when there's no List hash
		if (empty($this->listHash))
		{
			return (!$hideMessages) ? static::wrapLabel(JText::_('MOD_FRESHMAIL2_FIELD_APISTATUS_MISSING_LIST_HASH'), 'info') : '';
		}

		// Get HTML input
		$html = parent::getInput();

		if (count($this->listHash) > 1 && !$hideMessages)
		{
			$html .= static::wrapLabel(JText::_('MOD_FRESHMAIL2_FIELD_APISTATUS_LIST_MULTIPLE', 'info'));
		}

		return $html;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getOptions()
	{
		// Field setup
		$showType = (!empty($this->element['display_type']));

		$options = array();
		$items = array();

		// Get Items for each list using API
		foreach ((array) $this->listHash as $listHash)
		{
			$items = array_merge(
				$items,
				ModFreshmail2Helper::executeCommand(
					$this->apiKey,
					$this->apiSecret,
					'subscribers_list/getFields',
					array('hash' => $listHash),
					'fields'
				)
			);
		}

		// Make tag unique
		$tags = array();

		// Create options
		foreach ($items as $field)
		{
			if (!in_array($field['tag'], $tags))
			{
				$tags[] = $field['tag'];

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
		}

		reset($options);

		return $options;
	}
}
