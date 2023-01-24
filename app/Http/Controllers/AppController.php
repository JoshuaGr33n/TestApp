<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect, Response;
use App\Models\AppTest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $product = AppTest::orderBy('id', 'desc')->get();

        $total_quantity = 0;
        $total_price = 0;
        foreach ($product as $key => $d) {

            $total_quantity += $d['quantity'];
            $total_price += $d['price'];
        }
        $total_value_numbers_sum = $total_quantity + $total_price;
        return view('index', ['data' => $product, 'total_value_numbers_sum' => $total_value_numbers_sum]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        try {

            $request->validate([
                'product_name' => 'required|unique:app_tests',
                'quantity' => 'required|numeric|gt:0',
                'price' => 'required|numeric|gt:0',

            ]);



            // json file location baseURL/storage/app/file.json.
            $file = Storage::disk('local')->exists('file.json') ? json_decode(Storage::disk('local')->get('file.json')) : [];

            $data = $request->only(['product_name', 'quantity', 'price']);

            $data['total_value_number'] = $request->quantity * $request->price;
            $data['datetime_submitted'] = date('Y-m-d H:i:s');
            array_push($file, $data);
            Storage::disk('local')->put('file.json', json_encode($file));


            $db['product_name'] = $request->product_name;
            $db['quantity'] = $request->quantity;
            $db['price'] = $request->price;
            $db['created_at'] = date('Y-m-d H:i:s');
            AppTest::insert($db);

            return $data;
        } catch (Exception $e) {

            return ['error' => true, 'message' => $e->getMessage()];
        }
    }


    public function updateProduct(Request $request)
    {
        $updateProduct = AppTest::find($request->Uid);

        $request->validate([
            'edit_product_name' => 'required|unique:app_tests,product_name,' . $request->Uid . ',id',
            'edit_quantity' => 'required|numeric|gt:0',
            'edit_price' => 'required|numeric|gt:0',
        ]);

        $updateProduct->product_name = $request->edit_product_name;
        $updateProduct->quantity = $request->edit_quantity;
        $updateProduct->price = $request->edit_price;

        $updateProduct->save();

        if ($updateProduct->save()) {
            return response()->json(['success' => 'Sent']);
        }
    }

    public function deleteProduct($id)
    {
        $task = AppTest::find($id);
        if ($task) {
            $task->delete();
            return response()->json(['res' => 1]);
        }
        return "Item Not Found.";
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // 

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
