<?php

/*
 * This file is part of the RCHCapistranoBundle.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public function getBundleVendorDir()
    {
        $psr4Path = $this->getRootDir().'/../vendor/rch/capistrano-bundle';
        $psr0Path = $psr4Path.'/RCH/CapistranoBundle';

        if (is_dir($psr0Path)) {
            return $psr0Path;
        }

        return $psr4Path;
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
