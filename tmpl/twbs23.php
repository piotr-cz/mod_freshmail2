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

// Load Ajax scripts
if ($isAjaxEnabled)
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'system/core.js', false, true);
 	JHtml::_('script', 'mod_freshmail2/submit.js', false, true);
}
?>
<form method="post" class=" <?php echo $moduleclass_sfx ?>" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<?php // Custom Fields ?>
	<?php foreach ($customFields as $field) : ?>
	<div class="control-group">
		<label class="control-label">
			<?php echo $field->name ?>:<?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
		</label>
		<div class="controls">
			<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" class="input-medium" <?php if ($field->required) : ?>required="required" <?php endif ?> />
		</div>
	</div>
	<?php endforeach ?>

	<?php // Email ?>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>:<span class="star">&nbsp;*</span>
		</label>
		<div class="controls">
			<input name="<?php echo $control ?>[email]" type="email" class="input-medium validate-email required" required="required" />
		</div>
	</div>

	<?php // Terms of Service ?>
	<?php if ($tosLink) : ?>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" class="required" required="required" />
				<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
			</label>
		</div>
	</div>
	<?php endif ?>

	<?php // Submit button ?>
	<div class="control-group">
		<div class="controls">
			<button class="btn btn-primary" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>
		</div>
	</div>

	<?php echo JHtml::_('form.token') ?>
</form>
