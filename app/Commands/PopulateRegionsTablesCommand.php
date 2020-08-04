<?php

namespace App\Commands;

use App\Bairro;
use App\City;
use App\Estado;
use App\Municipio;
use App\State;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class PopulateRegionsTablesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'db:populate';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Read the originals nonstandard tables of States, cities and districts and migrate data to stadard tables.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $this->info("Creating and populating unnormalized tables:\n");
            $this->populateUnnormalizedTables();

            $this->info("\nCreating and populating normalized tables:\n");

            Artisan::call("migrate");
            $this->line(Artisan::output());

            $this->populateNormalizedTables();

            $this->info("\n\nAll states, cities and districts saved with success!\n");
            $this->notify("Brazil Regions Script", "States, cities and districts tables are populated with success.");

            DB::commit();
        } catch (\Exception $e) {
            $this->error("\n\nAn error occur while executing the script.\n\n");
            $this->notify("Brazil Regions Script", "An error occur while executing the script.");
            $this->error($e->getMessage());
            DB::rollBack();
        }

        return 0;
    }

    private function populateUnnormalizedTables()
    {
        $this->task('Importing states', function() {
            try {
                DB::unprepared(file_get_contents('database/sql/Estados.sql'));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return false;
            }

            return true;
        });

        $this->task('Importing cities', function() {
            try {
                DB::unprepared(file_get_contents('database/sql/Municipios.sql'));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return false;
            }

            return true;
        });

        $this->task('Importing districts', function() {
            try {
                DB::unprepared(file_get_contents('database/sql/Bairros.sql'));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                return false;
            }

            return true;
        });
    }

    private function populateNormalizedTables()
    {
        $estados = Estado::all();

        foreach ($estados as $estado) {
            $state = $this->persistState($estado);

            $municipios = Municipio::where('Uf', $state->initials)->get();
            $municipiosProgressBar = $this->createCitiesProgressBar($municipios, $state);

            foreach ($municipios as $municipio) {
                $city = $this->persistCity($municipio, $state);
                $municipiosProgressBar->advance();

                $bairros = Bairro::where('Uf', $state->initials)->get();

                foreach ($bairros as $bairro) {
                    $this->persistDistrict($bairro, $city);
                }
            }

            $municipiosProgressBar->finish();
        }
    }

    private function persistState(Estado $estado) : State
    {
        $state = new State();
        $state->name = $estado->Nome;
        $state->initials = $estado->Uf;
        $state->save();

        return $state;
    }

    private function persistCity(Municipio $municipio, State $state) : City
    {
        $city = new City();
        $city->name = $municipio->Nome;
        $state->cities()->save($city);

        return $city;
    }

    private function persistDistrict(Bairro $bairro, City $city)
    {
        $delimiter = strtolower($city->name);
        $bairroPieces = explode(" - $delimiter", strtolower($bairro->Nome));

        if (count($bairroPieces) === 2 && empty($bairroPieces[1])) {
            $districtName = $this->treatDistrictName($bairroPieces[0]);
            $city->districts()->create(['name' => $districtName]);
        }
    }

    private function treatDistrictName($districtName)
    {
        $districtName = Str::title(trim($districtName));

        $districtName = str_replace(' Ix', ' IX', $districtName);
        $districtName = str_replace(' Viii', ' VIII', $districtName);
        $districtName = str_replace(' Vii', ' VII', $districtName);
        $districtName = str_replace(' Vi', ' VI', $districtName);
        $districtName = str_replace(' Iv', ' IV', $districtName);
        $districtName = str_replace(' Iii', ' III', $districtName);
        $districtName = str_replace(' Ii', ' II', $districtName);

        return $districtName;
    }

    private function createCitiesProgressBar($citiesCollection, State $state)
    {
        $progressBar = $this->output->createProgressBar(count($citiesCollection));
        $this->line("\n\nSaving cities and districts for {$state->name}:\n");

        return $progressBar;
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
