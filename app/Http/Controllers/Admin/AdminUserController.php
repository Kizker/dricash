<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $status = $request->input('status');

        $usersQuery = User::query()
            ->withCount(['transactions', 'monthlyObligations', 'growthTargets'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role && $role !== 'all', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'suspended') {
                    $query->where('is_active', false);
                }
            })
            ->orderByDesc('created_at');

        $users = $usersQuery->paginate(12)->withQueryString()->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
                'is_active' => (bool) $user->is_active,
                'currency' => $user->currency ?? 'IDR',
                'monthly_start_day' => $user->monthly_start_day,
                'initial_net_worth' => (float) $user->initial_net_worth,
                'avatar_url' => $user->avatar_url,
                'transactions_count' => $user->transactions_count,
                'obligations_count' => $user->monthly_obligations_count,
                'growth_targets_count' => $user->growth_targets_count,
                'created_at' => $user->created_at->format('d M Y H:i'),
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search ?? '',
                'role' => $role ?? 'all',
                'status' => $status ?? 'all',
            ],
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'regular_users' => User::where('role', 'user')->count(),
                'suspended' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusLabel = $user->is_active ? 'diaktifkan kembali' : 'ditangguhkan (nonaktif)';

        return back()->with('success', "Status akun pengguna {$user->name} berhasil {$statusLabel}.");
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return back()->with('success', "Role pengguna {$user->name} berhasil diperbarui menjadi {$user->role}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', "Kata sandi untuk pengguna {$user->name} berhasil direset.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "Akun pengguna {$userName} beserta data terkait berhasil dihapus.");
    }
}
