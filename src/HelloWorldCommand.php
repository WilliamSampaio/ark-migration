<?php

namespace Williamsampaio\ArkMigration;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloWorldCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'hello';

    protected function configure()
    {
        $this
            ->setDescription("Hello World!")
            ->setHelp("Hello World...")
            ->addArgument('name', InputArgument::OPTIONAL, 'Seu nome!')
            ->addArgument('sobrenome', InputArgument::OPTIONAL, 'Seu sobrenome!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null($input->getArgument('name'))) {
            $output->writeln(['Hello World!']);
        } else {
            $name = $input->getArgument('name');
            $output->writeln(["Hello $name!"]);
        }
    }
}
