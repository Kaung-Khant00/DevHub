<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getAdmins(Request $request)
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', 5);

        $page = $request->query('page', 1);
        $admins = User::latest()
            ->where('role', 'admin')
            ->with('adminProfile')
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'admins' => $admins,
        ]);
    }
    public function createAdmin(Request $request)
    {
        $this->validateAdmin($request,false);

        $adminData = $this->getAdminData($request);
        $adminProfileData = $this->getAdminProfileData($request);

        if ($request->hasFile('officeImage')) {
            $image = $request->file('officeImage');
            $path = $image->store('profile', 'public');
            logger($path);
            $adminProfileData['office_image'] = $path;
        }
        $adminData['role'] = 'ADMIN';
        $adminData['password'] = Hash::make($adminData['password']);
        DB::transaction(function () use ($adminData, $adminProfileData) {
            $admin = User::create($adminData);
            $admin->adminProfile()->create($adminProfileData);
        });
        return response()->json(
            [
                'message' => 'Admin created successfully',
            ],
            201,
        );
    }
    private function validateAdmin(Request $request, $updating = false, $id = -1)
    {
        return $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'password' => ['string', 'min:8', 'max:40', ($updating ? 'nullable' : 'required')],
                'officeImage' => 'required|max:2048|image|mimes:png,jpg,jpeg,webp',
                'phone' => 'nullable|string|max:20',
                'role' => 'nullable|string|max:255',
                'admin_specialty' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'age' => 'nullable|integer|min:0|max:120',
                'gender' => 'nullable|string|in:male,female',
            ],
            [
                'password.min' => 'The password must be at least 8 characters.',
                'email.unique' => 'The email has already been taken.',
                'officeImage.max' => 'The image can not be greater than 2 MB.',
            ],
        );
    }
    private function getAdminData(Request $request)
    {
        return [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role', 'ADMIN'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender'),
        ];
    }
    private function getAdminProfileData(Request $request)
    {
        return [
            'admin_specialty' => $request->input('admin_specialty'),
            'address' => $request->input('address'),
        ];
    }
    public function getAdminById($id)
    {
        $admin = User::where('role', 'admin')->with('adminProfile')->findOrFail($id);
        return response()->json([
            'admin' => $admin,
        ]);
    }
    public function updateAdminById(Request $request, $id)
    {
        $this->validateAdmin($request, true, $id);

        $admin = User::where('role', 'ADMIN')->with('adminProfile')->findOrFail($id);
        $adminData = $this->getAdminData($request);
        $adminProfileData = $this->getAdminProfileData($request);

        if ($request->hasFile('officeImage')) {
            $image = $request->file('officeImage');
            $path = $image->store('profile', 'public');
            if (Storage::disk('public')->exists( $admin->adminProfile["office_image"])) {
                Storage::disk('public')->delete($admin->adminProfile['office_image']);
            }
            $adminProfileData['office_image'] = $path;
        }
        if ($request->filled('password')) {
            $adminData['password'] = Hash::make($adminData['password']);
        } else {
            unset($adminData['password']);
        }
        DB::transaction(function () use ($admin, $adminData, $adminProfileData) {
            $admin->update($adminData);
            $admin->adminProfile()->update($adminProfileData);
        });
        return response()->json(
            [
                'message' => 'Admin updated successfully',
            ],
            200,
        );
    }
}
