<?php

namespace App\Services\Dietary;

use App\Services\Dietary\Interfaces\ReportsServiceInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;
use App\Repositories\Dietary\Interfaces\MealCensusRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ReportsService implements ReportsServiceInterface
{
    protected $diets_repository;
    protected $sns_repository;
    protected $census_repository;

    //  Constructor
    public function __construct(DietsRepositoryInterface $diets_repository, SNSRepositoryInterface $sns_repository, MealCensusRepositoryInterface $census_repository)
    {
        $this->diets_repository = $diets_repository;
        $this->sns_repository = $sns_repository;
        $this->census_repository = $census_repository;
    }

    //  Get monthly statistics report
    public function getMonthlyStatistics($date)
    {
        // Split the string into an array [month, year]
        [$month, $year] = explode(',', $date);

        // Ensure they are integers
        $month = (int) $month;
        $year = (int) $year;

        // Fetch records
        $data = $this->diets_repository->getMonthly($year, $month);
        $psych_data = $this->diets_repository->getMonthlybyWard($year, $month, '018');
        $sns_data = $this->sns_repository->getMonthly($year, $month);
        $nonPatient_data = $this->census_repository->getMonthly($year, $month);

        // Define diet categories and their corresponding diet codes
        $dietCategories = [
            'soft' => ['01', '02', '03', '13', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '57'],
            'full' => ['19', '16'],
            'therapeutic' => ['05', '06', '07', '08', '09', '11', '12', '20', '25', '30', '40', '41', '43', '42'],
            'liquid' => ['18', '17'],
            'therapeutic_milk' => ['29'],
            'formulas' => ['35', '36', '37', '38', '39', '44'],
            'blenderized' => ['34']
        ];

        // Define valid allergy values that should not be considered as precautions
        $notAllergy = ['NO KNOWN', 'No Known Allergy', 'NO KNOWN ALLERGIES', null];

        // Initialize the summary structure with default values
        $summaryTemplate = [
            'total_soft' => 0,
            'total_full' => 0,
            'total_therapeutic' => 0,
            'total_liquid' => 0,
            'total_therapeutic_milk' => 0,
            'total_formulas' => 0,
            'total_blenderized' => 0,
            'total_precautions' => 0,
            'total_meals_served' => 0,
            'total_sns' => 0
        ];

        // Summary for both Payward (PW) and Service (SV) categories
        $summary = [
            'PW' => $summaryTemplate,
            'SV' => $summaryTemplate,
            'PSYCH' => '',
            'NP' => '',
        ];

        // Iterate through meal data and update the corresponding summary
        foreach ($data as $meal) {
            if ($meal->tacode === 'ADPAY') {
                $this->updateSummary($summary, $meal, $dietCategories, $notAllergy, 'PW');
            } elseif ($meal->tacode === 'SERVI') {
                $this->updateSummary($summary, $meal, $dietCategories, $notAllergy, 'SV');
            }
        }

        $grouped = $sns_data->groupBy('tacode');

        $summary['PW']['total_sns'] = isset($grouped['ADPAY']) ? $grouped['ADPAY']->count() : 0;
        $summary['SV']['total_sns'] = isset($grouped['SERVI']) ? $grouped['SERVI']->count() : 0;
        $summary['PSYCH'] = $psych_data->count(); // Counts how many 
        $summary['NP'] = $nonPatient_data->reduce(function ($total, $value) { return $total += $value->qnty; }, 0);
        
        return $summary;
    }

    //  Export monthly statistics report
    public function exportMonthlyStatistics($date)
    {
        list($month, $year) = explode(',', $date);
        $dateCreated = Carbon::createFromDate($year, $month, 1);
        $formattedDate = $dateCreated->format('F Y');
        $data = $this->getMonthlyStatistics($date);

        $relativePath = "public/reports/MonthlyStatistics.xlsx";
        $fullPath = storage_path("app/{$relativePath}");
        $download_path = 'public/reports/Downloaded_MonthlyStatistics.xlsx';
        $updatedPath = storage_path("app/{$download_path}");
        
        // ✅ Step 1: Check if the file exists
        if (!Storage::exists($relativePath)) {
            abort(404, 'File not found.');
        }

        // ✅ Step 2: Load the existing Excel file
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($formattedDate);

        // ✅ Step 4: Insert new data at rowIndex
        $sheet->setCellValue('D3', $formattedDate);

        // TOTAL SUMMARY
        $sheet->setCellValue('A8', $data['PW']['total_meals_served']);
        $sheet->setCellValue('C8', $data['SV']['total_meals_served']);
        $sheet->setCellValue('E8', $data['PSYCH']); // In Patients Total
        $sheet->setCellValue('G8', $data['NP']); // Non Patients Total
        $sheet->setCellValue('I8', $data['PW']['total_meals_served'] + $data['SV']['total_meals_served'] + $data['PSYCH'] + $data['NP']); // Grand Total

        // PAYWARD
        $sheet->setCellValue('D14', $data['PW']['total_full']); // Full
        $sheet->setCellValue('D15', $data['PW']['total_soft']); // Soft
        $sheet->setCellValue('D16', $data['PW']['total_therapeutic']); // Therapeutic
        $sheet->setCellValue('D17', $data['PW']['total_precautions']); // Precautions
        $sheet->setCellValue('D18', $data['PW']['total_liquid']); // Liquid
        $sheet->setCellValue('D20', $data['PW']['total_blenderized']); // Blenderized
        $sheet->setCellValue('D21', $data['PW']['total_formulas']); // Formulated
        $sheet->setCellValue('D22', $data['PW']['total_therapeutic_milk']); // Milk
        $sheet->setCellValue('D23', $data['PW']['total_sns']); // SNS
        $sheet->setCellValue('D24', $data['PW']['total_meals_served']); // Total

        // SERVICE
        $sheet->setCellValue('G14', $data['SV']['total_full']); // Full
        $sheet->setCellValue('G15', $data['SV']['total_soft']); // Soft
        $sheet->setCellValue('G16', $data['SV']['total_therapeutic']); // Therapeutic
        $sheet->setCellValue('G17', $data['SV']['total_precautions']); // Precautions
        $sheet->setCellValue('G18', $data['SV']['total_liquid']); // Liquid
        $sheet->setCellValue('G20', $data['SV']['total_blenderized']); // Blenderized
        $sheet->setCellValue('G21', $data['SV']['total_formulas']); // Formulated
        $sheet->setCellValue('G22', $data['SV']['total_therapeutic_milk']); // Milk
        $sheet->setCellValue('G23', $data['SV']['total_sns']); // SNS
        $sheet->setCellValue('G24', $data['SV']['total_meals_served']); // Total

        // DIET TOTAL
        $sheet->setCellValue('J14', $data['SV']['total_full'] + $data['PW']['total_full']); // Full
        $sheet->setCellValue('J15', $data['SV']['total_soft'] + $data['PW']['total_soft']); // Soft
        $sheet->setCellValue('J16', $data['SV']['total_therapeutic'] + $data['PW']['total_therapeutic']); // Therapeutic
        $sheet->setCellValue('J17', $data['SV']['total_precautions'] + $data['PW']['total_precautions']); // Precautions
        $sheet->setCellValue('J18', $data['SV']['total_liquid'] + $data['PW']['total_liquid']); // Liquid
        $sheet->setCellValue('J20', $data['SV']['total_blenderized'] + $data['PW']['total_blenderized']); // Blenderized
        $sheet->setCellValue('J21', $data['SV']['total_formulas'] + $data['PW']['total_formulas']); // Formulated
        $sheet->setCellValue('J22', $data['SV']['total_therapeutic_milk'] + $data['PW']['total_therapeutic_milk']); // Milk
        $sheet->setCellValue('J23', $data['SV']['total_sns'] + $data['PW']['total_sns']); // SNS

        // TA TOTAL
        $sheet->setCellValue('D24', $data['PW']['total_meals_served']);
        $sheet->setCellValue('G24', $data['SV']['total_meals_served']);
        $sheet->setCellValue('J24', $data['PW']['total_meals_served'] + $data['SV']['total_meals_served']);

        // NOURISHMENTS
        $sheet->setCellValue('E29', $data['PSYCH']);
        $sheet->setCellValue('E31', $data['NP']);
        $sheet->setCellValue('E33', $data['NP'] + $data['PSYCH']);

        // ✅ Step 5: Save the updated file properly
        $writer = new Xlsx($spreadsheet);
        $writer->save($updatedPath); // ✅ Correctly saves as XLSX format

        // ✅ Step 6: Check if file is created
        if (!Storage::exists($download_path)) {
            abort(404, 'File not found.');
        }

        // clean any output buffer
        ob_end_clean();

        // ✅ Step 7: Return path
        return $download_path;
    }

    /**
     * Updates the summary counts based on meal data.
     *
     * @param array $summary Reference to the summary array.
     * @param object $meal The meal object containing diet and precaution details.
     * @param array $dietCategories Mapping of diet categories to their respective diet codes.
     * @param array $notAllergy Array list indicating that patient doesn't have food allergies.
     * @param string $type Either 'PW' (Payward) or 'SV' (Service).
     */
    function updateSummary(&$summary, $meal, $dietCategories, $notAllergy, $type)
    {
        // Increment the total meals served counter
        $summary[$type]['total_meals_served']++;

        // Check if the meal should be considered under precautions
        if (!in_array($meal->category, $notAllergy) || !is_null($meal->precaution)) {
            $summary[$type]['total_precautions']++;
        } else {
            // Loop through diet categories and increment corresponding count if dietcode matches
            foreach ($dietCategories as $category => $codes) {
                if (in_array($meal->dietcode, $codes)) {
                    $summary[$type]["total_{$category}"]++;
                }
            }
        }
    }
}