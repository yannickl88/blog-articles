[//]: # (TITLE: Migrating your project to Symfony)
[//]: # (TAGS: symfony, php, migration, framework)

From time to time I see people struggling with trying to port their existing site or web-app to Symfony. Their old framework architecture might not match that of Symfony, making porting your controllers not that easy. Other times, their data structure contained so much logic that it was impossible to simply replace with doctrine. Thus porting the project can seem like a daunting task.

So, what are your options of migrating to Symfony? Well you could try the 'big bang' approach and just power through your project, porting all of it and then releasing it all at once. While I might not consider this the most optimal way, it does have its advantages. It's gives you a clean slate which allows for a fresh new design and fixing all the legacy stuff you had from the old framework.

But what if your project is too big or you want a more graceful way of migrating? Well there is another way, which allows for a more gradual replacement of the old code. This is running both Symfony and your old project at the same time using a fallback method, and it is a lot easier than you might think.

## Setup
So, what do I mean with a fallback method? The gist of it is that you wrap Symfony around your existing project, if a route cannot be matched by Symfony that request should fallback onto the old framework.

First of all, make sure your project is ready for Symfony. I recommend setting up composer to work with your old project, if it doesn't already. This will help a lot, since Symfony is mainly distributed using composer and keeping it up to date will save you a lot of trouble.

Secondly, you will need to add Symfony to your existing project. You could just copy the standard distribution into your project, which should work fine, all you really need is the `AppKernel` and all its settings properly configured.

Lastly, create a `LegacyBundle` and in it a single controller, which shall aptly be named `FallbackController`.

## The Fallback Controller
So, what does the `FallbackController` do? This controller will handle all non-Symfony routes, basically things still left for porting. For this example the idea is to let Symfony fallback onto Zend framework. An example setup would be:
```php
<?php
namespace LegacyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * @Route(service="legacy.controller.fallback")
 */
class FallbackController
{
    private $webDir;

    public function __construct($webDir)
    {
        $this->webDir = $webDir;
    }

    public function fallback(Request $request, $path)
    {
        // fallback to Zend Framework
        chdir(dirname($this->webDir));
        $appConfig = require $this->webDir . '/../config/application.config.php';

        // Run the application!
        $app = Application::init($appConfig);
        // Add event listener to prevent outputting the result so we can wrap it later
        $app->getEventManager()->attach('finish', function (MvcEvent $e) {
            $e->stopPropagation();
        });
        $app->run();

        // wrap the content in a Symfony response
        return new Response($app->getResponse()->getContent());
    }
}
```
> Note: the content of the `FallbackController::fallback` is essencially the [Zend Framework index.php][zf-index-php], but with some small changes.

Registering your service in the [service container][controller-as-a-service]. 
```yml
services:
    legacy.controller.fallback:
        class: LegacyBundle\Controller\FallbackController
        arguments:
            - "%kernel.root_dir%/../public"
```

Finally, all you have to do is actually configuring the fallback. You do this by creating an special route at the **bottom** of the routing configuration. So in your `routing.yml` make sure this is the last route:
```yml
fallback:
    path: /{path}
    defaults: { _controller: "legacy.controller.fallback:fallback" }
    requirements:
        path: .*
```
> Note: the *path* has a special requirement of `.*`, which will match everything. This is important, [since by default symfony won't match the `/` in parameters][symfony-slash-url].

That is it! Now any URL you will use will end up in the `FallbackController` and will trigger your old framework (Zend Framework in this case). For this example, the result would look something like this, you even have a symfony toolbar!

![Symfony and Zend Framework](http://img.yannickl88.nl/fallback_zf.png)

## Migration to Symfony
You now have routed all paths to your `FallbackController`, now what? Well, the routing configuration actually has an implicit priority. In the case that two routes would match the same URL, the route that is defined first, i.e., higher in your routing file, will be picked. This means that routes defined before the fallback will have a higher priority. 

Therefore, if you want create a new action simply add a new controllers in your `AppBundle` (or any other bundle) and make sure to configure it **above** the fallback in your routing configuration, like so:
```yml
app:
    resource: "@AppBundle/Controller/"
    type:     annotation

fallback:
    # ...
```
This will result in any URL that is being matched by a controller in the `AppBundle` will replace any exising URLs in the old project but if the URL isn't matched, it will fallback to the old framework.

This setup allows for porting each page separately, allowing for a more gradual replacement of your old project with Symfony.

## Creating Compatibility
### Routing to old pages
In some cases you might need to link to a page that is not yet in one of the Symfony controllers but in the old framework. You could just hardcoded the URL, but you will lose all the advantages the `Router` give you. What you could do instead is defined extra dummy routes which do allow for routing but will always end up in the fallback. 

You could do this by creating a `routing.yml` in your `LegacyBundle` and include it in the Symfony routing. If you add it at the very bottom of your routing configuration, you do not actually need to assign a controller to it, since they will never match (the fallback will always have a higher priority over them since it is defined before the other routes).
```yml
app:
    # ...
fallback:
    # ...

legacy_routes:
    resource: "@LegacyBundle/Resources/config/routing.yml"
```

In the `LegacyBundle/Resources/config/routing.yml` you can define your routes as follows:
```yml
legacy.old-page:
    path: /some/legacy/url/{foo}
```

In your code you can now call `$router->generate('/some/legacy/url', [foo => 'bar'])` which will result in `/some/legacy/url/bar`.

### Forward compatibility
Something to consider is that you might be able to share code so easily between the old project and Symfony. Having duplicate code is always discouraged. What you could do is make your new code backwards compatible, but once you have ported your project, you still have a lot of old code that you need to clean up. A better solution would be to expose the service container to your legacy code, this will make your old code [forward compatible][wiki-forward-compat] and is helpful for migrating code.

A simple way of doing so is injecting the service container into some static class in your fallback action. This will ensure the class is useless in your new code (forcing you to do it right) and will still allow your old code to access the service container.

A simple implementation would look like:
```php
<?php
namespace LegacyBundle\Compatibility;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Container
{
      private static $container;

      public function init(ContainerInterface $container)
      {
          self::$container = $container;
      }
      
      public function get($id)
      {
          if (!self::$container instanceof ContainerInterface) {
              throw new \LogicException('Container not initialized!');
          }
          
          return self::$container->get($id);
      }
}
```
Simply call `Container::init()` in the `FallbackController` to make sure your wrapper works in your old code. In your old code you can get services using `Container::get('doctrine.orm.entity_manager')`.

And that is it, a simple way to gradually migrate to Symfony. If you are migrating from Symfony1 to Symfony, you might be interested in [hostnet/hnDependencyInjectionPlugin][hn-dep-plugin]. This uses the same method I described but with a bit more features which will help speed up the migration.

[controller-as-a-service]: http://symfony.com/doc/current/cookbook/controller/service.html
[symfony-slash-url]: http://symfony.com/doc/current/cookbook/routing/slash_in_parameter.html
[hn-dep-plugin]: https://github.com/hostnet/hnDependencyInjectionPlugin
[zf-index-php]: https://github.com/zendframework/ZendSkeletonApplication/blob/master/public/index.php
[wiki-forward-compat]: https://en.wikipedia.org/wiki/Forward_compatibility
