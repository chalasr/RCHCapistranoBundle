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
 * Handles configuration file generation.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class Handler
{
    protected $generator;

    /**
     * Constructor.
     *
     * @param GeneratorInterface $generator
     */
    public function __construct(GeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generates.
     */
    public function generate()
    {
        $this->generator->open();
        $this->generator->write();
        $this->generator->close();
    }

    /**
     * @static
     *
     * @param GeneratorInterface $generator
     *
     * @return Handler
     */
    public static function create(GeneratorInterface $generator)
    {
        return new self($generator);
    }
}
