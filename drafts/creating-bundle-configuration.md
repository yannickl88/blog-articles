[//]: # (TITLE: Creating Bundle Configuration)
[//]: # (DATE: 2017-02-15T09:00:00+01:00)
[//]: # (TAGS: Symfony, Bundle, Configuration, Extension, DIC, Dependency Injection Container)
[1]: https://stovepipe.systems/post/what-are-bundles-in-symfony
[2]: https://symfony.com/doc/current/components/config/definition.html
[3]: https://symfony.com/doc/current/bundles/extension.html#using-the-load-method
[4]: http://api.symfony.com/master/Symfony/Component/Config/Definition/Exception/InvalidConfigurationException.html

[Iltar wrote an excellent post][1] on what bundles are in Symfony. In this post I will dive a bit deeper into the configuration of a bundle, since most bundles require some configuration to work. Think of the Doctrine bundle that requires you to configure the database connection. Another example is the twig bundle which has a config item to let it know if it is in debug-mode. But how do you write this configs, how do they work, and why should you use them?

In this post I will give an example of a what you need to do to create a configuration for your bundle. 

## Why configuration in the first place?
So why configuration of a bundle? Well first of all it is not a necessity. You can relay on other ways to do so. For instance, making some parameters mandatory or passing options to method of services. Yet, these methods can be hard to explain or discover without proper documentation. 

Configuration in Symfony a method of passing information from the application to the bundle *when the container is compiled*. Note here, *"when the container is compiled"*. This means that your configuration is only used for building the container, nothing more.

This might seem like a limiting thing, but you have to look back at what a bundle is. To quote Iltar: *"The main purpose of a bundle however, is to provide an extension point for the Dependency Injection Container."* So the bundle is for modifying the service container. Having some configured values will help build the services your bundle is trying to register. Think of a location for writing files, or a URL of a web service.

## Defining a config
The [Symfony docs do quite a good job at explaining how to define configuration for your bundle][2]. I will not go too much in depth, but only show a basic running example to give a brief impression on usage. So for our example the configuring will be for a small library which communicates with a web service.

In the library there is a `RestClient` which requires the services URL, a port, a username, a password and a timeout in seconds. The library class would look like so:

```php
namespace Acme\Lib\WebClient;

class RestClient
{
    public function __construct(string $url, int $port, string $username, string $password, int $timeout)
    {
        // ...
    }
}
```

The bundle should register a service for the `RestClient` but it will need confirmation to do so. This is done by defining a `Configuration` class which implements the `ConfigurationInterface` interface from the Symfony config component. A definition which requires all those config values can look as follows:

```php
<?php
namespace Acme\WebClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('acme_webclient');
        $root
            ->children()
                ->scalarNode('url')
                    ->isRequired()
                ->end()
                ->integerNode('port')
                    ->isRequired()
                ->end()
                ->scalarNode('username')
                    ->isRequired()
                ->end()
                ->scalarNode('password')
                    ->isRequired()
                ->end()
                ->integerNode('timeout')
                    ->isRequired()
                ->end()
            ->end();

        return $builder;
    }
}
```

This class can process a config which has values defined as such:
```yaml
# Default configuration for extension with alias: "acme_webclient"
acme_webclient:
    url:                  ~ # Required
    port:                 ~ # Required
    username:             ~ # Required
    password:             ~ # Required
    timeout:              ~ # Required
```
Okay, the config can be defined. However, by default Symfony does not do anything with it. It will need a bundle Extension to actually process the config.

## Processing a config
Processing the config will actually do something with the configured values. This is where the real gluing takes place. First of all, the bundle will require an `Extension` class. By convention, this is usually the bundle name post-fixed with `Extension`. In the case of the running example the bundle is `AcmeWebClientBundle`, the extension will be called `AcmeWebClientExtension`. The  `AcmeWebClientExtension` should also extend the `Extension` class.

For the example the extension will process the config and add a service definition for the `RestClient`. It will use the configured values to build the service definition. 

Processing the config will use the `Processor` class from the Symfony config component. It will flatten the config arrays and return an associative array with the config nodes as keys. There is a shorthand method in the extension to help you do this named  ` processConfiguration()`.

When all put together you will end up with the following extension class.
```php
<?php
namespace Acme\WebClientBundle\DependencyInjection;

use Acme\Lib\WebClient\RestClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AcmeWebClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs); 

        $container->setDefinition('amce.web_client', new Definition(
            RestClient::class,
            [
                $config['url'],
                $config['port'],
                $config['username'],
                $config['password'],
                $config['timeout'],
            ]
        ));
    }
}
```
Now it is possible to configure the `amce.web_client` service using your application configuration.

## Tips and ticks
Some tricks I found will improve your configuration.

* **Define default values:** If possible, try to give some sensible defaults. Less is more when it comes to configuring bundles. In the running example for instance, the port and timeout could have a default. This will only leave 3 required fields.

* **Replacing arguments:** It can be useful to [load a service configuration][3] first with default values. The extension can then replace some of the arguments of a definition with the config values. This will reduce the amount of code in your extension building service definitions.

* **Creating parameters:** Something the extension is not flexible enough and you need a compiler pass. If you still want to use the values from the config you can create parameters in the extension to use later in the compiler pass. Just make sure they are unique enough to not get overridden somewhere.

* **Custom config validation:** You can always validate your config in the extension. You can throw a [`InvalidConfigurationException`][4], or a subclass of it, if something is not valid. If you can, try to have exception messages which provide solutions. For instance, if a port has to be between a range have the message say something like *"Port X not allowed, should be between 1000 and 2000"*.

## Wrapping up
I have tried to give a brief overview of why and how to use a configuration class for your bundle. Hopefully in the future when you write a bundle it will allow you to have a decent way of configuring your bundle.
