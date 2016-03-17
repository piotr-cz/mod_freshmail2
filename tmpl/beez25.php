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
<form class="contact mod_freshmail<?php echo $moduleclass_sfx ?> form-validate" data-freshmail2="<?php echo $control ?>" method="post" action="<?php echo JUri::getInstance() ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
		<?php // Lists: Control ?>
		<?php foreach ($lists as $i => $list) : ?>
		<p>
			<label title="<?php echo $list->description ?>" for="<?php echo $control ?>_list">
				<input id="<?php echo $control ?>_list" name="<?php echo $control ?>[list][]" type="checkbox" value="<?php echo $list->subscriberListHash ?>"<?php if ($list->selected) : ?> checked="checked"<?php endif ?> />
				<?php echo $list->name ?>
			</label>
		</p>
		<?php endforeach ?>
	<?php endif ?>

	<dl>
		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<dt>
			<label for="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>">
				<?php echo $field->name ?><?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
			</label>
		</dt>
		<dd>
			<input class="inputbox<?php if ($field->required) : ?> required<?php endif ?>" id="<?php echo $control ?>_custom_fields_<?php echo $field->tag ?>" name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>"<?php if (!empty($stateValues['custom_fields'][$field->tag])) : ?> value="<?php echo htmlspecialchars($stateValues['custom_fields'][$field->tag]) ?>"<?php endif ?> <?php if ($field->required) : ?> required="required"<?php endif ?> size="18" />
		</dd>
		<?php endforeach ?>

		<?php // Email ?>
		<dt>
			<label for="<?php echo $control ?>_email">
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?><span class="star">&nbsp;*</span>
			</label>
		</dt>
		<dd>
			<input class="inputbox validate-email required" id="<?php echo $control ?>_email" name="<?php echo $control ?>[email]" type="email"<?php if (!empty($stateValues['email'])) : ?> value="<?php echo htmlspecialchars($stateValues['email']) ?>"<?php endif ?> required="required" size="18" />
		</dd>
	</dl>


	<?php // Terms of Service ?>
	<?php if ($tosLink) : ?>
	<p class="clr">
		<label for="<?php echo $control ?>_tos">
			<input class="inputbox required" id="<?php echo $control ?>_tos" name="<?php echo $control ?>[tos]" type="checkbox" value="1"<?php if (!empty($stateValues['tos'])) : ?> checked="checked"<?php endif ?> required="required" />
			<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
		</label>
	</p>
	<?php endif ?>

	<?php // Submit button ?>
	<button class="button validate" type="submit"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

	<?php echo JHtml::_('form.token') ?>
</form>
<?php if ($isEmailCloakActive) : ?><!--{emailcloak=off}--><?php endif ?>
