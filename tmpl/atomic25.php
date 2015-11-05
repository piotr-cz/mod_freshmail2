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
<form method="post" class="input mod_freshmail<?php echo $moduleclass_sfx ?> form-validate" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<fieldset class="userdata">
		<?php // Lists ?>
		<?php if (count($lists) > 1) : ?>
			<?php // Lists: Control ?>
			<?php foreach ($lists as $i => $list) : ?>
			<p>
				<label title="<?php echo $list->description ?>" for="<?php echo $control ?>_list">
					<input name="<?php echo $control ?>[list][]" type="checkbox" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" id="<?php echo $control ?>_list" />
				<?php echo $list->name ?>
				</label>
			</p>
			<?php endforeach ?>
		<?php endif ?>

		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<p>
			<label for="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>">
				<?php echo $field->name ?><?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
			</label>
			<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?>value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?>  id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" <?php if (!$field->required) : ?> class="validate-required"<?php else : ?> class="inputbox required" required="required"<?php endif ?> size="18" />
		</p>
		<?php endforeach ?>

		<?php // Email ?>
		<p>
			<label for="<?php echo $control ?>_email">
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?><span class="star">&nbsp;*</span>
			</label>
			<input name="<?php echo $control ?>[email]" type="email" <?php if (!empty($stateValues['email'])) : ?>value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> id="<?php echo $control ?>_email" class="inputbox validate_email required" required="required" size="18" />
		</p>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<p>
			<label for="<?php echo $control ?>_tos">
				<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" <?php if (!empty($stateValues['tos'])) : ?>checked="checked"<?php endif ?> id="<?php echo $control ?>_tos" class="inputbox required" required="required" />
			</label>
			<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
		</p>
		<?php endif ?>

		<?php // Submit button ?>
		<button type="submit" class="button validate"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

		<?php echo JHtml::_('form.token') ?>
	</fieldset>
</form>
