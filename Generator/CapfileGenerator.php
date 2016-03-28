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
 * Generates Capfile for capistrano.
 *
 * @internal
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
     * @var string
     */
    protected static $importTemplate = "

Dir.glob('config/tasks/*.rake').each { |r| import r }
";

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

        // $capfile .= PHP_EOL.PHP_EOL;
        $capfile .= self::$importTemplate;

        fwrite($this->file, $this->addHeaders($capfile));
    }
}
