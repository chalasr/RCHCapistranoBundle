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
class StagingGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected static $template =
"# Production
server '<domain>',
user: '<user>',
ssh_options: {
		user: '<user>',
		keys: %w(<keys>),
		forward_agent: <forwardAgent>,
		auth_methods: %w(<authMethods>),
}

set(:deploy_to, '<deployTo>')";

    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param string $path
     * @param string $name
     */
    public function __construct(array $parameters, $path, $name = 'production.rb')
    {
        parent::__construct($parameters, $path, $name);
        $this->path = sprintf('%s/../config/deploy/%s', $path, $name);
    }

    /**
     * Write staging in file.
     *
     * @param string $staging Generated staging
     */
    public function write()
    {
        foreach ($this->parameters as $prop => $value) {
            $placeHolders[] = sprintf('<%s>', $prop);
            $replacements[] = $value;
        }

        $content = str_replace($placeHolders, $replacements, self::$template);
        fwrite($this->file, $this->addHeaders($content));
    }
}
