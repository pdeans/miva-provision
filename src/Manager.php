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

	public function setUrl($url)
	{
		$this->client->setPrvUrl($url);
	}

	public function getUrl()
	{
		return $this->client->getPrvUrl();
	}

	public function setToken($token)
	{
		$this->client->setPrvToken($token);
	}

	public function getToken()
	{
		return $this->client->getPrvToken();
	}

	public function send($prv_request, $minify = false)
	{
		return $this->client->sendRequest($this->addProvision($prv_request), $minify);
	}

	public function setStore($store_code)
	{
		$this->builder->setStoreCode($store_code);
	}

	public function getStore()
	{
		return $this->builder->getStoreCode();
	}

	public function addStore($prv_xml)
	{
		return $this->builder->appendToStore($prv_xml);
	}

	public function addProvision($prv_xml)
	{
		return $this->builder->appendToProvision($prv_xml);
	}

	public function cdata($value)
	{
		return $this->builder->cdata($value);
	}

	public function create($prv_tag_name, $attributes = array())
	{
		$this->builder->addPrvTag($prv_tag_name, $attributes);

		return $this;
	}

	public function tags($tags)
	{
		$this->builder->addTags($tags);

		return $this;
	}

	public function tag($tag_name, $value = '')
	{
		$this->builder->addTag($tag_name, $value);

		return $this;
	}

	public function get()
	{
		return $this->builder->getPrvTag();
	}
}