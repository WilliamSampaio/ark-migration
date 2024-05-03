<?php

namespace Williamsampaio\ArkMigration\Commands;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Williamsampaio\ArkMigration\Util;

class Create extends AbstractCommand
{
    protected static $defaultName = 'create';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Create a new migration")
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the migration (in CamelCase)')
            ->setHelp(sprintf(
                "%sCreates a new database migration%s",
                PHP_EOL,
                PHP_EOL
            ));
    }

    protected function getCreateMigrationDirectoryQuestion()
    {
        return new ConfirmationQuestion('Create migrations directory? [y]/n ', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null($this->getConfig())) {
            throw new RuntimeException(sprintf(
                'Config file not set yet! Run init command.',
                self::CONFIG_FILE_PATH
            ));
        }

        $path = $this->getConfig()['path'];

        if (!file_exists($path)) {
            $helper = $this->getHelper('question');
            $question = $this->getCreateMigrationDirectoryQuestion();

            if ($helper->ask($input, $output, $question)) {
                mkdir($path, 0755, true);
            }
        }

        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Migration directory "%s" does not exist',
                $path
            ));
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Migration directory "%s" is not writable',
                $path
            ));
        }

        $path = realpath($path);
        $className = $input->getArgument('name');

        if ($className === null) {
            $currentTimestamp = Util::getCurrentTimestamp();
            $className = 'V' . $currentTimestamp;
            $fileName = $currentTimestamp . '.php';
        } else {
            if (!Util::isValidPhinxClassName($className)) {
                throw new InvalidArgumentException(sprintf(
                    'The migration name "%s" is invalid. Please use CamelCase format.',
                    $className
                ));
            }

            // Compute the file path
            $fileName = Util::mapClassNameToFileName($className);
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new InvalidArgumentException(sprintf(
                'The migration name "%s" already exists',
                $className
            ));
        }

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        $this->writeMigration($filePath);

        $output->writeln('<info>created</info> ' . $fileName);

        return self::CODE_SUCCESS;
    }

    protected function writeMigration($path)
    {
        if (is_file($path)) {
            throw new InvalidArgumentException(sprintf(
                'The file "%s" already exists',
                $path
            ));
        }

        // Check if dir is writable
        $dirname = dirname($path);
        if (!is_writable($dirname)) {
            throw new InvalidArgumentException(sprintf(
                'The directory "%s" is not writable',
                $dirname
            ));
        }

        // load the config template
        $migration_template = self::MIGRATION_TEMPLATE_FILE_PATH;
        if (file_exists($migration_template)) {
            $contents = file_get_contents($migration_template);
        } else {
            throw new RuntimeException(sprintf(
                'Could not find migration template "%s"!',
                $migration_template
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
