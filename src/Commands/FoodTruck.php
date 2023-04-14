<?php

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'food:facility:info',
    description: 'Get info about a food facility'
)]
class FoodTruck extends Command
{
    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of facility (truck or cart)', 'truck')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Find only facilities with this status', 'approved')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        //
    }
}