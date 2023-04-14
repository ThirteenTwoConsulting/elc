<?php

namespace App\Commands;

use App\Models\Facility;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Support\{Arr, Str};
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'food:facility:info',
    description: 'Get info about a food facility'
)]
class FacilityInfo extends Command
{
    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of facility (truck or cart)')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Find only facilities with this status')
            ->addOption('permit', null, InputOption::VALUE_OPTIONAL, 'Look up facility by permit')
            ->addOption('street', null, InputOption::VALUE_OPTIONAL, 'Look for a facility on a particular street')
            ->addOption('cap', null, InputOption::VALUE_OPTIONAL, 'Return only a max of this number of results')
            ->addArgument('threshold', InputArgument::OPTIONAL, 'If more than this amoun of results found do not show data', 25)
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Applicant', 'Type', 'Address', 'Permit ID', 'Status', 'Food Item(s)', 'Schedule']);
        $table->setVertical();

        $data = json_decode(@file_get_contents(__DIR__ . '/../../resources/data/truckdata.json'), true);
        $facilities = collect();

        foreach ($data['data'] as $facility) {
            $facilities->push(new Facility($facility));
        }

        $criteria = [
            'type' => $input->getOption('type'),
            'status' => $input->getOption('status'),
            'permit' => $input->getOption('permit'),
            'address' => $input->getOption('street'),
        ];

        $capResults = $input->getOption('cap');
        $totalResults = 0;

        $isCriteriaEmpty = max($criteria) == null;
        $results = $facilities->slice(0, $input->getOption('cap'))->filter(function (Facility $facility) use ($criteria, $isCriteriaEmpty) {
            return $isCriteriaEmpty || $facility->checkCriteria($criteria);
        });

        $output->writeln('Food Truck Info Tool');
        $output->writeln('====================');
        $output->writeln('Found <info>' . $results->count() . '</info> results (<info>' . $facilities->count() . '</info> total)'. "\n");

        /** @var Facility $result */
        foreach ($results as $id => $result) {
            $table->setHeaderTitle('Result #<info>' . ($id + 1) . '</info>');
            $table->addRow([
                $result->applicant,
                $result->type,
                $result->getAddress(),
                $result->permit,
                $result->getStatus(),
                $result->foodItems,
                '<href=' . $result->schedule . '>' . $result->schedule . '</>',
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}