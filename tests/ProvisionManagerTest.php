<?php

use pdeans\Miva\Provision\Manager;

class ProvisionManagerTest extends \PHPUnit_Framework_TestCase
{

	public function testProvisionManager($store_code = 'PS', $url = 'http://test.url.com', $token = 'testing123')
	{
		$manager = new Manager($store_code, $url, $token);

		$this->assertInstanceOf('pdeans\Miva\Provision\Manager', $manager);
	}

}