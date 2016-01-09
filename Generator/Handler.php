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
 * Handles configuration file generation.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class Handler
{
    protected $generator;

    /**
     * @param Source\SourceIteratorInterface $source
     * @param Writer\WriterInterface         $writer
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
     * @param Source\SourceIteratorInterface $source
     * @param Writer\WriterInterface         $writer
     *
     * @return Handler
     */
    public static function create(GeneratorInterface $generator)
    {
        return new self($generator);
    }
}
