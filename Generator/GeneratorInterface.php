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
 * Interface for generators.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface GeneratorInterface
{
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
