<?php

/*
 *
 * Miva Merchant
 *
 * This file and the source codes contained herein are the property of
 * Miva, Inc. Use of this file is restricted to the specific terms and
 * conditions in the License Agreement associated with this file. Distribution
 * of this file or portions of this file for uses not covered by the License
 * Agreement is not allowed without a written agreement signed by an officer of
 * Miva, Inc.
 *
 * Copyright 1998-2025 Miva, Inc. All rights reserved.
 * https://www.miva.com
 *
 */

namespace pdeans\Miva\Provision;

use InvalidArgumentException;
use pdeans\Http\Client;

/**
 * Http client for sending xml provision requests and responses
 */
class HttpClient
{
	/**
	 * Provision xml request url
	 *
	 * @var string
	 */
	protected $prv_url;

	/**
	 * Provision access token
	 *
	 * @var string
	 */
	protected $prv_token;

	/**
	 * HTTP client
	 *
	 * @var \pdeans\Http\Client
	 */
	protected $client;

	/**
	 * Create a HttpClient object
	 *
	 * @param string  $url  Provision xml request url
	 * @param string  $token  Provision access token
	 * @param array  $client_options  Http client (cURL) options
	 */
	public function __construct($url, $token, array $client_options = [])
	{
		$this->setPrvUrl($url);
		$this->setPrvToken($token);

		$this->client = new Client(!empty($client_options) ? $client_options : [
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
		]);
	}

	/**
	 * Set provision xml request url
	 *
	 * @param string  $url  Provision xml request url
	 */
	public function setPrvUrl($url)
	{
		$this->prv_url = $url;
	}

	/**
	 * Get provision xml request url
	 *
	 * @return string
	 */
	public function getPrvUrl()
	{
		return $this->prv_url;
	}

	/**
	 * Set provision access token
	 *
	 * @param string  $token  Provision access token
	 */
	public function setPrvToken($token)
	{
		$this->prv_token = $token;
	}

	/**
	 * Get provision access token
	 *
	 * @return string
	 */
	public function getPrvToken()
	{
		return $this->prv_token;
	}

	/**
	 * Send provision request
	 *
	 * @param string  $request  Provision request xml
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \InvalidArgumentException  Empty or invalid provision request data
	 */
	public function sendRequest($request)
	{
		// Check if request was passed in
		if (!$request) {
			throw new InvalidArgumentException('No data was passed into the provision request');
		}

		return $this->client->post($this->prv_url, [
			'Content-type'             => 'text/xml',
			'Content-length'           => strlen($request),
			'MMProvision-Access-Token' => $this->prv_token,
		], $request);
	}
}
