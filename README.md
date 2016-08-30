# Data Transfer
This library provides a simple "validating hydrator", a service that "transfers" data from source to destination safely.

In other words, if you have `A` and `B` - two objects of different classes this service can: 

- extract data from `A` according specified metadata
- map (or transform) this data according specified rules
- hydrate mapped data to `B` if `B` remains valid after that

And yes - you can skip first step if `A` is array and you can skip last step if `B` is array. 

## Why?
Personally I just needed something to easily update DTOs and Doctrine entities from untrusted sources 
(like request parsed body, request headers, request query parameters and etc) 
inside [Zend Framework](https://framework.zend.com/) application. Something like 
[Symfony Serializer](http://symfony.com/doc/current/components/serializer.html) or 
[JMS Serializer](http://jmsyst.com/libs/serializer) but for arrays and with Zend flavour. 
The initial prototype was extremely useful for building APIs and after using it in several production projects 
I finally decided to make it a separate library. Hopefully, it will be useful for someone else.
     
## How to install?

Just add `"articus/data-transfer": "*"` to your [composer.json](https://getcomposer.org/doc/04-schema.md#require).

## How to use?
Library is supposed to be used with [Zend Expressive](http://zendframework.github.io/zend-expressive/) but 
it should be possible to integrate in any project that can provide `Interop\Container\ContainerInterface`.

### Metadata
First of all you need to declare metadata for classes that you would like to use with data transfer service. To do this 
you need to decorate each class property that should be used for transferring with special annotation:

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

If you need some special logic to extract and hydrate property value you can declare hydration strategy for this property
(see Articus\DataTransfer\Strategy\StrategyInterface for details).

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
         * You can also use your own startegy if it is registered in strategy plugin manager
         * @DTA\Data()
         * @DTA\Strategy(name=MyStrategy::class)
         * @var mixed
         */
        public $custom;
    }

And as you may have guessed there is a special annotation to add validation constraints. 
Any validator registered in validator plugin manager can be used.
 
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
         * If you set several validators they will be tested one by one until first failure.
         * Validators with higher prioriry will be executed earlier.
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
         * @DTA\Validator(name="Dictionary", options={"type":MyClass::class})
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

### Configuration
After declaring metadata you need to add few lines to you configuration (example is in YAML just for readability):

    # Register data transfer service in container 
    dependencies:
      factories:
        Articus\DataTransfer\Service: Articus\DataTransfer\ServiceFactory
      
    # Configure data transfer service
    data_transfer:
      # Configure dedicated Zend Cache Storage for class metadata (see Zend\Cache\StorageFactory) 
      metadata_cache:
        adapter: filesystem
        options:
          cache_dir: data/DataTransfer
          namespace: dt
        plugins:
          serializer:
            serializer: phpserialize
      # ... or use existing service inside container
      #metadata_cache: MyMetadataCacheStorage
      # Configure dedicated hydration strategy plugin manager (see Articus\DataTransfer\Strategy\PluginManager for details)
      strategies:
        invokables:
          MySampleStrategy: My\SampleStrategy
      # ... or use existing service inside container
      #strategies: MyStrategyPluginManager
      # Configure dedicated validator plugin manager (see Zend\Validator\ValidatorPluginManager)
      validators:
        factories:
          Articus\DataTransfer\Validator\Dictionary: Articus\DataTransfer\Validator\Factory
          Articus\DataTransfer\Validator\Collection: Articus\DataTransfer\Validator\Factory
        aliases:
          Dictionary: Articus\DataTransfer\Validator\Dictionary
          Collection: Articus\DataTransfer\Validator\Collection
      # ... or use existing service inside container
      #validators: MyValidatorPluginManager

### Usage
Finally you just need to get service from container and call `transfer` method:

    use Articus\DataTransfer\Service;

    $from = new MyClassA();
    $to = new MyClassB();
    $validationMessages = $container->get(Service::class)->transfer($from, $to);
    if (empty($validationMessages))
    {
        //Transfer was successful
    }
    
Supply third argument if you want to map data between extraction and hydration:

    use Articus\DataTransfer\Service;

    $from = new MyClassA();
    $to = new MyClassB();
    $map = function(array $data)
    {
        unset($data['key']);
        return $data;
    };
    $validationMessages = $container->get(Service::class)->transfer($from, $to, $map);

Use arrays if you want only extraction or only hydration:
 
    use Articus\DataTransfer\Service;

    $from = [];
    $to = new MyClassB();
    $validationMessages = $container->get(Service::class)->transfer($from, $to);
 
    $from = new MyClassA();
    $to = [];
    $validationMessages = $container->get(Service::class)->transfer($from, $to);
    
## Enjoy!
I really hope that this library will be useful for someone except me. 
Currently it is only the initial release. It is used for production purposes but it lacks lots of refinement, 
especially in terms of tests and documentation. 

If you have any suggestions, advices, questions or fixes feel free to submit issue or pull request.