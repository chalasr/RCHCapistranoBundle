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
 * Interface for generators.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * Handles file generation.
     */
    public function generate();

    /**
     * Opens an existing or newly created file.
     */
    public function open();

    /**
     * Writes in file.
     */
    public function write();

    /**
     * Closes the file.
     */
    public function close();
}
