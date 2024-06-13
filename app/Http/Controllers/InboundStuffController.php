<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\InboundStuff;
use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class InboundStuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $getInboundStuff = InboundStuff::with('stuff')->get();

            return ApiFormatter::sendResponse(200, 'Successfully Get All Inbound Stuff Data', $getInboundStuff);
        } catch (\Exception $e) {
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
    public function store(Request $request) // proof file disesuaikan
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);
            
            $checkStuff = Stuff::where('id', $request->stuff_id)->first();

            if(!$checkStuff){
                return ApiFormatter::sendResponse(400, 'Data Stuff Does Not Exist');
            }else {
                if ($request->hasFile('proff_file')) { // ngecek ada file apa engga
                    $proof = $request->file('proff_file'); // get filenya
                    $destinationPath = 'proof/'; // sub path di folder public
                    //20240308102130
                    $proofName = date('YmdHis') . "." . $proof->getClientOriginalExtension(); // modifikasi nama file, tahunbulantanggaljammenitdetik.extension
                    $proof->move($destinationPath, $proofName); // file yang sudah di get diatas dipindahkan ke folder public/proof dengan nama sesaui yang di variabel proofname
                }
    
                $createStock = InboundStuff::create([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $proofName,
                ]);
    
                if ($createStock) {
                    $getStuff = Stuff::where('id', $request->stuff_id)->first();
                    $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
    
                    if (!$getStuffStock) {
                        $updateStock = StuffStock::create([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $request->total,
                            'total_defac' => 0,
                        ]);
                    } else {
                        $updateStock = $getStuffStock->update([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $getStuffStock['total_available'] + $request->total,
                            'total_defac' => $getStuffStock['total_defac'],
                        ]);
                    }
    
                    if ($updateStock) {
                        $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                        $stuff = [
                            'stuff' => $getStuff,
                            'inboundStuff' => $createStock,
                            'stuffStock' => $getStock,
                        ];
    
                        return ApiFormatter::sendResponse(200, 'Successfully Create A Inbound Stuff Data', $stuff);
                    } else {
                        return ApiFormatter::sendResponse(400, 'Failed To Update A Stuff Stock Data');
                    }
                } else {
                    return ApiFormatter::sendResponse(400, 'Failed To Create A Inbound Stuff Data');
                }
            }

            // dd($request->all());

            
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $getInboundStuff = InboundStuff::with('stuff')->find($id);

            if (!$getInboundStuff) {
                return ApiFormatter::sendResponse(404, 'Data Inbound Stuff Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get A Inbound Stuff Data', $getInboundStuff);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
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
        try {
            // Get daya inbound yang mau di update
            $getInboundStuff = InboundStuff::find($id); // find => mencari sesuai pk

            if (!$getInboundStuff) { // kalau inbound ga ada
                return ApiFormatter::sendResponse(404, false, 'Data Inbound Stuff Not Found');
            } else { // data inbound data

                $this->validate($request, [
                    'stuff_id' => 'required',
                    'total' => 'required',
                    'date' => 'required',
                ]);

                if ($request->hasFile('proff_file')) { // ini jika ada request proff_file
                    $proof = $request->file('proff_file');
                    $destinationPath = 'proof/';
                    $proofName = date('YmdHis') . "." . $proof->getClientOriginalExtension();
                    $proof->move($destinationPath, $proofName);

                    unlink(base_path('public/proof/' . $getInboundStuff['proff_file']));
                } else { // kalau ga ada pake data dari get inbound di awal
                    $proofName = $getInboundStuff['proff_file'];
                }

                // get data stuff berdasarkan stuff id di variabel awal
                $getStuff = Stuff::where('id', $getInboundStuff['stuff_id'])->first();

                // get data stuff stock berdasarkan stuff id di variabel awal
                $getStuffStock = StuffStock::where('stuff_id', $getInboundStuff['stuff_id'])->first(); // stuff_id request tidak berubah

                $getCurrentStock = StuffStock::where('stuff_id', $request['stuff_id'])->first(); // stuff_id request berubah

                if ($getStuffStock['stuff_id'] == $request['stuff_id']) {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - $getInboundStuff['total'] + $request->total,
                    ]); // update data yang stuff_id tidak berubah dengan merubah total available dikurangi total data lama di tamabah total data baru
                } else {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - $getInboundStuff['total'],
                    ]); // update data yang stuff _id tidak berubah dengan mengurangi total available dangand data yang lama

                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getStuffStock['total_available']  + $request->total,
                    ]); // update data stuff id yang berubah dengan menjumlahkan total available dengan total yang baru
                }

                $updateInbound = $getInboundStuff->update([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $proofName,
                ]);

                $getStock = StuffStock::where('stuff_id', $request['stuff_id'])->first();
                $getInbound = InboundStuff::find($id)->with('stuff', 'stuffStock');
                $getCurrentStuff = Stuff::where('id', $request['stuff_id'])->first();

                $stuff = [
                    'stuff' => $getCurrentStuff,
                    'inboundStuff' => $getInbound,
                    'stuffStock' => $getStock,
                ];

                return ApiFormatter::sendResponse(200, 'Successfully Update A Inbound Stuff Data', $stuff);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = InboundStuff::where('id', $id)->first();
    
            if ($checkProses) {
                $dataStock = StuffStock::where('stuff_id', $checkProses->stuff_id)->first();
                if ($dataStock->total_available < $checkProses->total) {
                    return ApiFormatter::sendResponse(400, 'bad request', 'Total Available Kurang Dari Total Dipinjam');
                } else {
                    $stuffId = $checkProses->stuff_id;
                    $totalInbound = $checkProses->total;
                    $checkProses->delete();

                    if ($dataStock) {
                        $total_available = (int)$dataStock->total_available - (int)$totalInbound;
                        $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
        
                        if ($minusTotalStock) {
                            $updateStuffAndInbound = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStocks')->first();
                            return ApiFormatter::sendResponse(200, 'success', $updateStuffAndInbound);
                        }
                    } else {
                        // Tangani jika data stok tidak ditemukan
                        return ApiFormatter::sendResponse(404, 'not found', 'Data Stock Stuff tidak ditemukan');
                    }
                }
            } else {
                // Tangani jika data InboundStuff tidak ditemukan
                return ApiFormatter::sendResponse(404, 'not found', 'Data InboundStuff tidak ditemukan');
            }
        } catch (\Exception $err) {
            // Tangani kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function recycleBin()
    {
        try {

            $inboundStuffDeleted = InboundStuff::onlyTrashed()->get();

            if (!$inboundStuffDeleted) {
                return ApiFormatter::sendResponse(404, 'Deletd Data Inbound Stuff Doesnt Exists');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get Delete All Inbound Stuff Data', $inboundStuffDeleted);
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {

            $getInboundStuff = InboundStuff::onlyTrashed()->where('id', $id);

            if (!$getInboundStuff) {
                return ApiFormatter::sendResponse(404, false, 'Restored Data Inbound Stuff Doesnt Exists');
            } else {
                $restoreStuff = $getInboundStuff->restore();

                if ($restoreStuff) {
                    $getRestore = InboundStuff::find($id);
                    $addStock = StuffStock::where('stuff_id', $getRestore['stuff_id'])->first();
                    $updateStock = $addStock->update([
                        'total_available' => $addStock['total_available'] + $getRestore['total'],
                    ]);

                    return ApiFormatter::sendResponse(200, 'Successfully Restore A Deleted Inbound Stuff Data', $getRestore);
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function forceDestroy($id)
    {
        try {

            $getInboundStuff = InboundStuff::onlyTrashed()->where('id', $id)->first();

            if (!$getInboundStuff) {
                return ApiFormatter::sendResponse(404, false, 'Data Inbound Stuff for Permanent Delete Doesnt Exists');
            } else {
                unlink(base_path('public/proof/' . $getInboundStuff['proff_file']));
                $forceStuff = $getInboundStuff->forceDelete();

                if ($forceStuff) {
                    return ApiFormatter::sendResponse(200, true, 'Successfully Permanent Delete A Inbound Stuff Data');
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
        }
    }


    
}