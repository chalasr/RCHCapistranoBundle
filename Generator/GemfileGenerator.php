<?php

/*
* This file is part of Chalasdev/CapistranoBundle.
*
* https://github.com/chalasr/CapistranoBundle
* Robin Chalas <robin.chalas@gmail.com>
*
*/

namespace Chalasdev\CapistranoBundle\Generator;

/**
 * Generates stagings for capistrano.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class GemfileGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected static $template = "gem '<gem>'";

    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param string $path
     * @param string $name
     */
    public function __construct(array $parameters, $path, $name = 'Gemfile')
    {
        parent::__construct($parameters, $path, $name);
    }

    /**
     * Writes Gemfile.
     */
    public function write()
    {
        $gemfile = '';

        foreach ($this->parameters as $gem) {
            $line = str_replace('<gem>', $gem, self::$template);
            $gemfile = sprintf('%s%s%s', $gemfile, PHP_EOL, $line);
        }

        fwrite($this->file, $this->addHeaders($gemfile));

        return $this;
    }
}
