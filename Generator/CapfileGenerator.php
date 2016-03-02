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
 * Generates Capfile for capistrano.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class CapfileGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected static $template = "require '<requirement>'";

    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param string $path
     * @param string $name
     */
    public function __construct(array $parameters, $path, $name = 'Capfile')
    {
        parent::__construct($parameters, $path, $name);
    }

    /**
     * Writes Capfile.
     */
    public function write()
    {
        $capfile = '';

        foreach ($this->parameters as $namespace) {
            $line = str_replace('<requirement>', $namespace, self::$template);
            $capfile = sprintf('%s%s%s', $capfile, PHP_EOL, $line);
        }

        fwrite($this->file, $this->addHeaders($capfile));
    }
}
