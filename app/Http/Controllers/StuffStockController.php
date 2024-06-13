<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class StuffStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $getStuffStock = StuffStock::with('stuff')->get();

            return ApiFormatter::sendResponse(200, 'Successfully Get All Stuff Stock Data', $getStuffStock);
        }catch(\Exception $e){
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function show(StuffStock $stuffStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function edit(StuffStock $stuffStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StuffStock $stuffStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StuffStock  $stuffStock
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = StuffStock::where('id', $id)->delete();

            return ApiFormatter::sendResponse(200,  'Data Stuff berhasil dihapus');
        }catch (\Exception $err) {
            return ApiFormatter::sendResponse(400,  $err->getMessage());
        }
    }

    public function deletePermanent($id){
        try{
            $checkProses = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200,  'Berhasil menghapus data permanen dari stuff');
        }catch (\Exception $err){
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    public function addStock(Request $request, $id)
    {
        try {

            $getStuffStock = StuffStock::find($id);

            if(!$getStuffStock) {
                return ApiFormatter::sendResponse(404, false, 'Data Stuff not found');
            }else{
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required',
                ]);

                $addStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_available,
                    'total_defec' => $getStuffStock['total_defec'] + $request->total_defec,
                ]);

                if ($addStock) {
                    $getStockAdded = StuffStock::where('id', $id)->with('stuff')->first();

                    return ApiFormatter::sendResponse(200, 'Succesfiully add stock of stuff stock data', $getStockAdded);
                }
            }
        }
    }

    public function subStock(Request $request, $id)
    {
        try {
             $getStuffStock = StuffStock::find($id);

             if (!$getStuffStock) {
                return ApiFormatter::sendResponse(400, false, 'Data Stuff Stock Not Found');
             } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defac' => 'required',
                ]);

                $isStockAvailable = $getStuffStock->update['total_available'] - $request->total_available;
                $isStockDefac = $getStuffStock->update['total_defac'] - $request->total_defac;

                if ($isStockAvailable < 0 || $isStockDefac < 0) {
                    return ApiFormatter::sendResponse(400, true, 'Substraction Stock Cant Less Than A Stock Stored');
                } else {
                    $subStock = $getStuffStock->update([
                        'total_available' => $isStockAvailable,
                        'total_defac' => $isStockDefac,
                    ]);

                    if ($subStock) {
                        $getStockSub = StuffStock::where('id', $id)->with('stuff')->first();

                        return ApiFormatter::sendResponse(200, true, 'Succesfully Sub A Stock Of StuFf Stock Data', $getStockSub);
                    }
                }
             }
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    public function __construct()
{
    $this->middleware('auth:api');
}
}

