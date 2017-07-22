<?php

namespace pdeans\Miva\Provision;

use UnexpectedValueException;
use XMLWriter;

/**
 * Miva provision xml tag builder
 */
class Builder
{
	/**
	 * Store code
	 *
	 * @var string
	 */
	protected $store_code;

	/**
	 * Xml writer object
	 *
	 * @var \XMLWriter
	 */
	protected $writer;

	/**
	 * Create an xml builder object
	 *
	 * @param string|null  $store_code  Store code
	 */
	public function __construct($store_code = null)
	{
		$this->writer = new XMLWriter;

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
	 * Create a provsion xml tag
	 *
	 * @param string  $tag_name  Provision tag name
	 * @param array  $tags  Associative array of xml tag data
	 * @throws \UnexpectedValueException  Invalid array for reserved tag value
	 */
	public function addPrvTag($tag_name, array $tags)
	{
		$this->writer->openMemory();
		$this->writer->setIndent(true);
		$this->writer->setIndentString('    ');

		$this->writer->startElement($tag_name);

		if (isset($tags['@attributes'])) {
			if (!is_array($tags['@attributes'])) {
				throw new UnexpectedValueException('Expected array for @attributes key');
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
				throw new UnexpectedValueException('Expected array for @tags key');
			}

			$this->addTags($tags['@tags']);
		}

		$this->writer->endElement();

		return $this->writer->outputMemory();
	}

	/**
	 * Generate child tag xml markup
	 *
	 * @param array  $tags  Child tag data
	 * @throws \UnexpectedValueException  Invalid array for reserved tag value
	 */
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
						throw new UnexpectedValueException('Expected array for @attributes key');
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

	/**
	 * Generate a standard xml tag
	 *
	 * @param string  $tag_name  Tag name
	 * @param mixed  $value  Tag value
	 */
	protected function addTag($tag_name, $value = null)
	{
		$this->writer->startElement($tag_name);

		if ($value !== null) {
			$this->writer->writeRaw($value);
		}

		$this->writer->endElement();
	}

	/**
	 * Wrap value in cdata tag
	 *
	 * @param mixed  $value  Tag value
	 * @return string
	 */
	public function cdata($value)
	{
		return '<![CDATA['.$value.']]>';
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