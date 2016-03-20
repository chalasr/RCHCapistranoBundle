<?php

/**
 * This file is part of RCH/CapistranoBundle.
 *
 * Robin Chalas <robin.chalas@gmail.com>
 *
 * For more informations about license, please see the LICENSE
 * file distributed in this source code.
 */

namespace RCH\CapistranoBundle\Util;

use Symfony\Component\Console\Exception\RuntimeException;

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
        return $this->getRootDir().'/../vendor/rch/capistrano-bundle/RCH/CapistranoBundle';
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

    /**
     * Get capistrano config directory.
     *
     * @return string
     */
    public function getCapistranoDeployDir()
    {
        return $this->getCapistranoDir().'/deploy';
    }

    /**
     * Get staging's configuration directory (Yaml).
     *
     * @return string
     */
    public function getStagingsConfigDir()
    {
        return $this->getPublishedConfigDir().'/staging';
    }

    /**
     * Get the Service Container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     *
     * @throws RuntimeException If the service container is not accessible
     */
    public function getContainer()
    {
        if (property_exists(__CLASS__, 'container')) {
            return $this->container;
        } else {
            return parent::getContainer();
        }

        throw new RuntimeException(sprintf('The service container must be accessible from class %s to use this trait', __CLASS__));
    }
}
