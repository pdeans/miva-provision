<?php

namespace pdeans\Miva\Provision;

use XMLWriter;

class Builder
{
	protected $store_code;
	protected $writer;

	public function __construct($store_code = null)
	{
		$this->writer = new XMLWriter();

		if ($store_code !== null) {
			$this->setStoreCode($store_code);
		}
	}

	public function setStoreCode($code)
	{
		$this->store_code = $code;
	}

	public function getStoreCode()
	{
		return $this->store_code;
	}

	public function addPrvTag($tag_name, array $tags)
	{
		$this->writer->openMemory();
		$this->writer->setIndent(true);
		$this->writer->setIndentString('    ');

		$this->writer->startElement($tag_name);

		if (isset($tags['@attributes'])) {
			if (!is_array($tags['@attributes'])) {
				throw new \Exception('Expected array for @attributes key');
			}

			foreach ($tags['@attributes'] as $name => $value) {
				$this->writer->writeAttribute($name, $value);
			}
		}

		if (isset($tags['@value'])) {
			$this->writer->writeRaw($tags['@value']);
		}
		else if (isset($tags['@tags'])) {
			if (!is_array($tags['@tags'])) {
				throw new \Exception('Expected array for @tags key');
			}

			$this->addTags($tags['@tags']);
		}

		$this->writer->endElement();
	}

	protected function addTags(array $tags)
	{
		foreach ($tags as $name => $value) {
			if (is_array($value)) {
				// Check if this is a sequential array
				if ($value === array_values($value)) {
					foreach ($value as $tags) {
						$this->writer->startElement($name);
						$this->addTags($tags);
						$this->writer->endElement();
					}
				}
				else if ($name === '@attributes') {
					if (!is_array($tags['@attributes'])) {
						throw new \Exception('Expected array for @attributes key');
					}

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

	protected function addTag($tag_name, $value = null)
	{
		$this->writer->startElement($tag_name);

		if ($value !== null) {
			$this->writer->writeRaw($value);
		}

		$this->writer->endElement();
	}

	public function getPrvTag()
	{
		return $this->writer->outputMemory();
	}

	public function cdata($value)
	{
		return '<![CDATA['.$value.']]>';
	}

	public function appendToStore($xml)
	{
		return '<Store code="'.$this->store_code.'">'.$xml.'</Store>';
	}

	public function appendToDomain($xml)
	{
		return '<Domain>'.$xml.'</Domain>';
	}

	public function appendToProvision($xml)
	{
		return '<Provision>'.$xml.'</Provision>';
	}
}