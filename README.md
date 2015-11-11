[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74/mini.png)](https://insight.sensiolabs.com/projects/a1b5a249-e656-4a0f-af57-77f8f84f2e74)

# chalasdev/capistrano-bundle

Symfony/console command that provide automation of your deployment workflow, built on top of Capistrano.

## Requirements

- Symfony/Console >= 2.7
- Symfony/Filesystem >= 2.7
- Ruby >= 2.0
- Bundler (rubygem)
- Capistrano rubygems (Gemfile provided)

## Install

Download the bundle using [composer](http://getcomposer.org/) :

```composer require chalasdev/capistrano-bundle dev-master```

Setup Gemfile and Capfile to manage ruby dependencies :

```app/console capistrano:install```

Install rubygems by running :

```bundle install```

## Usage

Run the following command to setup your deployment workflow based on [capistrano/symfony](https://github.com/capistrano/symfony) tasks :

```app/console capistrano:setup```

## Deploy

As long as your production branch is up-to-date, you can do ```cap production deploy``` each times you need, without any maintenance or perturbations.

## Credits

Author : [Robin Chalas](https:/git.chalasdev.fr/)

## License

[![License](http://img.shields.io/:license-gpl3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0.html)
