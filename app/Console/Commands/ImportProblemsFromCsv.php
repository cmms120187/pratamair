<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Problem;
use Illuminate\Support\Facades\File;

class ImportProblemsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'problems:import-csv {file=basic problem.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import problems from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $filePath = base_path($filename);
        
        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }
        
        $this->info("Reading CSV file: {$filename}");
        
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("Cannot open file: {$filePath}");
            return 1;
        }
        
        // Skip header
        $header = fgetcsv($file, 0, ';');
        if (!$header) {
            $this->error("Cannot read header from CSV file");
            fclose($file);
            return 1;
        }
        
        $this->info("Header: " . implode(' | ', $header));
        $this->newLine();
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $uniqueProblems = [];
        
        // First pass: collect unique problems
        $this->info("Step 1: Collecting unique problems...");
        $lineNumber = 1;
        
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $lineNumber++;
            
            if (count($row) < 3) {
                continue;
            }
            
            $group = trim($row[0] ?? '');
            $problemHeader = trim($row[1] ?? '');
            $problemDetail = trim($row[2] ?? '');
            $problemMm = trim($row[3] ?? '');
            
            // Skip if problem detail is empty
            if (empty($problemDetail)) {
                continue;
            }
            
            // Use problem detail as key to ensure uniqueness
            if (!isset($uniqueProblems[$problemDetail])) {
                $uniqueProblems[$problemDetail] = [
                    'group' => $group,
                    'problem_header' => $problemHeader,
                    'name' => $problemDetail,
                    'problem_mm' => $problemMm ?: null,
                ];
            }
        }
        
        rewind($file);
        fgetcsv($file, 0, ';'); // Skip header again
        
        $this->info("Found " . count($uniqueProblems) . " unique problems");
        $this->newLine();
        
        // Second pass: import to database
        $this->info("Step 2: Importing to database...");
        $bar = $this->output->createProgressBar(count($uniqueProblems));
        $bar->start();
        
        foreach ($uniqueProblems as $problemDetail => $data) {
            try {
                Problem::firstOrCreate(
                    ['name' => $data['name']],
                    [
                        'group' => $data['group'] ?: null,
                        'problem_header' => $data['problem_header'] ?: null,
                        'problem_mm' => $data['problem_mm'],
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error importing '{$data['name']}': " . $e->getMessage());
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        fclose($file);
        
        $this->info("Import completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped (duplicates): {$skipped}");
        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }
        
        return 0;
    }
}
