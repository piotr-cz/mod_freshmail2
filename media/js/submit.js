/*!
 * @package     Freshmail2.Site
 * @subpackage  mod_freshmail2
 *
 * @copyright   Copyright (C) 2013 - 2017 Piotr Konieczny. All rights reserved.
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

		// Skip when message container is not available
		if (!document.getElementById('system-message-container')) {
			return;
		}

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
				{success : [response.data.message || 'OK']} :
				{error   : [response.message || 'Unknown error']}
			)
		;

		try {
			Joomla.renderMessages(messages);
		} catch (e) {
			var fallbackMessages = [];

			for (type in messages) {
				fallbackMessages.push(messages[type].join(', '));
			}

			window.alert(fallbackMessages.join('; '));
		}

		// Redirect
		if (response.data && response.data.redirectUrl) {
			window.location.href = response.data.redirectUrl;
		}
	};

	/**
	 * Failure (ie. parseerror)
	 * @param {Object} jqXHR
	 * @param {string} responseStatus
	 * @param {string} errorThrown
	 */
	function onSubmitError(jqXHR, responseStatus, errorThrown) {
		try {
			Joomla.renderMessages({
				'error': [responseStatus || 'Unknown error']
			});
		} catch (e) {
			window.alert(responseStatus || 'Unknown error');
		}
	};

}(jQuery, Joomla));
