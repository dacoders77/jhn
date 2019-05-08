<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Sample table output instead of a progress bar
 *
 * Class ProgressBook
 * @package Symfony\Component\Console\Helper
 */
final class ProgressBook
{
    private $barWidth = 28;
    private $barChar;
    private $emptyBarChar = '-';
    private $progressChar = '>';
    private $format;
    private $internalFormat;
    private $redrawFreq = 1;
    private $output;
    private $step = 0;
    private $max;
    private $startTime;
    private $stepWidth;
    private $percent = 0.0;
    private $formatLineCount;
    private $messages = [];
    private $overwrite = true;
    private $terminal;
    private $firstRun = true;
    private $console;

    private static $formatters;
    private static $formats;

    /**
     * @param OutputInterface $output An OutputInterface instance
     * @param int             $max    Maximum steps (0 if unknown)
     */
    public function __construct(OutputInterface $output, int $max = 0, $console)
    {
        $this->console = $console;

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->output = $output;
        $this->setMaxSteps($max);
        $this->terminal = new Terminal();

        if (!$this->output->isDecorated()) {
            // disable overwrite when output does not support ANSI codes.
            $this->overwrite = false;

            // set a reasonable redraw frequency so output isn't flooded
            $this->setRedrawFrequency($max / 100);
        }

        $this->startTime = time();
    }


    /**
     * Gets the format for a given name.
     *
     * @param string $name The format name
     *
     * @return string|null A format string
     */
    public static function getFormatDefinition(string $name): ?string
    {
        if (!self::$formats) {
            self::$formats = self::initFormats();
        }

        return isset(self::$formats[$name]) ? self::$formats[$name] : null;
    }

    /**
     * Sets the redraw frequency.
     *
     * @param int|float $freq The frequency in steps
     */
    public function setRedrawFrequency(int $freq)
    {
        $this->redrawFreq = max($freq, 1);
    }

    /**
     * Advances the progress output X steps.
     *
     * @param int $step Number of steps to advance
     */
    public function advance(int $step = 1, $headers, $tableData)
    {
        $this->setProgress($this->step + $step, $headers, $tableData);
        // dump($this->step); // Works good
    }

    /**
     * Sets whether to overwrite the progressbar, false for new line.
     */
    public function setOverwrite(bool $overwrite)
    {
        $this->overwrite = $overwrite;
    }

    public function setProgress(int $step, $headers, $tableData)
    {
        if ($this->max && $step > $this->max) {
            $this->max = $step;
        } elseif ($step < 0) {
            $step = 0;
        }

        $prevPeriod = (int) ($this->step / $this->redrawFreq);
        $currPeriod = (int) ($step / $this->redrawFreq);
        $this->step = $step;
        $this->percent = $this->max ? (float) $this->step / $this->max : 0;
        if ($prevPeriod !== $currPeriod || $this->max === $step) {
            $this->display($headers, $tableData);
        }
    }

    public function setMaxSteps(int $max)
    {
        $this->format = null;
        $this->max = max(0, $max);
        $this->stepWidth = $this->max ? Helper::strlen((string) $this->max) : 4;
    }


    /**
     * Outputs the current progress string.
     */
    public function display($headers, $tableData): void
    {
        if (OutputInterface::VERBOSITY_QUIET === $this->output->getVerbosity()) {
            return;
        }

        if (null === $this->format) {
            $this->setRealFormat($this->internalFormat ?: $this->determineBestFormat());
        }

        //$this->overwrite($this->buildLine());

        $message = "dfdfg " . $this->step . "\nkkkkkkk\n";
        $this->overwrite(''); // $message

        //$table = new Table($this->console->getOutput());// Works excellent
        //$table->setHeaders(array('jjj', "Score', 'Status $this->step"));
        //$table->render();

        $this->console->table($headers, $tableData);

    }

    /**
     * Removes the progress bar from the current line.
     *
     * This is useful if you wish to write some output
     * while a progress bar is running.
     * Call display() to show the progress bar again.
     */
    public function clear(): void
    {
        if (!$this->overwrite) {
            return;
        }

        if (null === $this->format) {
            $this->setRealFormat($this->internalFormat ?: $this->determineBestFormat());
        }

        $this->overwrite('');
    }

    private function setRealFormat(string $format)
    {
        // try to use the _nomax variant if available
        if (!$this->max && null !== self::getFormatDefinition($format.'_nomax')) {
            $this->format = self::getFormatDefinition($format.'_nomax');
        } elseif (null !== self::getFormatDefinition($format)) {
            $this->format = self::getFormatDefinition($format);
        } else {
            $this->format = $format;
        }

        $this->formatLineCount = substr_count($this->format, "\n");
    }

    /**
     * Overwrites a previous message to the output.
     */
    private function overwrite(string $message): void
    {

        if ($this->overwrite) {
            if (!$this->firstRun) {
                // Erase previous lines
                if ($this->formatLineCount > 0) {
                    $message = str_repeat("\x1B[1A\x1B[2K", $this->formatLineCount).$message;
                }
                // Move the cursor to the beginning of the line and erase the line
                $message = "\x0D\x1B[2K$message";
            }
        } elseif ($this->step > 0) {

            $message = PHP_EOL.$message;
        }

        $this->firstRun = false;
        $this->output->write($message);

        /*if ($this->overwrite) {
            if (!$this->firstRun) {
                if ($this->output instanceof ConsoleSectionOutput) {
                    $lines = floor(Helper::strlen($message) / $this->terminal->getWidth()) + $this->formatLineCount + 1;
                    $this->output->clear($lines);
                } else {
                    $this->output->write(sprintf("\033\143")); // IT WORKS! CLEAR THE WHOLE SCREEN
                    // Erase previous lines
                    if ($this->formatLineCount > 0) {
                            $message = str_repeat("\x1B[1A\x1B[2K", $this->formatLineCount).$message;
                    }

                    // Move the cursor to the beginning of the line and erase the line
                    $message = "\x0D\x1B[2K$message";
                }
            }
        } elseif ($this->step > 0) {
            $message = PHP_EOL.$message;
        }

        $this->firstRun = false;
        $this->output->write($message);*/
    }

    private function determineBestFormat(): string
    {
        switch ($this->output->getVerbosity()) {
            // OutputInterface::VERBOSITY_QUIET: display is disabled anyway
            case OutputInterface::VERBOSITY_VERBOSE:
                return $this->max ? 'verbose' : 'verbose_nomax';
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                return $this->max ? 'very_verbose' : 'very_verbose_nomax';
            case OutputInterface::VERBOSITY_DEBUG:
                return $this->max ? 'debug' : 'debug_nomax';
            default:
                return $this->max ? 'normal' : 'normal_nomax';
        }
    }


    private static function initFormats(): array
    {
        return [
            'normal' => ' %current%/%max% [%bar%] %percent:3s%%',
            'normal_nomax' => ' %current% [%bar%]',

            'verbose' => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%',
            'verbose_nomax' => ' %current% [%bar%] %elapsed:6s%',

            'very_verbose' => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%',
            'very_verbose_nomax' => ' %current% [%bar%] %elapsed:6s%',

            'debug' => ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%',
            'debug_nomax' => ' %current% [%bar%] %elapsed:6s% %memory:6s%',
        ];
    }


}
