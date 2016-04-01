# RCH/CapistranoBundle

[![Build Status](https://travis-ci.org/chalasr/RCHCapistranoBundle.svg?branch=master)](https://travis-ci.org/chalasr/RCHCapistranoBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74/mini.png)](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74)

![](https://raw.githubusercontent.com/chalasr/RCHCapistranoBundle/master/Resources/doc/SCREENCAST.gif)

Make deployment a part of your development environment by :
- Setup a fast and automated deployment workflow
- Create stagings in config format (YAML, PHP, XML)
- Control execution order by namespaces
- Add custom tasks and environment variables.

Requirements
============

- Symfony ~2.4
- Ruby ~2.0
- Capistrano ~3.0

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require rch/capistrano-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php

// app/AppKernel.php

$bundles = array(
    // ...

    new RCH\CapistranoBundle\RCHCapistranoBundle(),
);
```

Step 3: Install Capistrano
-------------------------

Build installation files for capistrano
```bash
$ app/console rch:deploy:install
```

Install dependencies
```bash
$ bundle install
```

Usage
======

Setup deployment configuration in interactive mode  
```bash
$ app/console rch:deploy:setup
```

Create YAML stagings  
```yaml
domain: 'ssh_host'
user: 'ssh_user'
keys: '/home/ssh_user/.ssh/id_rsa'
forward_agent: 'false'
auth_methods: 'publickey password'
deploy_to: '/path/to/deploy'

```

Start deployment  
```bash
$ app/console rch:deploy:run --staging-name=[STAGING-NAME]
```

[Advanced usage](https://github.com/capistrano/capistrano#usage)

Credits
=======

[Robin Chalas](https://github.com/chalasr)  
[robin.chalas@gmail.com](mailto:robin.chalas@gmail.com)

License
=======

[![License](http://img.shields.io/:license-gpl3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)
