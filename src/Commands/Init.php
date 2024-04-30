<?php

namespace Williamsampaio\ArkMigration\Commands;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends AbstractCommand
{
    protected static $defaultName = 'init';

    protected function configure()
    {
        $this
            ->setDescription("Initialize the migrations")
            ->setHelp(sprintf(
                "%sInitializes the migrations%s",
                PHP_EOL,
                PHP_EOL
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists(self::CONFIG_FILE_PATH)) {
            throw new InvalidArgumentException(sprintf(
                'Config file "%s" already exists!',
                self::CONFIG_FILE_PATH
            ));
        }

        $this->writeConfig(self::CONFIG_FILE_PATH);

        $output->writeln(sprintf("<info>created</info> %s", self::CONFIG_FILE_PATH));

        return self::CODE_SUCCESS;
    }

    protected function writeConfig($path)
    {
        // Check if dir is writable
        $dirname = dirname($path);
        if (!is_writable($dirname)) {
            throw new InvalidArgumentException(sprintf(
                'The directory "%s" is not writable',
                $dirname
            ));
        }

        // load the config template
        $filename = self::CONFIG_TEMPLATE_FILE_PATH;
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
        } else {
            throw new RuntimeException(sprintf(
                'Could not find template "%s"!',
                $filename
            ));
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }
    }
}
