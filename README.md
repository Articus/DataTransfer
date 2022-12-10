# Data Transfer

[![GitHub Actions: Run tests](https://github.com/Articus/DataTransfer/workflows/Run%20tests/badge.svg)](https://github.com/Articus/DataTransfer/actions?query=workflow%3A%22Run+tests%22)
[![Coveralls](https://coveralls.io/repos/github/Articus/DataTransfer/badge.svg?branch=master)](https://coveralls.io/github/Articus/DataTransfer?branch=master)
[![Codacy](https://app.codacy.com/project/badge/Grade/2ec15ac8c40c4a709e7662e9c7124fad)](https://www.codacy.com/gh/Articus/DataTransfer/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Articus/DataTransfer&amp;utm_campaign=Badge_Grade)
This library provides a "validating hydrator", a service that patches destination data with source data only if destination data remains valid after that. Source and destination can be anything - scalars, arrays, objects... So either you want to make a partial update of ORM entity with parsed JSON from HTTP-request or produce a plain DTO from this entity to send in AMQP-message this library can help you to do that in a neat convenient way. 

## How it works?
Let's make few definitions:

* **typed data** - some complex, application-specific, rigidly structured data like object or array of objects. For example, DTO's or ORM entities.
* **untyped data** - the opposite of **typed data** - some simple, general-purpose, amorphous data like scalar or array of scalars or stdClass instance. For example, result of `json_decode` or `yaml_parse`.
* **extract** - an algorithm to convert **typed data** to **untyped data**
* **merge** - an algorithm to patch one piece of **untyped data** with another piece of **untyped data**     
* **validate** - an algorithm to check that **untyped data** is correct according some rules
* **hydrate** - an algorithm to patch **typed data** with **untyped data**

So if we have two pieces of **typed data** - *A* and *B* - this library does a rather simple thing to **transfer** *A* to *B*: it **merges** pieces of **untyped data** **extracted** from *A* and *B*, **validates** the result and **hydrates** *B* with **untyped data** **extracted** from *A* if validation is successful.   

## Why?
Personally I just needed something to easily update DTOs and Doctrine entities from untrusted sources 
(like request parsed body, request headers, request query parameters and etc). Something like [request body converter from FOSRestBundle](https://symfony.com/doc/master/bundles/FOSRestBundle/request_body_converter_listener.html) and [JMS Serializer](http://jmsyst.com/libs/serializer), but more flexible. 
The initial prototype was extremely useful for building APIs and after using it in several production projects 
I finally decided to make it a separate library. Hopefully, it will be useful for someone else.

     
## How to install?

Just add `"articus/data-transfer"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require) and check [packages suggested by the library](https://getcomposer.org/doc/04-schema.md#suggest) for extra dependencies of optional components you may want to use.

> *Note* - library has [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) as direct dependency but only because of [plugin managers](https://docs.laminas.dev/laminas-servicemanager/plugin-managers/). So you can use this library with any PSR-11 container you like.  
 
## How to use?

Library provides a single service `Articus\DataTransfer\Service` that allows transferring data in various ways. So first of all you need to register it in your PSR-11 container. Here is a sample configuration for [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) (example is in YAML just for readability):

```YAML
# Register required services in container
dependencies:
  factories:
    # Service to inject wherever you need data transfer
    Articus\DataTransfer\Service: Articus\DataTransfer\Factory
    # ..and its dependencies
    Articus\DataTransfer\MetadataProvider\Annotation: Articus\DataTransfer\MetadataProvider\Factory\Annotation
    Articus\DataTransfer\Strategy\PluginManager: Articus\DataTransfer\Strategy\Factory\PluginManager
    Articus\DataTransfer\Validator\PluginManager: Articus\DataTransfer\Validator\Factory\PluginManager
    # Optional - only if you want to use validators from laminas/laminas-validator
    Laminas\Validator\ValidatorPluginManager: Laminas\Validator\ValidatorPluginManagerFactory
  # Default metadata provider service allows to get metadata both for classes and for class fields so two aliases for single service
  aliases:
    Articus\DataTransfer\ClassMetadataProviderInterface: Articus\DataTransfer\MetadataProvider\Annotation
    Articus\DataTransfer\FieldMetadataProviderInterface: Articus\DataTransfer\MetadataProvider\Annotation

# Configure metadata provider
Articus\DataTransfer\MetadataProvider\Annotation:
  # Configure directory to store cached class metadata
  cache:
    directory: ./data
  # ... or use existing service implementing Psr\SimpleCache\CacheInterface (PSR-16)
  #cache: MyMetadataCache

# Configure strategy plugin manager (see Articus\DataTransfer\Strategy\PluginManager for details)
Articus\DataTransfer\Strategy\PluginManager:
  invokables:
    MySampleStrategy: My\SampleStrategy

# Configure validator plugin manager (see Articus\DataTransfer\Validator\PluginManager for details)
Articus\DataTransfer\Validator\PluginManager:
  invokables:
    MySampleValidator: My\SampleValidator
  # Optional - only if you want to use validators from laminas/laminas-validator
  abstract_factories:
    - Articus\DataTransfer\Validator\Factory\Laminas
  
```

That is the only requirement to use `Articus\DataTransfer\Service::transfer` method that provides the most explicit and fine-grained control over data transfer.

If you provide some additional metadata for classes that you would like to use with data transfer service several more convenient methods will be available:
- `Articus\DataTransfer\Service::transferTypedData`
- `Articus\DataTransfer\Service::transferToTypedData`
- `Articus\DataTransfer\Service::transferFromTypedData`
- `Articus\DataTransfer\Service::extractFromTypedData`     

Currently, the default way to declare metadata shown in code examples across this documentation is via [Doctrine Annotations](https://www.doctrine-project.org/projects/annotations.html). If your project uses PHP 8 you may declare metadata via [attributes](https://www.php.net/manual/en/language.attributes.overview.php) instead (just switch from `Articus\DataTransfer\MetadataProvider\Annotation` to `Articus\DataTransfer\MetadataProvider\PhpAttribute`). And you can create your own implementation for `Articus\DataTransfer\ClassMetadataProviderInterface` if you want to get metadata from another source.

Metadata consists of two parts:
- strategy - `Articus\DataTransfer\Strategy\StrategyInterface` implementation that knows how **extract**, **merge** and **hydrate** class objects
- validators - one or more `Articus\DataTransfer\Validator\ValidatorInterface` implementations that know how to validate **untyped data** from class objects

One class may have several subsets of metadata distinguished by name, default subset name is empty string:  

```PHP
<?php
use Articus\DataTransfer\Annotation as DTA;

/**
 * Default metadata subset.
 * @DTA\Strategy(name="MySampleStrategy")
 * @DTA\Validator(name="MySampleValidator")
 *
 * Metadata subset with several validators.
 * They will be checked in the same order they declared or according priority.
 * If validator is "blocker" then all following validators will be skipped when it finds violations.
 * @DTA\Strategy(name="MySampleStrategy", subset="several-validators")
 * @DTA\Validator(name="MySampleValidator2", subset="several-validators", blocker=true)
 * @DTA\Validator(name="MySampleValidator3", subset="several-validators")
 * @DTA\Validator(name="MySampleValidator1", priority=2, subset="several-validators")
 *
 * Strategies and validators are constructed via plugin managers from laminas/laminas-servicemanager,
 * so you may pass options to their factories.
 * Check Articus\DataTransfer\Strategy\PluginManager and Articus\DataTransfer\Validator\PluginManager for details.
 * @DTA\Strategy(name="MySampleStrategy", options={"test":123}, subset="with-options")
 * @DTA\Validator(name="MySampleValidator", options={"test":123}, subset="with-options")
 */
class Sample
{
}
```

### Build-in strategies and validators

Pretty often data transfer of object simply means data transfer of its properties. Library provides a convenient way to handle this scenario. If you add some special metadata for class properties then `Articus\DataTransfer\Strategy\FieldData` will be used as class strategy and `Articus\DataTransfer\Validator\FieldData` will be added to class validator list at highest priority: 

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
	 * Property name and untyped data field for extraction/hydration may differ
	 * @DTA\Data(field="fancy-property")
	 */
	public $renamedProperty;

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
	 * If you property does not have setter (or getter) just set empty string.
	 * Property without setter will not be hydrated, property without getter will not be extracted.
	 * @DTA\Data(setter="")
	 */
	protected $propertyWithoutSetter;
	public function getPropertyWithoutSetter()
	{
		return $this->propertyWithoutSetter;
	}

	/**
	 * You can also use your own strategy and/or your own validators for property like for whole class
	 * @DTA\Data()
	 * @DTA\Strategy(name="MyStrategy")
	 * @DTA\Validator(name="MyValidator")
	 * @var mixed
	 */
	public $customValue;

	/**
	 * Library provides simple strategy and simple validator for embedded objects.
	 * Check Articus\DataTransfer\Strategy\Factory\NoArgObject and Articus\DataTransfer\Validator\Factory\TypeCompliant for details.
	 * @DTA\Data()
	 * @DTA\Strategy(name="Object", options={"type":MyClass::class})
	 * @DTA\Validator(name="TypeCompliant", options={"type":MyClass::class})
	 * @var MyClass
	 */
	public $objectValue;

	/**
	 * ... and simple strategy for lists of embedded objects and simple validator for lists
	 * Check Articus\DataTransfer\Strategy\Factory\NoArgObjectList and Articus\DataTransfer\Validator\Factory\Collection for details.
	 * @DTA\Data()
	 * @DTA\Strategy(name="ObjectArray", options={"type":MyClass::class})
	 * @DTA\Validator(name="Collection",options={"validators":{
	 *     {"name": "TypeCompliant", "options": {"type":MyClass::class}},
	 * }})
	 * @var MyClass[]
	 */
	public $objectArray;

	/**
	 * Even if there is no validators value will be tested not to be null.
	 * Mark property "nullable" if you do not want that.
	 * And if you set any validators for nullable property they will be executed only for not null value.
	 * @DTA\Data(nullable=true)
	 * @DTA\Validator(name="MyValidatorForNotNullValue")
	 * @var string
	 */
	public $nullableString;

	/**
	 * Library provides simple abstract factory to use validators from laminas/laminas-validator seamlessly.
	 * If you enable this integration in your container configuration (check configuration sample for details) 
	 * you may use any validator registered in Laminas\Validator\ValidatorPluginManager.
	 * @DTA\Data()
	 * @DTA\Validator(name="StringLength",options={"min": 1, "max": 5})
	 * @DTA\Validator(name="Hex")
	 */
	public $laminasValidated;
}
```

Same as for class metadata there may be several subsets for property metadata and [Doctrine Annotations](https://www.doctrine-project.org/projects/annotations.html) is the default way to declare property metadata. If your project uses PHP 8 you may declare property metadata via [attributes](https://www.php.net/manual/en/language.attributes.overview.php) instead (just switch from `Articus\DataTransfer\MetadataProvider\Annotation` to `Articus\DataTransfer\MetadataProvider\PhpAttribute`). And you can create your own implementation for `Articus\DataTransfer\FieldMetadataProviderInterface` if you want to use another metadata source.  
    
## Enjoy!
I really hope that this library will be useful for someone except me. 
It is used for production purposes but it lacks lots of refinement, especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.
