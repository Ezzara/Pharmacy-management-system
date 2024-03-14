<?php

namespace App\Http\Controllers;
use App\Models\Category;
use League\Csv\Reader;

use Illuminate\Http\Request;

class ListController extends Controller
{
    //
    public function store(Request $request)
    {
        $input = $request->input();
        $client_id = $request->input('client_id');

        if ($request->hasFile('name')) {
            $file = $request->file('name');
            $name = time() . '-' . $file->getClientOriginalName();
            $path = storage_path('documents');

            // Insert into the lists table
            Lists::create(['client_id' => $client_id, 'name' => $name]);

            // Read CSV data and insert into customers table
            $reader = Reader::createFromPath($file->getRealPath());
            $headers = [];
            foreach ($reader as $index => $row) {
                if ($index === 0) {
                    $headers = $row;
                } else {
                    $data = array_combine($headers, $row);
                    Customers::create($data);
                }
            }

            $file->move($path, $name);

            // You can add any additional logic or redirection here
            // For example:
            return response()->json(['message' => 'CSV data imported successfully']);
        }
    }
}
