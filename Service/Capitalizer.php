<?php

/*
 * This file is part of the RCHCapistranoBundle.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RCH\CapistranoBundle\Service;

/**
 * Capitalizes strings corresponding to language.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class Capitalizer
{
    /**
     * Convert strings from under_scores to CamelCase.
     *
     * @param mixed $base
     *
     * @return mixed
     */
    public function camelize($base)
    {
        $callback = create_function('$c', 'return strtoupper($c[1]);');

        if (!is_array($base)) {
            return preg_replace_callback('/_([a-z])/', $callback, $base);
        }

        foreach ($base as $key => $val) {
            unset($base[$key]);
            $newKey = preg_replace_callback('/_([a-z])/', $callback, $key);
            $base[$newKey] = $val;
        }

        return $base;
    }

    /**
     * Converts strings from camelCase to under_score.
     *
     * @param mixed $base
     *
     * @return mixed
     */
    public function uncamelize($base)
    {
        $callback = create_function('$c', 'return "_" . strtolower($c[1]);');

        if (!is_array($base)) {
            $base[0] = strtolower($base[0]);

            return preg_replace_callback('/([A-Z])/', $callback, $base);
        }

        foreach ($base as $key => $val) {
            unset($base[$key]);
            $newKey = preg_replace_callback('/([A-Z])/', $callback, $key);
            $base[$newKey] = $val;
        }

        return $base;
    }
}
