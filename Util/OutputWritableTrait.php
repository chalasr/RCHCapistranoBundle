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

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides helper methods.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
trait OutputWritableTrait
{
    /**
     * Writes stylized welcome message in Output.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function sayWelcome(OutputInterface $output)
    {
        $breakline = '';
        $output = $this->createBlockTitle($output);
        $title = $this->formatAsTitle('RCHCapistranoBundle - Continuous Deployment');

        $welcome = array(
            $breakline,
            $title,
            $breakline,
            'This bundle provides continuous deployment for Symfony2+ using <comment>Capistrano</comment>',
            'Created by Robin Chalas - github.com/chalasr',
            $breakline,
        );

        $output->writeln($welcome);
    }

    protected function createBlockTitle(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $output->getFormatter()->setStyle('title', $style);

        return $output;
    }

    /**
     * Formats string as output block title.
     *
     * @param string $content
     *
     * @return string
     */
    protected function formatAsTitle($content)
    {
        $formatter = $this->getHelper('formatter');
        $title = $formatter->formatBlock($content, 'title', true);

        return $title;
    }
}
