<?php
/**
 * @package     Freshmail2.Site
 * @subpackage  lib_freshmail
 *
 * @copyright   Copyright (C) 2013 - 2014 piotr_cz. Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Import dependencies
JLoader::import('joomla.http.http');
JLoader::import('joomla.http.httpfactory');
JLoader::import('joomla.http.transport.curl');

/**
 * Freshmail Rest Api extended to use JHttp package
 *
 * @package     Freshmail2.Site
 * @subpackage  lib_freshmail
 * @since       1.0
 *
 * @note  Package is fully backward compatible with FmRestApi
 * @note  code changes to origin:
 *        - doRquest uses JHttp (resolved problems with SSL certificate)
 *        - setTimeout method
 *        - had to duplicate methods to gain access to private properties
 *
 * @see  https://github.com/FreshMail/REST-API
 */
class JFmRestApi // extends FmRestApi
{
	/**
	 * Host URI
	 *
	 * @var    string
	 */
	const host = 'https://app.freshmail.pl/';

	/**
	 * API prefix
	 *
	 * @var    string
	 */
	const prefix = 'rest/';

	/**
	 * API Key
	 *
	 * @var    string
	 */
	private $strApiKey;

	/**
	 * API Secret
	 *
	 * @var    string
	 */
	private $strApiSecret;

	/**
	 * Parsed response
	 *
	 * @var    array
	 */
	private $response;

	/**
	 * Raw response
	 *
	 * @var    string
	 */
	private $rawResponse;

	/**
	 * Response HTTP status code
	 *
	 * @var    integer
	 */
	private $httpCode;

	/**
	 * Request content type
	 *
	 * @var    string
	 */
	private $contentType = 'application/json';

	/**
	 * Response errors
	 *
	 * @var    array
	 */
	protected $errors = array();

	/**
	 * Timeout
	 *
	 * @var    integer
	 */
	private $timeout;

		/**
	 * Get parsed response
	 *
	 * @return  array
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Get raw response
	 *
	 * @return  string
	 */
	public function getRawResponse()
	{
		return $this->rawResponse;
	}

	/**
	 * Get response HTTP status code
	 *
	 * @return  integer
	 */
	public function getHttpCode()
	{
		return $this->httpCode;
	}

	/**
	 * Get API timeout
	 *
	 * @return  integer  Timeout
	 */
	public function getTimeout()
	{
		return $this->timeout;
	}

	/**
	 * Metoda ustawia klucz do API
	 *
	 * @param   string  $strKey
	 *
	 * @return  JFmRestApi  Returns itself to support chaining.
	 */
	public function setApiKey($strKey)
	{
		$this->strApiKey = $strKey;

		return $this;
	}

	/**
	 * Metoda ustawia secret do API
	 *
	 * @param   string  $strSecret
	 *
	 * @return  JFmRestApi  Returns itself to support chaining.
	 */
	public function setApiSecret($strSecret)
	{
		$this->strApiSecret = $strSecret;

		return $this;
	}

	/**
	 * Set request content type
	 *
	 * @param   string
	 *
	 * @return  JFmRestApi  Returns itself to support chaining.
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Set API timeout
	 *
	 * @param   integer  $timeout
	 *
	 * @return  JFmRestApi  Returns itself to support chaining.
	 */
	public function setTimeout($timeout = 60)
	{
		$this->timeout = $timeout;

		return $this;
	}

	/**
	 * Do a request
	 *
	 * @param   string      $strUrl           REST command URL
	 * @param   null|array  $arrParams        Query parameters
	 * @param   boolean     $boolRawResponse  Return raw response
	 *
	 * @return  array  Parsed response
	 *
	 * @note    `No HTTP response received.` means no connection
	 */
	public function doRequest($strUrl, $arrParams = array(), $boolRawResponse = false)
	{
		// Get POST data as a string
		if (empty($arrParams))
		{
			$strPostData = $signData = '';
		}
		else if ($this->contentType == 'application/json')
		{
			$strPostData = $signData = json_encode($arrParams);
		}
		else
		{
			// JHttpTransportCurl Builds a query for us
			$strPostData = $arrParams;
			$signData = http_build_query($arrParams);
		}


		// Get Signature
		$strSign = sha1($this->strApiKey . '/' . static::prefix . $strUrl . $signData . $this->strApiSecret);

		// Get URI
		$requestUri = static::host . static::prefix . $strUrl;

		// Get Headers
		$arrHeaders = array(
			'X-Rest-ApiKey' =>  $this->strApiKey,
			'X-Rest-ApiSign' => $strSign,
		);

		// Set Content Type
		if ($this->contentType)
		{
			/* Workaround nasty J2.5 bug:
			 * use `Content-type` Transport will add `Content-type:application/x-www-form-urlencoded`
			 * and request will result in auth denied. (ERROR 1000)
			 */
			if (version_compare(JVERSION, '3', '<'))
			{
				$arrHeaders['Content-type'] = $this->contentType;
			}
			// J3.0 and J2.5 JHttpTransportSocket
			else
			{
				$arrHeaders['Content-Type'] = $this->contentType;
			}
		}

		// Setup options
		$options = new JRegistry(array(
			/* @type array $headers */
			'headers' => $arrHeaders,
			/* @type integer $timeout */
			'timeout' => $this->timeout,
			/* @type string $userAgent */
			'userAgent' => null
		));

		// Get HTTP Client
		// Note: JHttpFactory is available since Platform 12.1 (J2.5.15)
		if (class_exists('JHttpFactory'))
		{
			$jHttp = JHttpFactory::getHttp($options, 'Curl');
		}
		else
		{
			// Note: don't use JHttpTransportSocket
			$jHttp = new JHttp($options, new JHttpTransportCurl(new JRegistry));
		}


		$httpMethod = ($strPostData) ? 'POST' : 'GET';

		// Send request.
		// Note: For <= J3.x headers must be included in request itself.
		/* @type JHttpResponse Object */
		/* @throws UnexpectedValueException */
		$responseObject = ($strPostData) 
			? $jHttp->post($requestUri, $strPostData, $arrHeaders) 
			: $jHttp->get($requestUri, $arrHeaders);

		// Assign response variables
		$this->rawResponse = $responseObject->body;
		$this->httpCode = $responseObject->code;

		// Return raw response
		if ($boolRawResponse)
		{
			return $this->rawResponse;
		}


		$this->response = json_decode($this->rawResponse, true);

		// Throw response exceptions
		if ($this->httpCode !== 200)
		{
			$this->errors = $this->response['errors'];

			if (is_array($this->errors))
			{
				foreach ($this->errors as $arrError)
				{
					throw new RestException($arrError['message'], $arrError['code']);
				}
			}
		}

		if (!is_array($this->response))
		{
			throw new Exception('Invalid json response');
		}

		return $this->response;
	}
}

/**
 * FM Rest Exception object
 *
 * @package     Freshmail2.Site
 * @subpackage  lib_freshmail
 *
 * @since       2.0
 */
class RestException extends Exception
{
}
