<?php
/*
    Title: Diet Change Scheduler
    Name: update-diet-oral
    Author: Randy
    Description:
                Selects updated doctor's order and inserts into patient's diet history
    Process:
        [1] Receives an argument: meal_time ('BREAKFAST', 'LUNCH', 'DINNER' ), time (05:00, 10:30, 16:00)
        [2] Lock all new doctor's order in hdocord
        [3] Get all unique hpercode of admitted patients from hdocord where dietgroup = 'O'
        [4] For each Unique Hpercode: get the latest update from hdocord
        [5] Insert latest update to patients_diet_history
        [6] Update log
*/
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Query\Builder;
use App\Events\scheduledUpdates;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use App\Repositories\Dietary\Interfaces\MealsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;

class UpdateDietOral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-diet-oral {meal_time}, {sns_time}, {time}';

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
    protected $meals_repository;
    protected $diets_repository;
    protected $sns_repository;

    /**
     * 
     */
    public function __construct(
        DoctorsOrdersRepositoryInterface $orders_repository, 
        MealsRepositoryInterface $meals_repository,
        DietsRepositoryInterface $diets_repository, 
        SNSRepositoryInterface $sns_repository)
    {
        parent::__construct();
        $this->orders_repository = $orders_repository;
        $this->meals_repository = $meals_repository;
        $this->diets_repository = $diets_repository;
        $this->sns_repository = $sns_repository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mealtime = $this->argument('meal_time');
        $snstime = $this->argument('sns_time');
        $time = $this->argument('time');

        //log start
        Log::channel('scheduler')->info("[" . $mealtime . " - " . $snstime . "]\t========START========");
        echo "========START========\n";
        echo "\nFetching patients...\n";

        // Get doctor's orders
        $doctors_orders = $this->orders_repository->getAdmittedOrders('!=', date("Y-m-d") . ' ' . $time);
        
        // Get doctor's orders for SNS
        $doctors_orders_ons = $this->orders_repository->getAdmittedOrdersSNS($snstime, date("Y-m-d") . ' ' . $time);
        
        echo "Fetching patients completed.\n";

        // Process data
        echo "\nProcessing data...\n";

        // Modify data
        $insert_data = $doctors_orders->map(function ($do) use($mealtime, $time) {
            return [
                'docointkey' => $do->docointkey,
                'meal_time' => $mealtime,
                'created_at' => date("Y-m-d") . ' ' . $time,
                'updated_at' => date("Y-m-d H:i:s"),
                'updated_by' => 'SCHEDULER'
            ];
        })->toArray();

        // Modify data for ONS
        $insert_data_ons = $doctors_orders_ons->map(function ($ons) use($snstime, $time) {
            return [
                'docointkey' => $ons->docointkey,
                'meal_time' => $snstime,
                'created_at' => date("Y-m-d") . ' ' . $time,
                'updated_at' => date("Y-m-d H:i:s"),
                'updated_by' => 'SCHEDULER',
                'status' => 'REQUESTED'
            ];
        })->toArray();
        
        // Store data
        $docointkeys = $doctors_orders->pluck('docointkey')->toArray();
        $enccodes = $doctors_orders->pluck('enccode')->toArray();
        $hpercodes = $doctors_orders->pluck('hpercode')->toArray();
        
        echo "Processing data completed.\n";

        // Use a transaction to ensure atomicity
        DB::transaction(function () use($mealtime, $snstime, $time, $doctors_orders, $insert_data, $insert_data_ons, $docointkeys, $enccodes, $hpercodes) {
            
            // Cancel current meals
            echo "\nCancelling meals not prepared...\n";
            
            //  Get docointkeys
            $dokeys = $this->orders_repository->getDocointkeys($enccodes);

            //  Cancel current meals
            $this->meals_repository->cancelCurrentMeals('patients_diet_history', $dokeys, $mealtime);
            
            echo "Cancelling meals not prepared completed.\n";

            // Insert data
            echo "\nInserting doctors orders...\n";
            
            $batch_size = 300; // Set to not exceed 2100 limit

            // Insert into patients diet history
            foreach (array_chunk($insert_data, $batch_size) as $batch) {
                $this->diets_repository->storeHistory($batch);
            }

            // Insert into patients snack history
            foreach (array_chunk($insert_data_ons, $batch_size) as $batch) {
                $this->sns_repository->storeHistory($batch);
            }

            echo "Insert completed.\n";

            // Update related tables
            echo "\nUpdating related tables...\n";
            
            // Lock and deactivate doctors' orders
            $this->orders_repository->lockAndDeactivateMultiple($hpercodes);

            // Update dostatus of hdocord as Active based on the latest doctors order
            $this->orders_repository->activateMultiple($docointkeys);

            echo "Updating related tables completed.\n";

            //log end
            Log::channel('scheduler')->info("[" . $mealtime . " - " . $snstime . "]\t=========END=========");
            echo "\n=========END=========\n";
        });
        
        //  Reload all open pages.
        broadcast(new scheduledUpdates('Reload'));

        exec('timeout /t 3 /nobreak');
    }
}
