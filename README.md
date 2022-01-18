# CravlerFayeAppBundle

This bundle depends on [faye-app](http://github.com/cravler/faye-app).

## Installation

### Step 1: Download the Bundle

``` bash
composer require cravler/faye-app-bundle:1.x-dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...

        new Cravler\FayeAppBundle\CravlerFayeAppBundle(),
    );
}
```

### Step 3: Add routing

``` yaml
# app/config/routing.yml

cravler_faye_app:
    resource: "@CravlerFayeAppBundle/Resources/config/routing.yml"
```

### Step 4: Include Javascript

To include the relevant javascript libraries necessary for FayeApp, add these to your root layout file.

``` twig
<!-- .../Acme/DemoBundle/Resources/views/layout.html.twig -->

<script type="text/javascript" src="..."></script>
{{ faye_app_javascript() }}
```

## Usage

### Create entry-point

``` php
<?php
// .../Acme/DemoBundle/EntryPoint/Example.php

namespace Acme\DemoBundle\EntryPoint;

use Cravler\FayeAppBundle\EntryPoint\AbstractEntryPoint;

class Example extends AbstractEntryPoint
{
    public function getId()
    {
        return 'acme-demo.example';
    }
}
```

### Register entry-point

``` xml
<!-- .../Acme/DemoBundle/Resources/config/services.xml -->

<services>
    ...
    <service id="acme.demo.entry_point.example" class="Acme\DemoBundle\EntryPoint\Example">

        <call method="setEntryPointManager">
            <argument type="service" id="cravler_faye_app.service.entry_point_manager" />
        </call>

        <tag name="cravler_faye_app.entry_point" />
    </service>
    ...
</services>
```

### Subscribing to channels

``` html
<script type="text/javascript">
    var exampleEntryPoint = FayeApp.createEntryPoint('acme-demo.example');

    var subscription = exampleEntryPoint.subscribe('/foo', function(message) {
        console.log('[foo] Handle message: ', message);
    });

    subscription.then(function() {
        console.log('[foo] Subscription is now active!');
    }, function(error) {
        console.log('[foo] Subscription problem: ' + error.message);
    });
</script>
```

### Sending messages

``` html
<script type="text/javascript">
    var exampleEntryPoint = FayeApp.createEntryPoint('acme-demo.example');

   var publication = exampleEntryPoint.publish('/foo', { text: 'Hi there' });

   publication.then(function() {
       console.log('[foo] Message received by server!');
   }, function(error) {
       console.log('[foo] There was a problem: ' + error.message);
   });
</script>
```

## Configuration

The default configuration for the bundle looks like this:

``` yaml
cravler_faye_app:
    example: false
    user_provider: false #security.user.provider.concrete.[provider_name]
    route_url_prefix: faye-app
    use_request_uri: false
    secret: ThisTokenIsNotSoSecretChangeIt
    app:
        scheme: ~
        host: 127.0.0.1
        port: ~
        mount: /pub-sub
        options: {}
```

## License

This bundle is under the MIT license. See the complete license in the bundle:

```
LICENSE
```
