<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2017 Piotr Konieczny. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Script file
 *
 * @link  https://docs.joomla.org/Manifest_files#Script_file
 */
class ModFreshmail2InstallerScript
{
	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($type, JAdapterInstance $adapter)
	{
		if ($type !== 'update') {
			return $this->configure();
		}
        
		return true;
	}

	/**
	 * Configure extension
	 *
	 * @return  boolean
	 */
	protected function configure()
	{
		$jversion = new JVersion();

		// Apply only to J2.5
		if ($jversion->isCompatible('3.0')) {
			return true;
		}

		$table = JTable::getInstance('module');

		if (!$table->load(array('module' => 'mod_freshmail2'))) {
			return false;
		}

		$params = new JRegistry();
		$params->loadString($table->params);

		// Set default layout to J2.5 compatible
		$params->set('layout', '_:atomic25');

		$table->params = (string) $params;

		return $table->store();
	}
}
