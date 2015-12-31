<?php

namespace Chalasdev\CapistranoBundle\Generator;

/**
 * Generates stagings.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class StagingGenerator
{
    /**
     * @var array
     */
    protected $parameters;

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
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Generates staging code from parameters.
     *
     * @return string
     */
    public function generate()
    {
        foreach ($this->getParameters() as $prop => $value) {
            $placeHolders[] = sprintf('<%s>', $prop);
            $replacements[] = $value;
        }

        return str_replace($placeHolders, $replacements, self::$template);
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    protected function getParameters()
    {
        return $this->parameters;
    }
}
