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

use pdeans\Builders\XmlBuilder;

/**
 * Miva provision xml tag builder
 */
class Builder extends XmlBuilder
{
	/**
	 * Store code
	 *
	 * @var string
	 */
	protected $store_code;

	/**
	 * Create an xml builder object
	 *
	 * @param string|null  $store_code  Store code
	 */
	public function __construct($store_code = null)
	{
		if ($store_code !== null) {
			$this->setStoreCode($store_code);
		}
	}

	/**
	 * Set the store code
	 *
	 * @param string  $code  Store code
	 */
	public function setStoreCode($code)
	{
		$this->store_code = $code;
	}

	/**
	 * Get the store code
	 *
	 * @return string
	 */
	public function getStoreCode()
	{
		return $this->store_code;
	}

	/**
	 * Append xml markup to <Store> tag
	 *
	 * @param string  $xml  Xml markup
	 * @return string
	 */
	public function appendToStore($xml)
	{
		return '<Store code="'.$this->store_code.'">'.$xml.'</Store>';
	}

	/**
	 * Append xml markup to <Domain> tag
	 *
	 * @param string  $xml  Xml markup
	 * @return string
	 */
	public function appendToDomain($xml)
	{
		return '<Domain>'.$xml.'</Domain>';
	}

	/**
	 * Append xml markup to <Provision> tag
	 *
	 * @param string  $xml  Xml markup
	 * @return string
	 */
	public function appendToProvision($xml)
	{
		return '<Provision>'.$xml.'</Provision>';
	}
}
