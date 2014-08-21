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
<form method="post" class=" <?php echo $moduleclass_sfx ?>" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<?php // Lists ?>
	<?php if (count($lists) > 1) : ?>
	<div class="control-group">
		<?php // Lists:Control ?>
		<?php foreach ($lists as $i => $list) : ?>
		<label class="checkbox" title="<?php echo $list->description ?>">
			<input name="<?php echo $control ?>[list][]" type="checkbox" <?php if ($list->selected) : ?> checked="checked"<?php endif ?> value="<?php echo $list->subscriberListHash ?>" />
			<?php echo $list->name ?>
		</label>
		<?php endforeach ?>
	</div>
	<?php endif ?>

	<div class="control-group">
		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<label class="control-label">
			<?php echo $field->name ?>:<?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
		</label>
		<div class="controls">
			<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" class="input-medium" <?php if ($field->required) : ?>required="required" <?php endif ?> />
		</div>
		<?php endforeach ?>

		<?php // Email ?>
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
