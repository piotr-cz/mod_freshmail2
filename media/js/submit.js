/**
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2016 Piotr Konieczny. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {

	'use strict';

	// Skip when dependencies not loaded :o
	if (!jQuery || !Joomla) {
		return;
	}

	// Initialize on DOM ready
	jQuery(function() {
		jQuery('form[data-freshmail2]').on('submit', onFormSubmit);
	});

	return;

	/**
	 * Callback on form submitted
	 * [async]
	 * @param {jQuery.Event} ev
	 * @this  {HTMLFormElement}
	 */
	function onFormSubmit(ev) {

		ev && ev.preventDefault();

		var $form       = jQuery(this);
		var control     = $form.data('freshmail2');
		var queryParams = ['option=com_ajax', 'format=json', 'module=freshmail2', 'method=post', 'ignoreMessages=0', 'control=' + control];

		// Submit form
		jQuery.ajax({
			type : 'POST',
			url  : $form.attr('action'),
			data : queryParams.join('&') + '&' + $form.serialize()
		})
			.done(onSubmitSuccess)
			.fail(onSubmitError)
		;

		return;
	};

	/**
	 * Response OK
	 * @param {Object} response
	 * @param {string} responseStatus
	 * @param {Object} jqXHR
	 */
	function onSubmitSuccess(response, responseStatus, jqXHR) {
		// Successful subscription response contains message in data key, unsuccessful in message key
		var messages = response.messages ||
			(response.success ?
				{success : [response.data || 'OK']} :
				{error   : [response.message || 'Unknown error']}
			)
		;

		Joomla.renderMessages(messages);
	};

	/**
	 * Failure (ie. parseerror)
	 * @param {Object} jqXHR
	 * @param {string} responseStatus
	 * @param {string} errorThrown
	 */
	function onSubmitError(jqXHR, responseStatus, errorThrown) {
		Joomla.renderMessages({
			'error': [responseStatus || 'Unknown error']
		});
	};

}(jQuery, Joomla));
