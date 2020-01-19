# Data Transfer

[![Travis](https://travis-ci.org/Articus/DataTransfer.svg?branch=master)](https://travis-ci.org/Articus/DataTransfer)
[![Coveralls](https://coveralls.io/repos/github/Articus/DataTransfer/badge.svg?branch=master)](https://coveralls.io/github/Articus/DataTransfer?branch=master)
[![Codacy](https://api.codacy.com/project/badge/Grade/2ec15ac8c40c4a709e7662e9c7124fad)](https://www.codacy.com/app/articusw/DataTransfer?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/DataTransfer&amp;utm_campaign=Badge_Grade)

This library provides a "validating hydrator", a service that merges source data to destination data only if destination data remains valid after that. Source and destination can be anything - scalars, arrays, objects... So either you want to make a partial update of ORM entity with parsed JSON from HTTP-request or produce a plain DTO from this entity to send in AMQP-message this library can help you to do that in a neat convenient way. 

## Why?
Personally I just needed something to easily update DTOs and Doctrine entities from untrusted sources 
(like request parsed body, request headers, request query parameters and etc). Something like [request body converter from FOSRestBundle](https://symfony.com/doc/master/bundles/FOSRestBundle/request_body_converter_listener.html) and [JMS Serializer](http://jmsyst.com/libs/serializer), but more flexible. 
The initial prototype was extremely useful for building APIs and after using it in several production projects 
I finally decided to make it a separate library. Hopefully, it will be useful for someone else.
     
## How to install?

Just add `"articus/data-transfer": "*"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you want to use.

## How to use?

Library is supposed to be used via [Zend Service Manager](https://docs.zendframework.com/zend-servicemanager/) but 
it should be possible to integrate it in any project that can provide `Interop\Container\ContainerInterface`.

### Metadata
First of all you need to declare metadata for classes that you would like to use with data transfer service. To do this 
you need to decorate each class property that should be used for transferring with special annotation:

```PHP
<?php
use Articus\DataTransfer\Annotation as DTA;

class Sample
{
    /**
     * Usual public property will be accessed directly
     * @DTA\Data()
     */
    public $property;

    /**
     * Protected or private property will be accessed by conventional getter and setter if they exist
     * @DTA\Data()
     */
    protected $propertyWithAccessors;
    public function getPropertyWithAccessors()
    {
        return $this->propertyWithAccessors;
    }
    public function setPropertyWithAccessors($propertyWithAccessors)
    {
        $this->propertyWithAccessors = $propertyWithAccessors;
    }

    /**
     * And that is how you can set custom getter and setter names for protected or private property
     * @DTA\Data(getter="customGetAccessor", setter="customSetAccessor")
     */
    protected $propertyWithCustomAccessors;
    public function customGetAccessor()
    {
        return $this->propertyWithCustomAccessors;
    }
    public function customSetAccessor($propertyWithCustomAccessors)
    {
        $this->propertyWithCustomAccessors = $propertyWithCustomAccessors;
    }

    /**
     * If you property does not have setter (or getter) just set empty string
     * @DTA\Data(setter="")
     */
    protected $propertyWithoutSetter;
    public function getPropertyWithoutSetter()
    {
        return $this->propertyWithoutSetter;
    }
}
```

If you need some special logic to extract and hydrate property value you can declare hydration strategy for this property
(see `Articus\DataTransfer\Strategy\StrategyInterface` for details).

```PHP
<?php
use Articus\DataTransfer\Annotation as DTA;

class Sample
{
    /**
     * Library provides simple strategy for embedded objects
     * @DTA\Data()
     * @DTA\Strategy(name="Object", options={"type":MyClass::class})
     * @var MyClass
     */
    public $object;

    /**
     * ... and simple strategy for lists of embedded objects
     * @DTA\Data()
     * @DTA\Strategy(name="ObjectArray", options={"type":MyClass::class})
     * @var MyClass[]
     */
    public $objectArray;

    /**
     * You can also use your own strategy if it is registered in strategy plugin manager
     * @DTA\Data()
     * @DTA\Strategy(name=MyStrategy::class)
     * @var mixed
     */
    public $custom;
}
```

And as you may have guessed there is a special annotation to add validation constraints. 
Any validator registered in validator plugin manager can be used (see `Articus\DataTransfer\Validator\ValidatorInterface` for details).
Library also provides `Articus\DataTransfer\Validator\Factory\Zend` - simple abstract factory to integrate validators from [Zend Validator](https://docs.zendframework.com/zend-validator/). 
 
```PHP
<?php
use Articus\DataTransfer\Annotation as DTA;

/**
 * You can set validator for whole object value 
 * @DTA\Validator(name=MyClassValidator::class)
 */
class Sample
{
    /**
     * You can set validator for specific property
     * @DTA\Data()
     * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5})
     * @var string
     */
    public $string;

    /**
     * If you set several validators they will be tested one by one.
     * Validators with higher priority will be executed earlier.
     * @DTA\Data()
     * @DTA\Validator(name="Hex")
     * @DTA\Validator(name="StringLength",options={"min": 1}, priority=3)
     * @DTA\Validator(name="StringLength",options={"max": 5}, priority=2)
     * @var string
     */
    public $hexString;

    /**
     * Even if there is no validators value will be tested not to be null. 
     * Mark property "nullable" if you do not want that. 
     * And if you set any validators for nullable property they will be executed only for not null value. 
     * @DTA\Data(nullable=true)
     * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5})
     * @var string
     */
    public $nullableString;

    /**
     * Library provides simple validator for embedded objects
     * @DTA\Data()
     * @DTA\Strategy(name="Object", options={"type":MyClass::class})
     * @DTA\Validator(name="TypeCompliant", options={"type":MyClass::class})
     * @var MyClass
     */
    public $object;

    /**
     * ... and simple validator for arrays
     * @DTA\Data()
     * @DTA\Validator(name="Collection",options={"validators":{
     *     @DTA\Validator(name="StringLength",options={"min": 1, "max": 5}),
     *     @DTA\Validator(name="Hex"),
     * }})
     */
    public $stringArray;
} 
```

### Configuration
After declaring metadata you need to add few lines to you configuration (example is in YAML just for readability):

```YAML
# Register required services in container 
dependencies:
  factories:
    Articus\DataTransfer\Service: Articus\DataTransfer\Factory
    Articus\DataTransfer\MetadataProvider\Annotation: Articus\DataTransfer\MetadataProvider\Factory\Annotation
    Articus\DataTransfer\Strategy\PluginManager: Articus\DataTransfer\Strategy\Factory\PluginManager
    Articus\DataTransfer\Validator\PluginManager: Articus\DataTransfer\Validator\Factory\PluginManager
    Zend\Validator\ValidatorPluginManager: Zend\Validator\ValidatorPluginManagerFactory
  # Default metadata provider service allows to get metadata both for classes and for class fields so two aliases for single service 
  aliases:
    Articus\DataTransfer\ClassMetadataProviderInterface: Articus\DataTransfer\MetadataProvider\Annotation
    Articus\DataTransfer\FieldMetadataProviderInterface: Articus\DataTransfer\MetadataProvider\Annotation

# Configure metadata provider
Articus\DataTransfer\MetadataProvider\Annotation:
  # Configure dedicated Zend Cache Storage for class metadata (see Zend\Cache\StorageFactory)
  cache:
    adapter: filesystem
    options:
      cache_dir: data/DataTransfer
      namespace: dt
    plugins:
      serializer:
        serializer: phpserialize
  # ... or use existing service inside container
  #cache: MyMetadataCacheStorage

# Configure hydration strategy plugin manager (see Articus\DataTransfer\Strategy\PluginManager for details)
Articus\DataTransfer\Strategy\PluginManager:
  invokables:
    MySampleStrategy: My\SampleStrategy

# Configure validator plugin manager (see Articus\DataTransfer\Validator\PluginManager for details)
Articus\DataTransfer\Validator\PluginManager:
  invokables:
    MySampleValidator: My\SampleValidator
  abstract_factories:
    - Articus\DataTransfer\Validator\Factory\Zend
  
```

### Usage
Finally you just need to get service `Articus\DataTransfer\Service` from container and call appropriate  `transfer*` method.

#### Transfer data between objects 
```PHP
<?php
use Articus\DataTransfer\Service;

$from = new MyClassA();
$to = new MyClassB();
$violations = $container->get(Service::class)->transferTypedData($from, $to);
if (empty($violations))
{
    //Transfer was successful
}
```

#### Transfer data from array to object 
```PHP
<?php 
use Articus\DataTransfer\Service;

$from = [];
$to = new MyClassB();
$violations = $container->get(Service::class)->transferToTypedData($from, $to);
```

#### Transfer data from object to array 
```PHP
<?php 
use Articus\DataTransfer\Service;

$from = new MyClassA();
$to = [];
$violations = $container->get(Service::class)->transferFromTypedData($from, $to);
//Or if you want only to extract without merge  
$to = $container->get(Service::class)->extractFromTypedData($from);
```

### Subsets

Sometimes you may want to assign several different variants of metadata for your class. To achieve that simply assign same **subset** for annotations that belong to same variant and pass name of the required **subset** during transfer:
```PHP
<?php
use Articus\DataTransfer\Annotation as DTA;
use Articus\DataTransfer\Service;
/**
 * DTO that can be filled from query parameters and from parsed JSON body.
 * Query parameters are always strings and it is not possible to use same validators and same strategies for both sources.
 */ 
class FromQueryOrJson
{
    /**
     * @DTA\Data(subset="query") 
     * @DTA\Strategy(name="IntFromString", subset="query")
     * @DTA\Validator(name="IsIntInString", subset="query")
     * @DTA\Data(subset="json") 
     * @DTA\Validator(name="IsInt", subset="json")
     * @var int
     */
    public $test;
}
//Fill DTO from query
$query = /* your favourite way to get request query parameters */;
$dto = new FromQueryOrJson();
$violations = $container->get(Service::class)->transferToTypedData($query, $dto, 'query');
//Fill DTO from parsed JSON
$json = json_decode(/* your favorite way to get request body */);
$dto = new FromQueryOrJson();
$violations = $container->get(Service::class)->transferToTypedData($json, $dto, 'json');
```
    
## Enjoy!
I really hope that this library will be useful for someone except me. 
It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
