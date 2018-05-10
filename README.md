# Tempest Tools Scribe

This package is the centerpiece of Tempest Tools.
Scribe provides a robust set of features for rapid building of RESTful APIs without limiting the developer in terms of the functionality they can implement on top of what the package does out of the box.

A key insight of Scribe is that a RESTful API can be defined as a set of configurations with closures and event listeners providing extension beyond base functionality. This saves time and provides a clear predictable structure to your code.

## Key features

Some of the key features of Scribe are as follows:

* Batching and Chaining -- Whole object graphs for multiple resources and their associations can be passed to Scribe instead of dealing with only a single resource at a time.
* Filterable Index Actions -- Index requests can be filtered with a powerful filtering language passed from the frontend.
* Contextual Extendable Configurations -- Scribe uses configurations that respond to designated contexts placed on your web applications routes. These configurations are extendable and leverage many other innovative features to maximize control while minimizing the amount of work required.
* Extendable Queries -- Along with extendable configurations, Scribe offers extendable queries so when multiple queries share common functionality they can extend each other for easier and faster building / maintaining of the queries in the system.
* Simplified Data Transport Between Components -- Scribe passes data from all levels of your application in objects that store normalized information related to the request. This makes it very easy to access the data you need at any place in your code.
* Event Driven Design -- Everything Scribe does fires an event. These events exist at the Controller, Repository and Entity level and let developers easily add their own custom code to implement new functionality or augment existing functionality with minimal hassle.
* Closures, Closures, Closures -- Scribe configs allow you to place closures everywhere, giving you complete customizable control of everything Scribe does in every context.
* Security -- Scribe has a robust permission / validation system built into its configuration structure to give developers fine grain control over every action that can be requested in every context.
* Rapid Prototyping -- Build fully working end points in minutes so your frontend team can begin prototyping right away.
* Deep Control Over your ORM -- If Doctrine does it, Scribe has a configuration option to leverage it.
* Control over what is returned from your requests -- Powerful configuration features for built-in transformers lets you return just the data you want from every request with minimal configuration required.
* Portability -- Scribe is built to be easily ported to other frameworks.
* Construction -- Scribe is built with best practices held firmly in mind.
* Execution Speed -- Even more than best practices, Scribe is built to run as quickly as possible, and using it will prevent many common mistakes that can grind a web application to a halt.
* Test Cases For Everything -- Everything Scribe does is included in a test case for easy reference on how to implement the feature.
* Learning and Documentation -- We are in the process of providing fully detailed docs for all features of Scribe to make learning easier.

## More On The Way

The features in the first release of Scribe are just the tip of the iceberg. Here is a peak at what is coming next:

* Integration with Quill for easy cache invalidation via Scribe configs.
* Integration with Raven for easy notification configurations.
* Query Blocks. A future version of Scribe will support the designation of blocks of query logic that are able to be switched on and off via the flags passed from the front end. The blocks can lead to other blocks stored in queries in other repository classes can require placeholders, and will be fully controlled by permissions. Blocks can be as simple as just showing a new field in the return, or as complex as you can imagine; the choice is up to the developer.
* Configuration Builders. A future version of Scribe will include configuration builder classes -- for users who prefer to build their configuration files by using builder classes instead of manually constructing arrays.
* Events inside built-in transformers to add another way to modify the data returned by the transformers.
* Array transformers for array hydrated returns, as opposed to entity hydrated returns.
* Enhanced controller configuration options, to allow users to have the same level of built-in control over validation and rules enforcement at the controller level that is currently available for Repositories and Entities.
* Improved convenience features for implementing HATEOAS.
* Unlimited fall back contexts can be specified for Scribe configs.
* Options in a controller to assign different transformers in different contexts, and also dependant on hydration types of returns.
* Built-in support for Fractal transformers.
* Integration with Maester for easy full-text search.

## More Info

Please see the wiki for additional documentation.

Tempest Tools Scribe can be seen in action in the Tempest Tools Skeleton: https://github.com/tempestwf/tempest-tools-skeleton



## Requirements

* PHP >= 7.1.0
* laravel/framework = 5.3.*
* laravel-doctrine/orm = 1.2.*
* tempest-tools/common = 1.0.*

* [Composer](https://getcomposer.org/).



