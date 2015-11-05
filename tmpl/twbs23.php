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
<?php var_dump($customFields); die() ?>
<form method="post" class="form-validate <?php echo $moduleclass_sfx ?>" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
	<div class="control-group  form-group">
		<?php // Lists: Control ?>
		<?php foreach ($lists as $i => $list) : ?>
		<div class="checkbox">
			<label class="hasTooltip" title="<?php echo $list->description ?>" for="<?php echo $control ?>_list">
				<input name="<?php echo $control ?>[list][]" type="checkbox" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" for="<?php echo $control ?>_list" />
				<?php echo $list->name ?>
			</label>
		</div>
		<?php endforeach ?>
	</div>
	<?php endif ?>

	<div class="control-group">
		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<div class="form-group">
			<label class="control-label" for="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>">
				<?php echo $field->name ?><?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
			</label>
			<div class="controls">
				<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?>value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?> id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" class="input-block-level  form-control" <?php if ($field->required) : ?> required="required"<?php endif ?> />
			</div>
		</div>
		<?php endforeach ?>

		<?php // Email ?>
		<div class="form-group">
			<label class="control-label" for="<?php echo $control ?>_email">
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?><span class="star">&nbsp;*</span>
			</label>
			<div class="controls">
				<input name="<?php echo $control ?>[email]" type="email" <?php if (!empty($stateValues['email'])) : ?>value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> id="<?php echo $control ?>_email" class="input-block-level  form-control  validate-email required" required="required" />
			</div>
		</div>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<div class="checkbox">
			<label for="<?php echo $control ?>_tos">
				<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" <?php if (!empty($stateValues['tos'])) : ?>checked="checked"<?php endif ?> id="<?php echo $control ?>_tos" class="required" required="required" />
				<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
			</label>
		</div>
		<?php endif ?>
	</div>

	<?php // Submit button ?>
	<button class="btn btn-primary btn-sm  validate" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

	<?php echo JHtml::_('form.token') ?>
</form>
