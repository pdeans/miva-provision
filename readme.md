> This package is **abandoned** and no longer maintained.

## Miva Remote Provision

PHP library consisting of a full toolkit for interacting with Miva's remote provisioning module. The library components include tools for creating Miva provision xml markup, sending provision xml requests, and capturing provision xml responses.

### Installation

Install via [Composer](https://getcomposer.org/).

```shell
composer require pdeans/miva-provision
```

### Usage

First, create a new "Provision" manager instance. The manager instance takes 3 required parameters:

1. **Store Code** - Store Settings > Store Code
2. **XML Request URL** - Domain Settings > Remote Provisioning > XML Request URL
3. **Access Token** - Domain Settings > Remote Provisioning > Access Token

```php
use pdeans\Miva\Provision\Manager as Provision;

$store_code = 'PS';
$url = 'http://www.example.com/mm5/json.mvc?Function=Module&Module_Code=remoteprovisioning&Module_Function=XML';
$token = '12345';

$prv = new Provision($store_code, $url, $token);
```

Once the manager instance has been created, it may be used to generate provision xml markup, as well as send provision requests.

### Creating Provision XML Tags

The library utilizes the simple and extensive pdeans [XML Builder](https://github.com/pdeans/xml-builder) package to easily generate Miva provisioning tags.

#### Using The Provision Tag Builder

The `create` method is used to generate a provision xml tag. The `create` method takes the name of the root element (provisioning tag name) as the first argument, and an associative array consisting of the data to build the root attribute elements and/or child elements as the second argument.

Here is a simple example:

```php
$xml = $prv->create('Category_Add', [
    '@tags' => [
        'Code' => 'Tools',
        'Name' => $prv->cdata('Class Tools and Skill Kits'),
    ],
]);
```

This will produce the following xml:

```xml
<Category_Add>
    <Code>Tools</Code>
    <Name><![CDATA[Class Tools and Skill Kits]]></Name>
</Category_Add>
```

#### Parent/Child Elements

Notice how the array key-values function under the `@tags` array from the above example. The keys represent the xml element names, and the values represent the xml element values. Child tags can also be nested following this pattern with the parent element represented by the array key, and the array value consisting of an array of the child elements as key-value pairs. This pattern can be repeated as needed to nest subsequent child elements.

#### Element Value Helpers

The `cdata` helper method can be used to wrap an element value in a `<![CDATA[]]>` tag, while the `decimal` helper method can be used to format a decimal number into a standard decimal format, rounding to 2 decimals by default and stripping out commas. The `decimal` helper method accepts an optional second parameter to set the precision.

```php
// Output: <![CDATA[Class Tools and Skill Kits]]>
echo $prv->cdata('Class Tools and Skill Kits');

// Output: 49.00
echo $prv->decimal(49.0000000);

// Output: 49.001
echo $prv->decimal(49.0005, 3);
```

#### Reserved Keys

The `@tags` key represents one of 3 reserved keys (each containing shortcut key counterparts) that the xml builder uses to parse and generate the xml. The reserved keys are as follows:

**@attributes Key**
_Shortcut: **@a**_

The `@attributes` key is used to create xml element attributes. The `@a` key is also supported as a shortcut for the `@attributes` key.

Examples:

```php
$xml = $prv->create('CategoryProduct_Assign', [
    '@attributes' => [
        'category_code' => 'Food',
        'product_code'  => 'ale-gallon',
    ],
]);

$xml = $prv->create('CategoryProduct_Assign', [
    '@a' => [
        'category_code' => 'Food',
        'product_code'  => 'ale-gallon',
    ],
]);
```

XML Produced:

```xml
<CategoryProduct_Assign category_code="Food" product_code="ale-gallon"/>
```

**@tags Key**
_Shortcut: **@t**_

The `@tags` key accepts an associative array of data to build the root element's children. The `@t` key is also supported as a shortcut for the `@tags` key.

Examples:

```php
$xml = $prv->create('ProductAttribute_Add', [
    '@a' => [
        'product_code' => 'chest',
    ],
    '@tags' => [
        'Code'   => 'lock',
        'Type'   => 'select',
        'Prompt' => $prv->cdata('Lock'),
    ],
]);

$xml = $prv->create('ProductAttribute_Add', [
    '@a' => [
        'product_code' => 'chest',
    ],
    '@t' => [
        'Code'   => 'lock',
        'Type'   => 'select',
        'Prompt' => $prv->cdata('Lock'),
    ],
]);
```

XML Produced:

```xml
<ProductAttribute_Add product_code="chest">
    <Code>lock</Code>
    <Type>select</Type>
    <Prompt><![CDATA[Lock]]></Prompt>
</ProductAttribute_Add>
```

**@value Key**
_Shortcut: **@v**_

The `@value` key explicitly sets an xml element value. Generally, this is only required on xml elements that require both attributes and a value to be set. The `@v` key is also supported as a shortcut for the `@value` key.

Examples:

```php
$xml = $prv->create('Module', [
    '@attributes' => [
        'code' => 'customfields',
        'feature' => 'fields_prod',
    ],
    '@tags' => [
        'ProductField_Value' => [
            '@attributes' => [
                'product' => 'chest',
                'field' => 'armor_type',
            ],
            '@value' => 'wood',
        ],
    ],
]);

$xml = $prv->create('Module', [
    '@a' => [
        'code' => 'customfields',
        'feature' => 'fields_prod',
    ],
    '@t' => [
        'ProductField_Value' => [
            '@a' => [
                'product' => 'chest',
                'field' => 'armor_type',
            ],
            '@v' => 'wood',
        ],
    ],
]);
```

XML Produced:

```xml
<Module code="customfields" feature="fields_prod">
    <ProductField_Value product="chest" field="armor_type">wood</ProductField_Value>
</Module>
```

Note that the `@tags` key is used on the first level only of the associative array of tag data, as it represents the child tag data, while the other two reserved keys can be used on any sub-level throughout the associative array.

#### Repeated Tags

Sometimes repeated tags are used in xml, which does not play nice with associative array key-value pairs. To circumvent this, the element name is still passed as the array key, however, the array value consists of a sequential array of arrays with the tag data.

```php
$xml = $prv->create('Order_Add', [
    '@t' => [
        'Charges' => [
            'Charge' => [
                [
                    'Type' => 'SHIPPING',
                    'Description' => 'Shipping: UPS Ground',
                    'Amount' => 5.95
                ],
                [
                    'Type' => 'TAX',
                    'Description' => 'Sales Tax',
                    'Amount' => 2.15
                ],
            ],
        ],
    ],
]);
```

XML Produced:

```xml
<Order_Add>
    <Charges>
        <Charge>
            <Type>SHIPPING</Type>
            <Description>Shipping: UPS Ground</Description>
            <Amount>5.95</Amount>
        </Charge>
        <Charge>
            <Type>TAX</Type>
            <Description>Sales Tax</Description>
            <Amount>2.15</Amount>
        </Charge>
    </Charges>
</Order_Add>
```

#### Self-closing Tags

To generate a self-closing element without attributes, pass a value of *null* as the array value.

```php
$xml = $prv->create('Order_Add', [
    '@t' => [
        'TriggerFulfillmentModules' => null,
    ],
]);
```

XML Produced:

```xml
<Order_Add>
    <TriggerFulfillmentModules />
</Order_Add>
```

### Sending Provision Requests

Provision requests are sent via the `send` method.

```php
$response = $prv->send($xml);
```

By default, the `send` method will automatically prepend the `Store` (with the current store code) and `Provision` tags to the xml data passed to the method. If you wish for the functionality to be ommited, simply pass *true* as the second parameter.

```php
$response = $prv->send($xml, true);
```

### Append Tag Helpers

The following append helpers can be used to help prepare the provision request xml for sending. As noted above, the `send` method will call the `addStore` and `addProvision` helper methods by default to auto-prepare the xml data before sending.

```php
// Appends <Domain></Domain> element to xml
$xml = $prv->addDomain($xml);

// Appends <Store code="store code"></Store> element to xml
$xml = $prv->addStore($xml);

// Appends <Provision></Provision> element to xml
$xml = $prv->addProvision($xml);
```

### Provision Responses

A response object is returned with each provision request via the `send` method. The response object is an instance of the Zend Framework's [Diactoros](https://zendframework.github.io/zend-diactoros/) response object, which implements the [PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP message interface.

### Helper Methods

The manager instance also includes helper methods to easily return or swap out the store code, request url, or access token as needed. Helper method examples:

```php
// Get store code
$store_code = $prv->getStore();

// Set store code
$prv->setStore('store code');

// Get provision request url
$url = $prv->getUrl();

// Set provision request url
$prv->setUrl('request url');

// Get provision access token
$token = $prv->getToken();

// Set provision access token
$prv->setToken('access token');
```

### Version Notes

Version 2 currently requires PHP 5.4 or higher, however, version 1 can be used if support for PHP 5.3 is required.
