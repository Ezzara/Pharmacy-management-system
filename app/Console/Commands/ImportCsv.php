<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use League\Csv\Reader;

class ImportCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $csvFilePath = storage_path('app/' . $this->argument('path'));
    
        // Check if the file exists
        if (!file_exists($csvFilePath)) {
            $this->error("The file {$csvFilePath} does not exist.");
            return;
        }
    
        // Read the CSV data
        $reader = Reader::createFromPath($csvFilePath);
        $results = $reader->getRecords(); // Fetch all rows
    
        foreach ($results as $row) {
            $medicineName = $row[0]; // Get the first column of the row
    
            // Insert each medicine name into the categories table
            if (!empty($medicineName)) {
                // Log the medicine name
                \Log::info("Inserting medicine: {$medicineName}");
    
                Category::create([
                    'name' => $medicineName,
                ]);
            } else {
                // Handle the case where the medicine name is empty
                $this->error("Empty medicine name encountered");
            }
        }
    
        $this->info('CSV data imported successfully');
    }
}
