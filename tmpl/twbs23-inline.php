<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2015 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_((JVersion::isCompatible('3.4')) ? 'behavior.formvalidator' : 'behavior.formvalidation');
?>
<form method="post" class="form-inline form-validate <?php echo $moduleclass_sfx ?>" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
	<div class="control-group  form-group">
		<?php foreach ($lists as $i => $list) : ?>
		<span class="checkbox inline">
			<label class="checkbox  checkbox-inline  hasTooltip" title="<?php echo $list->description ?>" for="<?php echo $control ?>_list">
				<input name="<?php echo $control ?>[list][]" type="checkbox" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" id="<?php echo $control ?>_list" />
				<?php echo $list->name ?>
			</label>
		</span>
		&nbsp;
		<?php endforeach ?>
	</div>
	<?php endif ?>

	<div class="control-group  form-group">
	<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
			<label class="hide" for="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>"><?php echo $field->name ?></label>
			<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?>value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?> id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" class="input-small  form-control input-sm  hasTooltip" <?php if ($field->required) : ?>required="required"<?php endif ?> placeholder="<?php echo $field->name ?> *" title="<?php echo $field->name ?>" />
		<?php endforeach ?>

		<?php // Email ?>
		<label class="hide" for="<?php echo $control ?>_email"><?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?></label>
		<input name="<?php echo $control ?>[email]" type="email" <?php if (!empty($stateValues['email'])) : ?>value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> id="<?php echo $control ?>_email" class="input-small  form-control input-sm  required hasTooltip validate-email" required="required" placeholder="<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?> *" title="<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>" />

		<?php // Submit button ?>
		<button class="btn btn-primary btn-sm  validate" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<label class="checkbox  checkbox-inline" for="<?php echo $control ?>_tos">
			<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" <?php if (!empty($stateValues['tos'])) : ?>checked="checked"<?php endif ?> id="<?php echo $control ?>_tos" class="required" required="required" />
			<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
		</label>
		<?php endif ?>
	</div>

	<?php echo JHtml::_('form.token') ?>
</form>
