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

use RCH\CapistranoBundle\Generator\AbstractGenerator;
use RCH\CapistranoBundle\Generator\Handler;
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
        $title = $this->formatAsTitle('Thank\'s to use RCHCapistranoBundle');

        $welcome = array(
            $breakline,
            $title,
            $breakline,
            'This bundle makes deployment easier by automating use of <comment>capistrano/symfony</comment>',
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

    /**
     * Generates a document from given configuration using given generator.
     *
     * @param AbstractGenerator $generator Instance of AbstractGeneratord
     *
     * @return callable The generation callback
     */
    public function generate(AbstractGenerator $generator)
    {
        $callback = function () use ($generator) {
            $handler = Handler::create($generator);
            $handler->generate();
        };

        return $callback();
    }
}
