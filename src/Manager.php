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

/**
 * Manager class for Builder and HttpClient classes
 */
class Manager
{
	/**
	 * Http client object
	 *
	 * @var \pdeans\Miva\Provision\HttpClient
	 */
	protected $client;

	/**
	 * Builder object
	 *
	 * @var \pdeans\Miva\Provision\Builder
	 */
	protected $builder;

	/**
	 * Create a Manager object
	 *
	 * @param string  $store_code  Miva store code
	 * @param string  $url  Remote provision module url "XML Request URL"
	 * @param string  $token  Remote provision "Access Token"
	 * @throws \InvalidArgumentException  Emtpy values passed in for $store_code|$url|$token
	 */
	public function __construct($store_code = null, $url = null, $token = null)
	{
		if ($store_code === null || $store_code === '') {
			throw new InvalidArgumentException(
				'Invalid argument passed to '.__METHOD__.'. Store code cannot be empty.'
			);
		}

		if ($url === null || $url === '') {
			throw new InvalidArgumentException(
				'Invalid argument passed to '.__METHOD__.'. Url cannot be empty.'
			);
		}

		if ($token === null || $token === '') {
			throw new InvalidArgumentException(
				'Invalid argument passed to '.__METHOD__.'. Token cannot be empty.'
			);
		}

		$this->client  = new HttpClient($url, $token);
		$this->builder = new Builder($store_code);
	}

	/**
	 * Prepend <Domain> tag to provision xml
	 *
	 * @param string  $prv_xml  Provision request xml
	 * @return string
	 */
	public function addDomain($prv_xml)
	{
		return $this->builder->appendToDomain($prv_xml);
	}

	/**
	 * Prepend <Provision> tag to provision xml
	 *
	 * @param string  $prv_xml  Provision request xml
	 * @return string
	 */
	public function addProvision($prv_xml)
	{
		return $this->builder->appendToProvision($prv_xml);
	}

	/**
	 * Prepend <Store> tag to provision xml
	 *
	 * @param string  $prv_xml  Provision request xml
	 * @return string
	 */
	public function addStore($prv_xml)
	{
		return $this->builder->appendToStore($prv_xml);
	}

	/**
	 * Wrap value in <![CDATA[]]> tag
	 *
	 * @param mixed  $value  Xml tag value
	 * @return string
	 */
	public function cdata($value)
	{
		return $this->builder->cdata($value);
	}

	/**
	 * Create a remote provision xml tag
	 *
	 * @param string  $prv_tag_name  Provision tag name
	 * @param array  $tags  Array of xml tag markup (see Builder class)
	 * @return string  Generated provision tag xml markup
	 */
	public function create($prv_tag_name, array $tags)
	{
		return $this->builder->create($prv_tag_name, $tags);
	}

	/**
	 * Format decimal number
	 *
	 * @param string|int|float  $value  The decimal value
	 * @param int  $precision  Decimal precision
	 * @return string  Formatted decimal number
	 */
	public function decimal($value, $precision = 2)
	{
		return $this->builder->decimal($value, $precision);
	}

	/**
	 * Get store code
	 *
	 * @return string  Current store code
	 */
	public function getStore()
	{
		return $this->builder->getStoreCode();
	}

	/**
	 * Get access token
	 *
	 * @return string  Current access token
	 */
	public function getToken()
	{
		return $this->client->getPrvToken();
	}

	/**
	 * Get xml request url
	 *
	 * @return string  Current xml request url
	 */
	public function getUrl()
	{
		return $this->client->getPrvUrl();
	}

	/**
	 * Send a remote provision request
	 *
	 * @param string  $prv_request  Request data (provision xml markup)
	 * @param boolean  $no_add_tags  Flag to prepend <Provision> and <Store> tags to request data
	 * @return \stdClass  Provision response object
	 */
	public function send($prv_request, $no_add_tags = false)
	{
		if ($no_add_tags) {
			return $this->client->sendRequest($prv_request);
		}

		return $this->client->sendRequest(
			$this->addProvision(
				$this->addStore($prv_request)
			)
		);
	}

	/**
	 * Set the store code
	 *
	 * @param string  $store_code  Store code
	 */
	public function setStore($store_code)
	{
		$this->builder->setStoreCode($store_code);
	}

	/**
	 * Set the access token
	 *
	 * @param string  $token  Access token
	 */
	public function setToken($token)
	{
		$this->client->setPrvToken($token);
	}

	/**
	 * Set the xml request url
	 *
	 * @param string  $url  Xml request url
	 */
	public function setUrl($url)
	{
		$this->client->setPrvUrl($url);
	}
}
