<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'categories';
        if($request->ajax()){
            $categories = Category::get();
            return DataTables::of($categories)
                    ->addIndexColumn()
                    ->addColumn('price',function($category){  
                        $price = number_format($category->price,0,'.',',');            
                        return settings('app_currency','$').' '. $price;
                    })
                    ->addcolumn('quantity',function($category){
                    //    $quantity = $category->quantity + $category->unit;
                        $quantity = number_format($category->quantity,0);
                        return $quantity.' '.$category->unit;
                    })
                    ->addcolumn('expiry_date',function($category){
                        $exp = $category->expiry_date;
                        if (!$category->expiry_date)
                            $exp = "-";
                        else
                            $exp = date_format(date_create($exp),"d M,Y");
                        return $exp;
                    })
                    ->addColumn('created_at',function($category){
                        return date_format(date_create($category->created_at),"d M,Y");
                    })
                    ->addColumn('action',function ($row){
                        $editbtn = '<a data-id="'.$row->id.'" data-name="'.$row->name.'" data-producer="'.$row->producer.'" data-type="'.$row->type.'" data-price="'.$row->price.'" data-unit="'.$row->unit.'" data-quantity="'.$row->quantity.
                            '" data-expiry_date="'.$row->expiry_date.'" href="javascript:void(0)" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('categories.destroy',$row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                        if(!auth()->user()->hasPermissionTo('edit-category')){
                            $editbtn = '';
                        }
                        if(!auth()->user()->hasPermissionTo('destroy-category')){
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('admin.products.categories',compact(
            'title'
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
        $this->validate($request,[
            'name'=>'required|max:100',
            'price'=>'required|min:1',
            'unit'=>'required',
            
        ]);
        //Category::create($request->all());
        Category::create([
            'name'=>$request->name,
            'price'=>$request->price,
            'unit'=>$request->unit,
        ]);
        $notification=array("Category has been added");
        return back()->with($notification);
    }

    

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request,[
            'name'=>'required|max:100',
            'price'=>'required',
            'quantity'=>'required',
            'unit'=>'required',
            'expiry_date'=>'required',
        ]);
        //dd($request->producer);
        $category = Category::find($request->id);
        $category->update([
            'name'=>$request->name,
            'price'=>$request->price,
            'quantity'=>$request->quantity,
            'unit'=>$request->unit,
            'expiry_date'=>$request->expiry_date,
        ]);
        $notification = notify("Category has been updated");
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Category::findOrFail($request->id)->delete();
    }
}
