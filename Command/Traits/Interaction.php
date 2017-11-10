<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Command\Traits;

use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * This trait is the perfect copy of the Blast\Bundle\OuterExtensionBundle\Command\Traits\Interaction
 * They are still distinct because of the few amount of code to factorize, but it will be done
 * as soon as we have a bit more to make generic.
 **/
trait Interaction
{
    /**
     * @param OutputInterface $output
     * @param string          $message
     */
    private function writeError(OutputInterface $output, $message)
    {
        $output->writeln(sprintf("\n<error>%s</error>", $message));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $questionText
     * @param mixed           $default
     * @param callable        $validator
     *
     * @return mixed
     */
    private function askAndValidate(InputInterface $input, OutputInterface $output, $questionText, $default = null, $validator = null)
    {
        $questionHelper = $this->getQuestionHelper();

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if ($questionHelper instanceof DialogHelper) {
            return $questionHelper->askAndValidate($output, $questionHelper->getQuestion($questionText, $default), $validator, false, $default);
        }

        $question = new Question($questionHelper->getQuestion($questionText, $default), $default);

        if (null !== $validator) {
            $question->setValidator($validator);
        }

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $questionText
     * @param string          $default
     * @param string          $separator
     *
     * @return string
     */
    private function askConfirmation(InputInterface $input, OutputInterface $output, $questionText, $default, $separator)
    {
        $questionHelper = $this->getQuestionHelper();

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if ($questionHelper instanceof DialogHelper) {
            $question = $questionHelper->getQuestion($questionText, $default, $separator);

            return $questionHelper->askConfirmation($output, $question, ($default === 'no' ? false : true));
        }

        $question = new ConfirmationQuestion($questionHelper->getQuestion(
            $questionText,
            $default,
            $separator
        ), ($default === 'no' ? false : true));

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @return QuestionHelper|DialogHelper
     */
    private function getQuestionHelper()
    {
        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        if (class_exists('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')) {
            $questionHelper = $this->getHelper('dialog');

            if (!$questionHelper instanceof DialogHelper) {
                $questionHelper = new DialogHelper();
                $this->getHelperSet()->set($questionHelper);
            }
        } else {
            $questionHelper = $this->getHelper('question');

            if (!$questionHelper instanceof QuestionHelper) {
                $questionHelper = new QuestionHelper();
                $this->getHelperSet()->set($questionHelper);
            }
        }

        return $questionHelper;
    }
}
