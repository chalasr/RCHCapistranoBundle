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
