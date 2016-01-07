[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74/mini.png)](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74)

# RC/CapistranoBundle

Generates deployment workflows on top of capistrano.

Requirements
============

- Symfony/Console >= 2.6
- Symfony/Filesystem >= 2.6
- Symfony/Yaml >= 2.6
- Ruby >= 2.0

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require chalasdev/capistrano-bundle dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Chaladev\CapistranoBundle\RCCapistranoBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Install Capistrano
-------------------------

Setup capistrano dependencies by creating a Gemfile and a Capfile :

```
app/console capistrano:install && bundle install
```

Usage
======

```
app/console capistrano:setup
```

Deploy
============

```
cap production deploy
```

[Advanced usage](https://github.com/capistrano/capistrano)

Credits
=======

[Robin Chalas](https:/github.com/chalasr)  
[robin.chalas@gmail.com](mailto:robin.chalas@gmail.com)

License
=======

[![License](http://img.shields.io/:license-gpl3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)
