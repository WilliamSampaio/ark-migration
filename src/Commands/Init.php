<?php

namespace Williamsampaio\ArkMigration\Commands;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Init extends Command
{
    protected static $FILE_NAME = 'arkconfig';
    protected static $defaultName = 'init';

    protected function configure()
    {
        $this
            ->setDescription("Initialize the migrations")
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to initialize migrations')
            ->setHelp(sprintf(
                "%sInitializes the migrations%s",
                PHP_EOL,
                PHP_EOL
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->resolvePath($input);
        $this->writeConfig($path);

        $output->writeln("<info>created</info> {$path}");

        return 0;
    }

    protected function resolvePath(InputInterface $input)
    {
        $path = (string) $input->getArgument('path');

        if (!$path) {
            $path = getcwd() . DIRECTORY_SEPARATOR . self::$FILE_NAME . '.php';
        }

        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . self::$FILE_NAME . '.php';
        }

        $dirname = dirname($path);
        if (is_dir($dirname) && !is_file($path)) {
            return $path;
        }

        if (is_file($path)) {
            throw new InvalidArgumentException(sprintf(
                'Config file "%s" already exists!',
                $path
            ));
        }

        // Dir is invalid
        throw new InvalidArgumentException(sprintf(
            'Invalid path "%s" for config file!',
            $path
        ));
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
        $filename = __DIR__ . '/../../' . self::$FILE_NAME . '.php.dist';
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
