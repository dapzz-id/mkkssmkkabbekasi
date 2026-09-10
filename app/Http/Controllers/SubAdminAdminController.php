<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SubAdminAdminController extends Controller
{
    public function index() {
        $data = User::where('role', 'admin')->latest()->paginate(5);
        // dd($data);
        return view('admin.subadmin.datasubadmin', [
            "data" => $data
        ]);
    }

    public function create() {
        $divisi = Divisi::all();
        return view('admin.subadmin.tambahsubadmin', [
            "divisi" => $divisi
        ]);
    }

    public function store(Request $request)
    {
        // Validate form data
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:user',
            'email' => 'required|string|email|max:255|unique:user',
            'divisi' => 'required|exists:divisi,id',
            'role' => 'required|string|in:admin,superadmin',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
            'alamat' => 'required|string',
        ], [
            'name.required' => 'Masukkan nama',
            'username.required' => 'Masukkan username',
            'username.unique' => 'Username sudah terdaftar',
            'email.required' => 'Masukkan email',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'divisi.required' => 'Pilih divisi',
            'divisi.exists' => 'Divisi tidak valid',
            'role.required' => 'Pilih role',
            'password.required' => 'Masukkan password',
            'password.min' => 'Password minimal 8 karakter',
            'alamat.required' => 'Masukkan alamat',
            'validation.same' => 'Password tidak sama dengan yang atas',
        ]);


        // Create the new Sub Admin
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'id_divisi' => $request->divisi,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password), // Hash password before storing
        ]);

        // Redirect to Sub Admin list with success message
        return redirect('/manage/user')->with('success', 'Sub Admin berhasil ditambahkan.');
    }

    public function show($id) {
        $user = User::with('divisi')->findOrFail($id);
        return view('admin.subadmin.show', compact('user'));
    }

    public function edit($id) {
        $subAdmin = User::findOrFail($id);
        if(!$subAdmin) {
            return redirect()->back();
        }
        $divisi = Divisi::all();

        return view('admin.subadmin.editsubadmin', [
            "subAdmin" => $subAdmin,
            "divisi" => $divisi
        ]);
    }

    public function update(Request $request, $id) {
        // Validate form data
        $subAdmin = User::find($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:user,username,' . $subAdmin->id, // Ignore current username
            'email' => 'required|string|email|max:255|unique:user,email,' . $subAdmin->id, // Ignore current email
            'divisi' => 'required|exists:divisi,id',
            'password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|string|same:password',
            'role' => 'required|string|in:admin,superadmin',
            'alamat' => 'required|string',
        ], [
            'name.required' => 'Masukkan nama',
            'username.required' => 'Masukkan username',
            'username.unique' => 'Username sudah terdaftar',
            'email.required' => 'Masukkan email',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'divisi.required' => 'Pilih divisi',
            'divisi.exists' => 'Divisi tidak valid',
            'role.required' => 'Pilih role',
            'password.min' => 'Password minimal 8 karakter',
            'alamat.required' => 'Masukkan alamat',
            'validation.same' => 'Password tidak sama dengan yang atas'
        ]);

        // Update Sub Admin details
        $subAdmin->name = $request->name;
        $subAdmin->username = $request->username;
        $subAdmin->email = $request->email;
        $subAdmin->id_divisi = $request->divisi;
        $subAdmin->role = $request->role;
        $subAdmin->alamat = $request->alamat;

        // Update password if provided
        if ($request->password) {
            $subAdmin->password = Hash::make($request->password);
        }

        $subAdmin->save();

        // Redirect to Sub Admin list with success message
        return redirect('/manage/user')->with('success', 'Sub Admin berhasil diperbarui.');
    }

    public function destroy($id) {
        // $user = User::find($id);

        $user = User::where('id', $id)->first();

        if($user){
            $user->delete();
            return response()->json(['status' => 'success']);
        }else{
            return response()->json(['status' => 'error']);
        }
    }
}
