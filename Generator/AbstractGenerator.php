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
 * Abstract class for generators.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $file;

    /**
     * @var string
     */
    protected static $headersTemplate = '
# This file is part of the RCHCapistranoBundle.
#
# (c) Robin Chalas <robin.chalas@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
';

    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param string $path
     * @param string $name
     */
    public function __construct(array $parameters, $path, $name)
    {
        $this->parameters = $parameters;
        $this->path = sprintf('%s/../%s', $path, $name);
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        if (!is_dir($directory = dirname($this->path))) {
            try {
                mkdir($directory, 0777);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s". Please change the permissions or create the directory manually.', $directory));
            }
        }

        if (file_exists($this->path)) {
            unlink($this->path);
        }

        $this->file = fopen($this->path, 'w');
    }

    /**
     * Close generated file.
     */
    public function close()
    {
        fclose($this->file);
    }

    /**
     * Add license headers.
     *
     * @internal
     *
     * @return string
     */
    protected function addHeaders($generated)
    {
        return sprintf('%s%s%s', self::$headersTemplate, PHP_EOL, $generated);
    }
}
