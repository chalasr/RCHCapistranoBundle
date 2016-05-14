<?php

/*
 * This file is part of the RCHCapistranoBundle.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    protected static $template = 'gem <gem>';

    private static $sourceTemplate = "

source 'https://rubygems.org'
";

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

        $gemfile .= self::$sourceTemplate;

        fwrite($this->file, $this->addHeaders($gemfile));
    }
}
