<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2018 Piotr Konieczny. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_(($jversion->isCompatible('3.4')) ? 'behavior.formvalidator' : 'behavior.formvalidation');
?>
<form class="form-validate <?php echo $moduleclass_sfx ?>" data-freshmail2="<?php echo $control ?>" method="post" action="<?php echo JUri::getInstance() ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
	<div class="control-group  form-group">
		<?php // Lists: Control ?>
		<?php foreach ($lists as $i => $list) : ?>
		<div class="checkbox">
			<label class="hasTooltip" title="<?php echo $list->description ?>" for="<?php echo $control ?>_list">
				<input id="<?php echo $control ?>_list" name="<?php echo $control ?>[list][]" type="checkbox" value="<?php echo $list->subscriberListHash ?>" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> />
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
				<input class="input-block-level  form-control" id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?>value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?> <?php if ($field->required) : ?> required="required"<?php endif ?> />
			</div>
		</div>
		<?php endforeach ?>

		<?php // Email ?>
		<div class="form-group">
			<label class="control-label" for="<?php echo $control ?>_email">
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?><span class="star">&nbsp;*</span>
			</label>
			<div class="controls">
				<input class="input-block-level  form-control  validate-email" id="<?php echo $control ?>_email" name="<?php echo $control ?>[email]" type="email" <?php if (!empty($stateValues['email'])) : ?>value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> required="required" />
			</div>
		</div>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<div class="checkbox">
			<label for="<?php echo $control ?>_tos">
				<input class="required" id="<?php echo $control ?>_tos" name="<?php echo $control ?>[tos]" type="checkbox" value="1"<?php if (!empty($stateValues['tos'])) : ?> checked="checked"<?php endif ?> required="required" />
				<small>
					<?php if ($params->get('tos_info_text')) : ?>
						<?php echo sprintf($params->get('tos_info_text'), sprintf('<a href="%s" target="site">%s</a>', JRoute::_($tosLink), $params->get('tos_button_text', JText::_('MOD_FRESHMAIL2_TOSLINK_BUTTON_TEXT')))) ?>
					<?php else : ?>
						<?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink), $params->get('tos_button_text', JText::_('MOD_FRESHMAIL2_TOSLINK_BUTTON_TEXT'))) ?>
					<?php endif ?>
				</small>
			</label>
		</div>
		<?php endif ?>

		<?php // Captcha ?>
		<?php if ($captcha instanceof JCaptcha) : ?>
		<div class="form-control  form-group">
			<?php echo $captcha->display('captcha', $control . '_captcha', ' ') ?>
		</div>
		<?php endif ?>
	</div>

	<?php // Submit button ?>
	<button class="btn btn-primary btn-sm  validate" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

	<?php echo JHtml::_('form.token') ?>
</form>
<?php if ($isEmailCloakActive) : ?><!--{emailcloak=off}--><?php endif ?>
