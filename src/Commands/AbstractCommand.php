<?php

namespace Williamsampaio\ArkMigration\Commands;

use RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    const CONFIG_FILE_NAME = 'arkconfig.php';
    const CONFIG_FILE_PATH = __DIR__ . '/../../' . self::CONFIG_FILE_NAME;
    const CONFIG_TEMPLATE_FILE_NAME = 'arkconfig.php.dist';
    const CONFIG_TEMPLATE_FILE_PATH = __DIR__ . '/../../' . self::CONFIG_TEMPLATE_FILE_NAME;
    const MIGRATION_TEMPLATE_FILE_NAME = 'migration.php.dist';
    const MIGRATION_TEMPLATE_FILE_PATH = __DIR__ . '/../../' . self::MIGRATION_TEMPLATE_FILE_NAME;

    const CODE_SUCCESS = 0;
    const CODE_ERROR = 1;

    protected $config = null;

    protected function getConfig()
    {
        if ($this->config == null) {
            $this->loadConfig();
        }

        return $this->config;
    }

    private function loadConfig()
    {
        if (!file_exists(self::CONFIG_FILE_PATH)) {
            return null;
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        $configArray = include self::CONFIG_FILE_PATH;

        // Hide console output
        ob_end_clean();

        if (!is_array($configArray)) {
            throw new RuntimeException(sprintf(
                'PHP file \'%s\' must return an array',
                self::CONFIG_FILE_PATH
            ));
        }

        $this->config = $configArray;
    }
}
