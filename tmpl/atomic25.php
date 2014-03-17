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
<form method="post" class="input mod_freshmail<?php echo $moduleclass_sfx ?> form-validate" action="<?php echo JUri::getInstance() ?>" data-freshmail2="<?php echo $control ?>">
	<fieldset class="userdata">
		<?php // Custom Fields ?>
		<?php foreach ($customFields as $field) : ?>
		<p>
			<label>
				<?php echo $field->name ?>:<?php if ($field->required) : ?><span class="star">&nbsp;*</span><?php endif ?>
			</label><br />
			<input name="<?php echo $control ?>[custom_fields][<?php echo $field->tag ?>]" type="<?php echo $field->type ?>" <?php if (!$field->required) : ?> class="validate-required"<?php else : ?> class="inputbox required" required="required"<?php endif ?> size="18" />
		</p>
		<?php endforeach ?>

		<?php // Email ?>
		<p>
			<label>
				<?php echo JText::_('MOD_FRESHMAIL2_FIELD_EMAIL') ?>:<span class="star">&nbsp;*</span>
			</label><br />
			<input name="<?php echo $control ?>[email]" type="email" class="inputbox validate-email required" required="required" size="18" />
		</p>

		<?php // Terms of Service ?>
		<?php if ($tosLink) : ?>
		<p>
			<label>
				<input name="<?php echo $control ?>[tos]" type="checkbox" value="1" class="inputbox required" required="required" />
			</label>
			<small><?php echo JText::sprintf('MOD_FRESHMAIL2_TOSLINK_TEXT', JRoute::_($tosLink)) ?></small>
		</p>
		<?php endif ?>

		<?php // Submit button ?>
		<button type="submit" class="button validate"><?php echo JText::_('MOD_FRESHMAIL2_SUBSCRIBE') ?></button>

		<?php echo JHtml::_('form.token') ?>
	</fieldset>
</form>
