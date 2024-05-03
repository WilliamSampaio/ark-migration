<?php

namespace Williamsampaio\ArkMigration\Commands;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends AbstractCommand
{
    protected static $defaultName = 'migrate';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->addOption('--environment', '-e', InputOption::VALUE_REQUIRED, 'The target environment')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to migrate to')
            ->setDescription("Migrate the database")
            ->setHelp(
                <<<EOT
The <info>migrate</info> command runs all available migrations, optionally up to a specific version

<info>ark migrate -e development</info>
<info>ark migrate -e development -t 20110103081132</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null($this->getConfig())) {
            throw new RuntimeException(sprintf(
                'Config file not set yet! Run init command.',
                self::CONFIG_FILE_PATH
            ));
        }

        $version = $input->getOption('target');
        $environment = $input->getOption('environment');

        if ($environment === null) {
            $environment = $this->getConfig()['environments']['default_environment'];
            $output->writeln('<comment>warning</comment> no environment specified, defaulting to: ' . $environment);
        } else {
            $output->writeln('<info>using environment</info> ' . $environment);
        }

        if (!array_key_exists($environment, $this->getConfig()['environments'])) {
            $output->writeln(sprintf('<error>The environment "%s" does not exist</error>', $environment));
            return self::CODE_ERROR;
        }

        $envOptions = $this->getConfig()['environments'][$environment];

        if (isset($envOptions['name'])) {
            $output->writeln('<info>using database</info> ' . $envOptions['name']);
        } else {
            $output->writeln('<error>Could not determine database name! Please specify a database name in your config file.</error>');
            return self::CODE_ERROR;
        }

        var_dump($this->getConfig());
        die;

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');

        return self::CODE_SUCCESS;
    }
}
