<?php

/**
 * This file is part of RCH/CapistranoBundle.
 *
 * Robin Chalas <robin.chalas@gmail.com>
 *
 * For more informations about license, please see the LICENSE
 * file distributed in this source code.
 */

namespace RCH\CapistranoBundle\Util;

use RCH\CapistranoBundle\Generator\GeneratorInterface;
use RCH\CapistranoBundle\Generator\Handler;

/**
 * Provides helper methods.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
trait CanGenerateTrait
{
    /**
     * Generates a document from given configuration using given generator.
     *
     * @param GeneratorInterface $generator Instance of AbstractGeneratord
     *
     * @return callable
     */
    public function generate(GeneratorInterface $generator)
    {
        $callback = function () use ($generator) {
            $handler = Handler::create($generator);
            $handler->generate();
        };

        return $callback();
    }

    /**
     * Call CanGenerateTrait::generateMany for each generator of a given array.
     *
     * @param GeneratorInterface[] $generators
     */
    public function generateMany(array $generators)
    {
        foreach ($generators as $generator) {
            $this->generate($generator);
        }
    }

    /**
     * Read staging parameters from YAML staging file.
     *
     * @param string $yamlFile The YAML staging filename
     *
     * @return array
     */
    public function parseYamlStaging($yamlFile)
    {
        if (property_exists(get_class($this), 'container')) {
            $container = $this->container;
        } else {
            $container = $this->getContainer();
        }

        $capitalizer = $container->get('rch_capistrano.capitalizer');
        $params = Yaml::parse(file_get_contents($yamlFile));

        return $capitalizer->camelize($params);
    }
}
