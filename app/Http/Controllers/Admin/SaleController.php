<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use charlieuki\ReceiptPrinter\ReceiptPrinter;

//require 'vendor/autoload.php';
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'sales';
        if($request->ajax()){
            $sales = Sale::latest();
            return DataTables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('product',function($sale){
                        $image = '';
                        if(!empty($sale->product)){
                            $image = null;
                            if(!empty($sale->product->purchase->image)){
                                $image = '<span class="avatar avatar-sm mr-2">
                                <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                </span>';
                            }
                            return $sale->product->purchase->product. ' ' . $image;
                        }                 
                    })
                    ->addColumn('category',function($purchase){
                        if(!empty($purchase->category)){
                            return $purchase->category->name;
                        }
                    })
                    ->addColumn('total_price',function($sale){                   
                        return settings('app_currency','$').' '. $sale->total_price;
                    })
                    ->addColumn('date',function($row){
                        return date_format(date_create($row->created_at),'d M, Y');
                    })
                    ->addColumn('action', function ($row) {
                        $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                        if (!auth()->user()->hasPermissionTo('edit-sale')) {
                            $editbtn = '';
                        }
                        if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['product','action'])
                    ->make(true);

        }
        $categories = Category::get();
        return view('admin.sales.index',compact(
            'title','categories',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::get();
        $categories = Category::get();
        return view('admin.sales.create',compact(
            'title','products','categories'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // get the products data as a JSON string
        $productsJSON = $request->input('products');
        // decode the JSON string into an array
        $products = json_decode($productsJSON, true);




        // loop through the products array and do something with each product
        foreach ($products as $product) {
            $sold_product = Category::find($product['product']);
            dd($this->generateAcronym($sold_product->name));
            $new_quantity = ($sold_product->quantity) - ($product['quantity']);
            $sold_product->update([
                'quantity'=>$new_quantity,
            ]);
            $total = $product['price'] * $product['quantity'];
            Sale::create([
                
                'category_id'=>$product['product'],
                'quantity'=>$product['quantity'],
                'total_price'=>$total,
            ]);
        }
        
        try {

            $connector = new WindowsPrintConnector("POS58 Printer");
            $printer = new Printer($connector);
            
            // Print header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Apotek\n");
            $printer->text("Padmasari\n");
            $printer->text("kd transaksi\n");
        
            // Print transaction details
            $printer->text("\n");
            $printer->text("Transaction ID: {000}\n\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $subtotal = 0;
            foreach ($products as $product) {
                $sold_product = Category::find($product['product']);
                $total = $product['price'] * $product['quantity'];
                #abbr the product name
                $abbreviation = preg_replace('/\b(\w)|./', '$1', $sold_product->name);
                $printer->text("{$abbreviation} \n ({$product['quantity']} x {$product['price']})\n");
                $subtotal = $subtotal + $product['quantity'] * $product['price'];
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            // Print subtotal, tax, and total
            $printer->text("Total: {$subtotal}\n\n");
            
            // Print footer
            $printer->text("Terima Kasih Telah Berbelanja!\n");
        
            // Cut the receipt
            $printer->cut();
        
            // Close the printer connection
            $printer->close();
        } catch (Exception $e) {
            // Handle any exceptions (e.g., printer not found)
            echo "Error: " . $e->getMessage();
        }
        return redirect()->route('sales.index');
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $sold_product = Product::find($request->product);
        /**
         * update quantity of sold item from purchases
        **/
        $purchased_item = Purchase::find($sold_product->purchase->id);
        if(!empty($request->quantity)){
            $new_quantity = ($purchased_item->quantity) - ($request->quantity);
        }
        $new_quantity = $sale->quantity;
        $notification = '';
        if (!($new_quantity < 0)){
            $purchased_item->update([
                'quantity'=>$new_quantity,
            ]);

            /**
             * calcualting item's total price
            **/
            if(!empty($request->quantity)){
                $total_price = ($request->quantity) * ($sold_product->price);
            }
            $total_price = $sale->total_price;
            $sale->update([
                'product_id'=>$request->product,
                'quantity'=>$request->quantity,
                'total_price'=>$total_price,
            ]);

            $notification = notify("Product has been updated");
        } 
        if($new_quantity <=1 && $new_quantity !=0){
            // send notification 
            $product = Purchase::where('quantity', '<=', 1)->first();
            event(new PurchaseOutStock($product));
            // end of notification 
            $notification = notify("Product is running out of stock!!!");
            
        }
        return redirect()->route('sales.index')->with($notification);
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'sales reports';
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.sales.reports',compact(
            'sales','title'
        ));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Sale::findOrFail($request->id)->delete();
    }
    /*
    function ticket_number() {
        do {
            $number = random_int(1000000, 9999999);
        } while (Sale::where("number", "=", $number)->first());
    
        return $number;
    }
    */
    public function generateRandomNumericString($length = 6)
    {
        $characters = '0123456789'; // Digits only

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    function generateAcronym($input)
    {
        $words = explode(' ', $input);
        $result = [];
    
        foreach ($words as $word) {
            // Keep the first 3 letters unchanged
            $prefix = substr($word, 0, 2);
    
            // Remove vowels after the third letter
            $suffix = preg_replace('/[aeiou]/i', '', substr($word, 2));
    
            // Combine the modified parts
            $result[] = $prefix . $suffix;
        }
    
        return implode(' ', $result);
    }

}
