<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<form method="post" class="mod_freshmail<?php echo $moduleclass_sfx ?> form-validate" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<fieldset class="userdata">
		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<p>
			<label>
				<?php echo $field->name ?>:<?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
				<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" class="inputbox" <?php if ($field->required) : ?>required="required" <?php endif ?> size="18" />
			</label>
		</p>
		<?php endforeach ?>

		<?php // Email ?>
		<p>
			<label>
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>:<span class="star">&nbsp;*</span>
				<input name="<?php echo $control ?>[email]" type="email" class="inputbox validate-email required" required="required" size="20" />
			</label>
		</p>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<p>
			<label>
				<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" class="inputbox required" required="required" />
				<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
			</label>
		</p>
		<?php endif ?>

		<?php // Submit button ?>
		<button type="submit" class="button validate"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

		<?php echo JHtml::_('form.token') ?>
	</fieldset>
</form>
