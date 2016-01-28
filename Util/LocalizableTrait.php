<?php

/*
* This file is part of RCH/CapistranoBundle.
*
* Robin Chalas <robin.chalas@gmail.com>
*
* For more informations about license, please see the LICENSE
* file distributed in this source code.
*/

namespace RCH\CapistranoBundle\Util;

/**
 * Retrieves directories absolute paths.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
trait LocalizableTrait
{
    /**
     * Get application root directory.
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->getContainer()->get('kernel')->getRootDir();
    }

    /**
     * Get bundle directory.
     *
     * @return string
     */
    public function getBundleDir()
    {
        return $this->getRootDir().'/../vendor/chalasr/capistrano-bundle/RCH/CapistranoBundle';
    }

    /**
     * Get published config directory.
     *
     * @return string
     */
    public function getPublishedConfigDir()
    {
        return $this->getRootdir().'/config/rch';
    }

    /**
     * Get capistrano config directory.
     *
     * @return string
     */
    public function getCapistranoDir()
    {
        return $this->getRootdir().'/../config';
    }
}
