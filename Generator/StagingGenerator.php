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
class StagingGenerator extends AbstractGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $name;

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
    public function __construct(array $parameters, $path, $name = 'production')
    {
        parent::__construct($parameters, $path);
        $this->name = $name;
    }

    /**
     * Generates staging from parameters.
     */
    public function generate()
    {
        $this->open();

        $this->write();

        $this->close();
    }

    /**
     * Open file at given path.
     */
    public function open()
    {
        $this->path = sprintf('%s/../config/deploy/%s.rb', $this->path, $this->name);
        $this->file = fopen($this->path, 'a');
    }

    /**
     * Write staging in file.
     *
     * @param string $staging Generated staging
     *
     * @return resource
     */
    public function write()
    {
        foreach ($this->parameters as $prop => $value) {
            $placeHolders[] = sprintf('<%s>', $prop);
            $replacements[] = $value;
        }

        $staging = str_replace($placeHolders, $replacements, self::$template);

        fwrite($this->file, $this->addHeaders($staging));
    }

    /**
     * Close generated file.
     */
    public function close()
    {
        fclose($this->file);
    }

    /**
     * Get staging path.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
