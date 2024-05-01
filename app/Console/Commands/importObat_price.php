<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class importObat_price extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:medicines_price {file}';

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
        $filePath = $this->argument('file');
        $csvData = array_map('str_getcsv', file($filePath));

        foreach ($csvData as $row) {
            $medicineName = $row[0];
            $price = (int) $row[1];

            Category::create([
                'name' => $medicineName,
                'price' => $price,
            ]);
        }

        $this->info('Medicine data imported successfully.'.$medicineName.$price);
    }
}
