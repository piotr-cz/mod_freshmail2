<?php
/**
 * @package     Freshmail.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
?>
<form method="post" class="form-inline <?php echo moduleclass_sfx ?>" action="<?php echo JUri::getInstance() ?>" >
	<?php // Terms of Service ?>
	<?php if ($tosLink) : ?>
	<label class="checkbox">
		<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" class="required" required="required" />
		<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
	</label>
	<br />
	<?php endif ?>

	<?php // Custom Fields ?>
	<?php foreach ($customFields as $field) : ?>
		<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>" type="<?php echo $field->type ?>" class="input-small" <?php if ($field->required) : ?>required="required" <?php endif ?> placeholder="<?php echo $field->name ?>" />
	<?php endforeach ?>

	<div class="input-append">
		<?php // Email ?>
		<input name="<?php echo $control ?>[email]" type="email" class="input-small required" required="required" placeholder="<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>" />

		<?php // Submit button ?>
		<button class="bnt btn-primary add-on" type="submit" name="submit" value="<?php echo $control ?>"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>
	</div>

	<?php echo JHtml::_('form.token') ?>
</form>
