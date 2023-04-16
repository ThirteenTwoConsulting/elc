<?php

namespace App\Commands;

use App\Models\Facility;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'food:facility:info',
    description: 'Get info about a food facility'
)]
class FacilityInfo extends Command
{
    private string $cachedData = __DIR__ . '/../../resources/data/facility_data.json';

    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of facility (truck or cart)')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Find only facilities with this status')
            ->addOption('permit', null, InputOption::VALUE_OPTIONAL, 'Look up facility by permit')
            ->addOption('street', null, InputOption::VALUE_OPTIONAL, 'Look for a facility on a particular street')
            ->addOption('cap', null, InputOption::VALUE_OPTIONAL, 'Return only a max of this number of results', 500)
            ->addOption('live', null, null, 'If set, fetch live data instead of cached JSON file')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Applicant', 'Type', 'Address', 'Permit ID', 'Status', 'Food Item(s)', 'Schedule']);
        $table->setVertical();

        /**
         * Read in the saved data.
         * 
         * Ideally we would instead use a databae if we wanted to do some advanced filtering and such,
         * but for a MVP/PoC we can read in static data.
         */
        $data = $this->getFacilityData($input, $output);
        $facilities = collect();

        $data->each(fn ($facilityData) => $facilities->push(new Facility($facilityData)));

        $criteria = [
            'type' => $input->getOption('type'),
            'status' => $input->getOption('status'),
            'permit' => $input->getOption('permit'),
            'address' => $input->getOption('street'),
        ];

        // Kind of a hack to determine if any filters should be applied, as value will be false if not passed in
        $isCriteriaEmpty = empty(max($criteria));

        $results = $facilities->slice(0, $input->getOption('cap'))->filter(function (Facility $facility) use ($criteria, $isCriteriaEmpty) {
            return $isCriteriaEmpty || $facility->checkCriteria($criteria);
        });

        $output->writeln('Food Truck Info Tool');
        $output->writeln('====================');

        $table->setHeaderTitle('Found <info>' . $results->count() . '</info> results (<info>' . $facilities->count() . '</info> total)'. "\n");

        /** @var Facility $result */
        $results->each(fn (Facility $result) => $table->addRow([
                $result->applicant,
                $result->type,
                $result->getAddress(),
                $result->permit,
                $result->getStatus(),
                $result->foodItems,
                '<href=' . $result->schedule . '>' . $result->schedule . '</>',
            ])
        );

        $table->render();

        return Command::SUCCESS;
    }

    private function getFacilityData(InputInterface $input, OutputInterface $output): Collection
    {
        $doItLive = $input->getOption('live');

        if (!$doItLive) {
            $output->writeln('<comment>WARNING!</comment> Using cached data.  Information may be outdated.  Use --live to update cached data.');
            $output->writeln('');

            if (!@file_exists($this->cachedData)) {
                $output->writeln('<error>ERROR!</error> No cached data found.  Pulling from data source.');
                $output->writeln('');
            } else {
                return collect(@json_decode(file_get_contents($this->cachedData))->data);
            }
        }

        $client = new Client();
        $facilities = $client->get('https://data.sfgov.org/api/views/rqzj-sfat/rows.json')->getBody();
        $decoded = @json_decode($facilities);

        return (json_last_error() === JSON_ERROR_NONE) ? collect($decoded->data) : collect();
    }
}