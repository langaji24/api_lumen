<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        try {
            // ambil data yang mau ditampilkan
            $data = User::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
        try {
            // validasi
            $this->validate($request, [
                'username' => 'required',
                'email' => 'required|email|min:3',
                'password' => 'required',
                'role' => 'required|in:admin,staff'
            ]);

            // proses tambah data
            // NamaModel::create(['column' => $request->name_or_key, ])
            $data = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
            $data = User::where('id', $id)->first();

            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'success', $data);
            } 
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
            $this->validate($request, [
                'username' => 'required',
                'email' => 'required|email|min:3',
                'role' => 'required|in:admin, staff'
            ]);

            if ($request->password) {
                    $checkProses = User::where('id', $id)->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'role' => $request->role,
                    'password' => Hash::make($request->password),
                ]);
            } else {
                $checkProses = User::where('id', $id)->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'role' => $request->role,
                ]);
            }

            if ($checkProses) {
                $data = User::find($id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengubah data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkProses = User::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                $data = User::find(id);
                return ApiFormatter::sendResponse(200, 'success', $data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent($id) 
    {
        try {
            $checkProses = User::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanen data stuff!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first(); // Mencari dan mendapatkan data user berdasarkan email yang digunakan

            if (!$user) {
                // Jika email tidak terdaftar maka akan dikembalikan response error
                return ApiFormatter::sendResponse(400, false, 'Login Failed! User Doesnt Exists');
            } else {
                // Jika email terdaftar, selanjutnya pencocokan password yang diinput dengan password di db dengan menggunakan Hash::check()
                $isValid = Hash::check($request->password, $user->password);

                if (!$isValid) {
                    // Jika password tidak cocok maka akan mendapatkan pesan error
                    return ApiFormatter::sendResponse(400, false, 'Login Failed! Password Doesnt Match');
                } else {
                    $generateToken = bin2hex(random_bytes(40));
                    
                    $user->update([
                        'token' => $generateToken
                    ]);

                    return ApiFormatter::sendResponse(200, 'Login Succesfully', $user);
                }
            }
        } catch (\Throwable $th) {
  
        }

        
    }

    public function logout(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
            ]);

            $user = User::where('email', $request->email)->first(); // Mencari dan mendapatkan data user berdasarkan email yang digunakan

            if (!$user) {
                // Jika email tidak terdaftar maka akan dikembalikan response error
                return ApiFormatter::sendResponse(400, false, 'Login Failed! User Doesnt Exists');
            } else {
                if (!$user->token) {
                    return ApiFormatter::sendResponse(400, 'logout failed! user doesnt login science ');
                }else {
                    $logout = $user->update(['token' => null]);

                    if ($logout) {
                        return ApiFormatter::sendResponse(200, 'Logout Succesfully');
                    }
                }
            }
        } catch (\Throwable $th) {
            
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
}