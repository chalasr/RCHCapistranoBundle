# RCH/CapistranoBundle

[![Build Status](https://travis-ci.org/chalasr/RCHCapistranoBundle.svg?branch=master)](https://travis-ci.org/chalasr/RCHCapistranoBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74/mini.png)](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74)

![](Resources/doc/SCREENCAST.gif)

Integrates deployment as part of your development environment.

- _Sets up a fast and automated deployment workflow_
- _Creates stagings in configuration format (YAML, PHP, XML)_
- _Controls tasks execution order through namespaces_
- _Generates custom tasks and environment variables_.

Prerequisites
=============

- Ruby >= 2.0

This version of the bundle requires __Symfony 3+__.  
For a Symfony version between _2.4.x_ and the last LTS release, [__please use the 1.0 branch__](https://github.com/chalasr/rchcapistranobundle/tree/1.0).

Installation
============

Download the bundle
------------------------------------------

```bash
$ composer require rch/capistrano-bundle:~2.0
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Enable the Bundle
-----------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// app/AppKernel.php

$bundles = array(
    // ...
    new RCH\CapistranoBundle\RCHCapistranoBundle(),
);
```

Install & Configure Capistrano
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

Build Stagings
---------------

```yaml
# app/config/rch/stagings/[staging].yml

# Remote host
domain: 'ssh_host'
# Remote user
user: 'ssh_user'
# Remote key              
keys: '/home/ssh_user/.ssh/id_rsa'
# Forward Agent
forward_agent: 'false'
# Authentication mode
auth_methods: 'publickey password'
# Deployment path
deploy_to: '/path/to/deploy'            
```

Run deployment
--------------

```bash
$ app/console rch:deploy:run --staging-name=[STAGING-NAME]
```

Advanced usage
===============

Look at [the capistrano documentation](https://github.com/capistrano/capistrano#usage).

Contributing
============

This bundle needs help!
For the contribution guidelines, see the [CONTRIBUTING.md(CONTRIBUTING.md)] distributed file.

Todo
-----

- Command\Generate\GenerateStagingCommand, takes an yaml file as optional argument (even look for an existing if the argument is not set) and generates a staging for capistrano in interactive mode (optional).

- Make the path of config files (staging, task) configurable using a bundle Extension.

- Handle XML/PHP in GenerateStagingCommand.

License
=======

[![License](http://img.shields.io/:license-gpl3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)
