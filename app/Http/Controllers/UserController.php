<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display My Profile page
     */
    public function profile()
    {
        $user = auth()->user();
        return view('settings.profile', compact('user'));
    }

    /**
     * Update current user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'كلمة المرور الحالية غير صحيحة');
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->save();

        ActivityLog::log('profile_updated', "User {$user->username} updated their profile");

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    /**
     * Display User Management page (manager only)
     */
    public function index()
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $users = User::withTrashed()->orderBy('created_at', 'desc')->get();
        return view('settings.users.index', compact('users'));
    }

    /**
     * Show form to create new user
     */
    public function create()
    {
        if (auth()->user()->role !== 'manager') abort(403);

        return view('settings.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:cashier,manager',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLog::log('user_created', "Created user: {$user->username} ({$user->role})");

        return redirect()->route('settings.users.index')
            ->with('success', "تم إنشاء المستخدم {$user->name} بنجاح");
    }

    /**
     * Show form to edit user
     */
    public function edit(User $user)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        return view('settings.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:cashier,manager',
            'is_active' => 'boolean',
        ]);

        $oldRole = $user->role;
        $oldStatus = $user->is_active;

        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->is_active = $request->has('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Log changes
        $changes = [];
        if ($oldRole !== $user->role) $changes[] = "role: {$oldRole} → {$user->role}";
        if ($oldStatus !== $user->is_active) $changes[] = "status: " . ($oldStatus ? 'active' : 'inactive') . " → " . ($user->is_active ? 'active' : 'inactive');

        ActivityLog::log('user_updated', "Updated user: {$user->username}" . (empty($changes) ? '' : ' (' . implode(', ', $changes) . ')'));

        return redirect()->route('settings.users.index')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        // Prevent user from deactivating themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تغيير حالة حسابك');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('user_toggled', "User {$user->username} {$status}");

        return back()->with('success', $user->is_active ? 'تم تفعيل المستخدم' : 'تم تعطيل المستخدم');
    }

    /**
     * Soft delete user
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        // Prevent user from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك');
        }

        $user->delete();

        ActivityLog::log('user_deleted', "Deleted user: {$user->username}");

        return back()->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * Restore deleted user
     */
    public function restore($id)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        ActivityLog::log('user_restored', "Restored user: {$user->username}");

        return back()->with('success', 'تم استعادة المستخدم بنجاح');
    }

    /**
     * Permanently delete user
     */
    public function forceDelete($id)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $user = User::withTrashed()->findOrFail($id);

        // Prevent user from force deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك نهائياً');
        }

        $username = $user->username;
        $user->forceDelete();

        ActivityLog::log('user_force_deleted', "Permanently deleted user: {$username}");

        return back()->with('success', 'تم حذف المستخدم نهائياً');
    }
}
