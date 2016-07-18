<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2016 Piotr Konieczny. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_(($jversion->isCompatible('3.4')) ? 'behavior.formvalidator' : 'behavior.formvalidation');
?>
<form class="form-inline form-validate <?php echo $moduleclass_sfx ?>" data-freshmail2="<?php echo $control ?>" method="post" action="<?php echo JUri::getInstance() ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
	<div class="control-group  form-group">
		<?php foreach ($lists as $i => $list) : ?>
		<span class="checkbox inline">
			<label class="checkbox  checkbox-inline  hasTooltip" for="<?php echo $control ?>_list" title="<?php echo $list->description ?>">
				<input id="<?php echo $control ?>_list" name="<?php echo $control ?>[list][]" type="checkbox" value="<?php echo $list->subscriberListHash ?>"<?php if ($list->selected) : ?> checked="checked"<?php endif ?> />
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
			<input class="input-small  form-control input-sm  hasTooltip" id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?>value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?> title="<?php echo $field->name ?>"<?php if ($field->required) : ?> required="required"<?php endif ?> placeholder="<?php echo $field->name ?> *" />
		<?php endforeach ?>

		<?php // Email ?>
		<label class="hide" for="<?php echo $control ?>_email"><?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?></label>
		<input class="input-small  form-control input-sm  hasTooltip validate-email" id="<?php echo $control ?>_email" name="<?php echo $control ?>[email]" type="email" <?php if (!empty($stateValues['email'])) : ?>value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> title="<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>" required="required" placeholder="<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?> *" />

		<?php // Submit button ?>
		<button class="btn btn-primary btn-sm  validate" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<label class="checkbox  checkbox-inline" for="<?php echo $control ?>_tos">
			<input class="required" id="<?php echo $control ?>_tos" name="<?php echo $control ?>[tos]" type="checkbox" value="1"<?php if (!empty($stateValues['tos'])) : ?> checked="checked"<?php endif ?> required="required" />
			<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
		</label>
		<?php endif ?>
	</div>
		<?php // Captcha ?>
	<?php if ($captcha instanceof JCaptcha) : ?>
	<div class="control-group  form-group">
		<?php echo $captcha->display('captcha', $control . '_captcha', ' ') ?>
	</div>
	<?php endif ?>

	<?php echo JHtml::_('form.token') ?>
</form>
<?php if ($isEmailCloakActive) : ?><!--{emailcloak=off}--><?php endif ?>
