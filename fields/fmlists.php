<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2015 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Import dependencies
JFormHelper::loadFieldClass('list');
JLoader::register('ModFreshmail2Helper', JPATH_ROOT . '/modules/mod_freshmail2/helper.php');

/**
 * Lists field class for mod_freshmail2
 *
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @since       1.0
 *
 * @note  API response
 * <code>
 *		[status] => OK
 *		[lists] => Array
 *		(
 *			[0] => Array
 *			(
 *				subscriberListHash
 *				name
 *				creation_date
 *				subscribers_number
 *			)
 *		)
 * </code>
 */
class JFormFieldFmLists extends JFormFieldList
{
	/**
	 * {@inheritdoc}
	 */
	public $type = 'FmLists';

	/**
	 * API Key
	 *
	 * @var    string
	 */
	protected $apiKey;

	/**
	 * API Secret
	 * @var    string
	 */
	protected $apiSecret;

	/**
	 * {@inheritdoc}
	 */
	public function setForm(JForm $form)
	{
		$this->apiKey		= $form->getValue('FMapiKey', 'params');
		$this->apiSecret	= $form->getValue('FMapiSecret', 'params');

		return parent::setForm($form);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInput()
	{
		// Stop when there's no API Key
		if (!$this->apiKey)
		{
			return static::wrapLabel(JText::_('MOD_FRESHMAIL2_FIELD_APISTATUS_MISSING_API_KEY'), 'info');
		}

		// Stop when there's no API Secret
		if (!$this->apiSecret)
		{
			return static::wrapLabel(JText::_('MOD_FRESHMAIL2_FIELD_APISTATUS_MISSING_API_SECRET'), 'info');
		}

		// Try out the API
		try
		{
			$html = parent::getInput();
		}
		catch (Exception $e)
		{
			return static::wrapLabel($e->getMessage(), 'error');
		}

		return $html;
	}

	/**
	 * {@inhertiDoc}
	 */
	protected function getOptions()
	{
		$options = array();

		// Get Items
		$items = ModFreshmail2Helper::executeCommand(
			$this->apiKey,
			$this->apiSecret,
			'subscribers_list/lists',
			null,
			'lists'
		);

		// Sort items by Creation date
		usort(
			$items,
			function( $a, $b )
			{
				strtotime($a['creation_date']) - strtotime($b['creation_date']);
			}
		);

		// Create options
		foreach ($items as $item)
		{
			// Create a new option object based values
			$tmp = JHtml::_(
				/* @type string $key */
				'select.option',
				/* @type string $value */
				$item['subscriberListHash'],
				/* @type string $text */
				$item['name'],
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

	/**
	 * Bootstrap 2.3 Labels
	 *
	 * @var  array
	 *
	 * @see  TWBS 2.3: Labels http://getbootstrap.com/2.3.2/components.html#labels-badges
	 * @see  TWBS 2.3: Emphasis classes  http://getbootstrap.com/2.3.2/base-css.html#typography
	 */
	protected static $helperClassnames = array(
	/* 'default'	=> ''			// label
	 * 'important	=> 'important'	// label
	 * 'inverse'	=> 'inverse',	// label
	 * 'muted'		=> ' muted',	// p
	 */
		'error'		=> 'error',		// p
		'warning'	=> 'warning',
		'info'		=> 'info',
		'success'	=> 'success',
	);

	/**
	 * Wrap string in label
	 *
	 * @param   string  $string     Label string
	 * @param   string  $labelType  Label type
	 *
	 * @return  string  HTML markup
	 */
	protected static function wrapLabel($string, $labelType = 'info')
	{
		$labelClass = (isset(static::$helperClassnames[$labelType]))
			? 'label label-' . static::$helperClassnames[$labelType]
			: '';

		$labelStyle = '';

		// Fallback for Joomla 2.5 without BS
		if (version_compare(JVERSION, '3', '<'))
		{
			$labelStyle = 'float: left; margin: 5px 5px 5px 0; color: red;';
		}

		$html = '<span class="' . $labelClass . '" style="' . $labelStyle . '">' . htmlspecialchars($string) . '</span>';

		return $html;
	}
}
