/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
jQuery(function(){

	var $$forms = jQuery('form[data-freshmail2]');

	$$forms.on('submit', function( ev )
	{
		ev && ev.preventDefault();

		var $form = jQuery(this),
			control = $form.data('freshmail2')
			query = 'option=com_ajax&format=json&module=freshmail2&method=post&ignoreMessages=0&control=' + control;
		;

		// Submit form
		jQuery.ajax({
			type: 'post',
			data:  query + '&' + $form.serialize(),
			url: $form.attr('action'),

			// Response OK
			success: function(response, responseStatus, jqXHR)
			{
				var messages;

				if (response.success)
				{
					messages = response.messages || {'success': [response.messages || 'OK']};
				}
				else
				{
					messages = response.messages || {'error': [response.message || 'Uknown Error']};
				}

				Joomla.renderMessages(messages);
			},
			// Failure (ie. parseerror)
			error: function(jqXHR, responseStatus, error)
			{
				Joomla.renderMessages({
					'error': [ responseStatus || 'Unknown Error']
				});
			}
		});

		return;
	});

	return;
});
