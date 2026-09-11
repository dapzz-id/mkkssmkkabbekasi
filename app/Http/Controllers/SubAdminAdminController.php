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
        $isDivisiUuid = preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', (string) $request->input('divisi'));
        $divisiRule = $isDivisiUuid ? 'required|exists:divisi,uuid' : 'required|exists:divisi,id';

        // Validate form data
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:user',
            'email' => 'required|string|email|max:255|unique:user',
            'divisi' => $divisiRule,
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

        $divisi = Divisi::whereIdentifier($request->divisi)->first();
        $divisiUuid = $divisi ? $divisi->uuid : null;

        // Create the new Sub Admin
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'divisi_uuid' => $divisiUuid,
            'alamat' => $request->alamat,
            'password' => Hash::make($request->password), // Hash password before storing
        ]);

        // Redirect to Sub Admin list with success message
        return redirect('/manage/user')->with('success', 'Sub Admin berhasil ditambahkan.');
    }

    public function show($id) {
        $user = User::with('divisi')->whereIdentifier($id)->firstOrFail();
        return view('admin.subadmin.show', compact('user'));
    }

    public function edit($id) {
        $subAdmin = User::findByIdentifierOrFail($id);
        $divisi = Divisi::all();

        return view('admin.subadmin.editsubadmin', [
            "subAdmin" => $subAdmin,
            "divisi" => $divisi
        ]);
    }

    public function update(Request $request, $id) {
        // Validate form data
        $subAdmin = User::findByIdentifierOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('user', 'username')->ignore($subAdmin->uuid, 'uuid')],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('user', 'email')->ignore($subAdmin->uuid, 'uuid')],
            'divisi' => 'required',
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
            'role.required' => 'Pilih role',
            'password.min' => 'Password minimal 8 karakter',
            'alamat.required' => 'Masukkan alamat',
            'validation.same' => 'Password tidak sama dengan yang atas'
        ]);

        $divisi = Divisi::whereIdentifier($request->divisi)->first();

        // Update Sub Admin details
        $subAdmin->name = $request->name;
        $subAdmin->username = $request->username;
        $subAdmin->email = $request->email;
        $subAdmin->divisi_uuid = $divisi ? $divisi->uuid : null;
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
        $user = User::whereIdentifier($id)->first();

        if($user){
            $user->delete();
            return response()->json(['status' => 'success']);
        }else{
            return response()->json(['status' => 'error']);
        }
    }
}
