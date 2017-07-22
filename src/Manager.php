<?php

namespace pdeans\Miva\Provision;

class Manager
{
	protected $client;
	protected $builder;

	public function __construct($store_code, $url, $token)
	{
		$this->client  = new HttpClient($url, $token);
		$this->builder = new Builder($store_code);
	}

	public function addDomain($prv_xml)
	{
		return $this->builder->appendToDomain($prv_xml);
	}

	public function addProvision($prv_xml)
	{
		return $this->builder->appendToProvision($prv_xml);
	}

	public function addStore($prv_xml)
	{
		return $this->builder->appendToStore($prv_xml);
	}

	public function cdata($value)
	{
		return $this->builder->cdata($value);
	}

	public function create($prv_tag_name, array $tags)
	{
		return $this->builder->addPrvTag($prv_tag_name, $tags);
	}

	public function getStore()
	{
		return $this->builder->getStoreCode();
	}

	public function getToken()
	{
		return $this->client->getPrvToken();
	}

	public function getUrl()
	{
		return $this->client->getPrvUrl();
	}

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

	public function setStore($store_code)
	{
		$this->builder->setStoreCode($store_code);
	}

	public function setToken($token)
	{
		$this->client->setPrvToken($token);
	}

	public function setUrl($url)
	{
		$this->client->setPrvUrl($url);
	}
}