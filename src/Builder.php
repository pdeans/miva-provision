<?php

namespace pdeans\Miva\Provision;

use XMLWriter;

class Builder
{
	protected $store_code;
	protected $writer;

	public function __construct($store_code)
	{
		$this->writer = new XMLWriter();

		$this->setStoreCode($store_code);
	}

	public function setStoreCode($code)
	{
		$this->store_code = $code;
	}

	public function getStoreCode()
	{
		return $this->store_code;
	}

	public function addPrvTag($tag_name, $attributes = array())
	{
		$this->writer->openMemory();
		$this->writer->setIndent(true);
		$this->writer->setIndentString('    ');

		$this->writer->startElement($tag_name);

		if (!empty($attributes)) {
			foreach ($attributes as $name => $value) {
				$this->writer->writeAttribute($name, $value);
			}
		}
	}

	public function addTags(array $tags)
	{
		foreach ($tags as $name => $value) {
			if (is_array($value)) {
				// Check if this is a numeric array
				if ($value === array_values($value)) {
					foreach ($value as $tags) {
						$this->writer->startElement($name);
						$this->addTags($tags);
						$this->writer->endElement();
					}
				}
				else if ($name === '@attributes') {
					foreach ($value as $attr_name => $attr_value) {
						$this->writer->writeAttribute($attr_name, $attr_value);
					}
				}
				else if ($name === '@value') {
					$this->writer->writeRaw($value);
				}
				else {
					$this->writer->startElement($name);
					$this->addTags($value);
					$this->writer->endElement();
				}
			}
			else if ($name === '@value') {
				$this->writer->writeRaw($value);
			}
			else {
				$this->addTag($name, $value);
			}
		}
	}

	public function addTag($tag_name, $value = '')
	{
		$this->writer->startElement($tag_name);

		if ($value !== 'SELF_CLOSING') {
			$this->writer->writeRaw($value);
		}

		$this->writer->endElement();
	}

	public function getPrvTag()
	{
		$this->writer->endElement();

		return $this->writer->outputMemory();
	}

	public function cdata($value)
	{
		return '<![CDATA['.$value.']]>';
	}

	public function appendToStore($xml)
	{
		return '<Store code="'.$this->store_code.'">'.PHP_EOL.$xml.'</Store>';
	}

	public function appendToProvision($xml)
	{
		return '<Provision>'.PHP_EOL.$xml.PHP_EOL.'</Provision>';
	}
}