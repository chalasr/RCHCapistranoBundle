<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Generator;

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
    protected static $template = "gem <gem>";

    private static $sourceTemplate = PHP_EOL."source 'https://rubygems.org'";

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

        $this->addSource($gemfile);

        fwrite($this->file, $this->addHeaders($gemfile));
    }

    /**
     * Writes the gems source.
     */
    private function addSource(&$gemfile)
    {
        $gemfile = sprintf('%s%s%s',  $gemfile, PHP_EOL, self::$sourceTemplate);
    }
}
