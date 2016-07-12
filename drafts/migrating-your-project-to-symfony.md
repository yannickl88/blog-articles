[//]: # (TITLE: Migrating your project to Symfony)
[//]: # (TAGS: symfony, php, migration, framework)

From time to time I see someone struggeling with trying to port their existing site or web-app to Symfony. Their old framework architecture might not match that of Symfony, making porting your controllers not that easy. Other times, their data structure contained so much logic that it was impossible to simply replace with doctrine.

So, what are your options of migrating to Symfony? Well you could try to 'big bang' it and just power through your application, porting it and then releasing it all at once. While I might not consider this the most optimal way, it does have its advantages. It's gives you a clean slate which allows for a fresh new design and fixing all the legacy stuff you had from the old framework.
But what if your project is too big? Well there is another way, which allows for a more gradual replacement of the old code. This is running both Symfony and your old one at the same time, using a fallback method and it is a lot easier than you might think.

## Setup
So, what do I mean with a fallback method? What you want to do is wrap Symfony around your existing project. If a route cannot be matched by Symfony, the request should fallback onto the old framework.

And that is it really. What I recommend is first making sure your old project works with composer if it doesn't already. This will help a lot, since Symfony is mainly distributed using composer.

Secondly, you will need to add Symfony to your exising project. You could just copy the standard distribution into your project, which should work fine, all you really need is the `AppKernel` and all its settings properly configured.

Lastly, create a `LegacyBundle` and in it a single controller, which shall aptly be named `FallbackController`.

## The Fallback Controller
So, what does the `FallbackController` do? This controller will handle all non-Symfony routes, basically things still left for porting.

A simple example is as follows:
```php
<?php
namespace LegacyBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="legacy.controller.fallback")
 */
class FallbackController
{
      public function fallback(Request $request, $path)
      {
          // fallback to your old framework and fill the $content with 
          // whatever the old framework returned.
          
          return new Response($content);
      }
}
```

Registering your service. 
```yml
services:
    legacy.controller.fallback:
        class: LegacyBundle\Controller\FallbackController
```
> Note: For this example, the controller is a service, however, extending the `Symfony\Bundle\FrameworkBundle\Controller` also works but is not recommended. [Please refer to the cookbook why defining a controller as a service is better.][controller-as-a-service]

Only thing left is actually configuring the fallback. You do this by creating an special route at the **bottom** of the routing configuration. So in your `routing.yml` make sure this is the last route:
```yml
fallback:
    path: /{path}
    defaults: { _controller: "legacy.controller.fallback:fallback" }
    requirements:
      path: .*
```

That is it, now any URL you will use will end up in the `FallbackController` and will trigger your old framework.

## Migration to Symfony
You now have routed all paths to your `FallbackController`, now what? Well, the routing configuration actually has priority build in. This means that routes defined before the fallback will have a higher priority. Thus, new controllers in your `AppBundle` (or any other bundle) can be matched by defining the following **above** the fallback:
```yml
app:
    resource: "@AppBundle/Controller/"
    type:     annotation

fallback:
    # ...
```
So any URL that is being matched by a controller in the `AppBundle` will match as you would expect, anything that isn't matched will fallback to the old framework. 

This setup allows for porting each page seperately, allowing for a more gradual replacement of your old project with Symfony.

## Creating Compatibility
### Routing to old pages
In some cases you might need to link to a page that is not yet in one of the Symfony controllers but in the old framework. You could just hardcode the URL, but you will lose all the advantages the `Router` give you. What you could do instead is defined extra routes which _also_ point to the fallback controller. 

You could do this by creating a `routing.yml` in your `LegacyBundle` and include it in the Symfony routing. If you add it at the very bottom of your routing configuration, you do not actually need to assign a controller to it, since they will never match (the fallback will always have priority over them).
```yml
fallback:
    # ...

legacy_routes:
    resource: "@LegacyBundle/Resources/config/routing.yml"
```

In the `LegacyBundle/Resources/config/routing.yml` you can define your routes as follows:
```yml
legacy.old-page:
    path: /some/legacy/url
```

### Forward compatibility
If you can, I would recommend exposing the service container to your legacy. This will make your old code forward compatible and is helpful for migrating code and making it accessable (and testable) to your new Symfony controllers and services. You could also make your new code backwards compatible, but when you are all done, you end up with still a lot of old code that you need to clean up. 

A simple way of doing so is injecting the service container into some static class in your fallback action. This will ensure the class is useless in your new code (forcing you to do it right) and will still allow your old code to access the serivce container.

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

And that is it, a simple way to gradually migrate to Symfony. If you are migrating from Symfony1 to Symfony, you might be interested in [hostnet/hnDependencyInjectionPlugin][hn-dep-plugin]. This uses the same method I described but it a bit more features.

[controller-as-a-service]: http://symfony.com/doc/current/cookbook/controller/service.html
[hn-dep-plugin]: https://github.com/hostnet/hnDependencyInjectionPlugin
