<?php
/*
    Title: Diet Change Scheduler
    Name: update-diet-enteral
    Author: Randy
    Description:
                Selects updated doctor's order and inserts into patient's diet history
    Process:
        [1] Receives an argument: meal_time ('TODAY')
        [2] Get all unique hpercode of admitted patients from hdocord where dietgroup = 'E'
        [3] For each Unique Hpercode: get the latest update from hdocord
        [4] Insert latest update to patients_diet_history
        [5] Update log
*/
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\scheduledUpdates;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;

class UpdateDietEnteral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-diet-enteral {meal_time}, {time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update patient\'s diet.';

    /**
     *
     */
    protected $orders_repository;
    protected $diets_repository;

    /**
     *
     */
    public function __construct(DoctorsOrdersRepositoryInterface $orders_repository, DietsRepositoryInterface $diets_repository)
    {
        parent::__construct();
        $this->orders_repository = $orders_repository;
        $this->diets_repository = $diets_repository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mealtime = $this->argument('meal_time');
        $time = $this->argument('time');

        //log start
        Log::channel('scheduler')->info("[" . $mealtime . "]\t========START========");
        echo "========START========\n";
        echo "\nFetching patients...\n";

        // Get doctors orders
        $doctors_orders = $this->orders_repository->getAdmittedOrders('=', date("Y-m-d") . ' ' . $time);

        echo "Fetching patients completed.\n";

        // Process data
        echo "\nProcessing data...\n";

        // Modify data
        $insert_data = $doctors_orders->flatMap(function ($do) use ($time) {
            return collect(range(1, $do->feedingFrequency))->map(function ($i) use ($do, $time) {
                return [
                    'docointkey' => $do->docointkey,
                    'meal_time' => $i,
                    'created_at' => date("Y-m-d") . ' ' . $time,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => 'SCHEDULER'
                ];
            });
        })->toArray();

        // Store data
        $docointkeys = $doctors_orders->pluck('docointkey')->toArray();
        $hpercodes = $doctors_orders->pluck('hpercode')->toArray();

        echo "Processing data completed.\n";

        // Use a transaction to ensure atomicity
        DB::transaction(function () use ($mealtime, $time, $insert_data, $docointkeys, $hpercodes) {

            // Insert data
            echo "\nInserting doctors orders...\n";

            $batch_size = 300; // Set to not exceed 2100 limit

            // Insert into patients diet history
            foreach (array_chunk($insert_data, $batch_size) as $batch) {
                $this->diets_repository->storeHistory($batch);
            }

            echo "Insert completed.\n";

            // Update related tables
            echo "\nUpdating related tables...\n";

            // Lock and deactivate doctor's orders
            $this->orders_repository->lockAndDeactivateMultiple($hpercodes);

            // Update dostatus of hdocord as Active based on the latest doctors order
            $this->orders_repository->activateMultiple($docointkeys);

            echo "Updating related tables completed.\n";

            //log end
            Log::channel('scheduler')->info("[" . $mealtime . "]\t=========END=========");
            echo "\n=========END=========\n";
        }, 2);

        //  Reload all open pages.
        broadcast(new scheduledUpdates('Reload'));

        exec('timeout /t 3 /nobreak');
    }
}
