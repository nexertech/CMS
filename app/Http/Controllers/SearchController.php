<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\SpareApprovalPerforma;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $results = [];
        $totalResults = 0;

        if (!empty($query)) {
            $results = $this->performSearch($query);
            $totalResults = collect($results)->sum('count');
        }

        return view('admin.search.index', compact('query', 'results', 'totalResults'));
    }

    public function api(Request $request)
    {
        $query = $request->get('q', '');
        $results = [];

        if (!empty($query) && strlen($query) >= 2) {
            $results = $this->performQuickSearch($query);
        }

        return response()->json([
            'query' => $query,
            'results' => $results,
            'total' => count($results)
        ]);
    }

    private function performSearch($query)
    {
        $results = [];

        // Search Complaints
        $complaints = Complaint::with(['client', 'assignedEmployee'])
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('status', 'like', "%{$query}%")
                  ->orWhere('priority', 'like', "%{$query}%");
            })
            ->orWhereHas('client', function($q) use ($query) {
                $q->where('client_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orWhereHas('assignedEmployee', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        if ($complaints->count() > 0) {
            $results[] = [
                'type' => 'complaints',
                'title' => 'Complaints',
                'icon' => 'alert-circle',
                'color' => 'primary',
                'count' => $complaints->count(),
                'items' => $complaints->map(function($complaint) {
                    return [
                        'id' => $complaint->id,
                        'title' => $complaint->getTicketNumberAttribute(),
                        'subtitle' => $complaint->client ? $complaint->client->client_name : 'Deleted Client',
                        'description' => $complaint->description,
                        'status' => $complaint->getStatusDisplayAttribute(),
                        'priority' => $complaint->getPriorityDisplayAttribute(),
                        'url' => route('admin.complaints.show', $complaint->id),
                        'created_at' => $complaint->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        // Search Users
        $users = User::with('role')
            ->where(function($q) use ($query) {
                $q->where('username', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orWhereHas('role', function($q) use ($query) {
                $q->where('role_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        if ($users->count() > 0) {
            $results[] = [
                'type' => 'users',
                'title' => 'Users',
                'icon' => 'users',
                'color' => 'success',
                'count' => $users->count(),
                'items' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'title' => $user->username,
                        'subtitle' => $user->email,
                        'description' => $user->role->role_name ?? 'No Role',
                        'status' => $user->status === 'active' ? 'Active' : 'Inactive',
                        'url' => route('admin.users.show', $user->id),
                        'created_at' => $user->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        // Search Clients
        $clients = Client::where(function($q) use ($query) {
                $q->where('client_name', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('address', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        if ($clients->count() > 0) {
            $results[] = [
                'type' => 'clients',
                'title' => 'Clients',
                'icon' => 'briefcase',
                'color' => 'info',
                'count' => $clients->count(),
                'items' => $clients->map(function($client) {
                    return [
                        'id' => $client->id,
                        'title' => $client->client_name,
                        'subtitle' => $client->contact_person,
                        'description' => $client->email,
                        'status' => $client->is_active ? 'Active' : 'Inactive',
                        'url' => route('admin.clients.show', $client->id),
                        'created_at' => $client->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        // Search Employees
        $employees = Employee::query()
            ->where(function($q) use ($query) {
                $q->where('designation', 'like', "%{$query}%");
            })
            ->orWhere('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        if ($employees->count() > 0) {
            $results[] = [
                'type' => 'employees',
                'title' => 'Employees',
                'icon' => 'user-check',
                'color' => 'warning',
                'count' => $employees->count(),
                'items' => $employees->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'title' => $employee->name ?? 'Unknown',
                        'subtitle' => $employee->designation,
                        'description' => '',
                        'status' => ($employee->status === 'active') ? 'Active' : 'Inactive',
                        'url' => route('admin.employees.show', $employee->id),
                        'created_at' => $employee->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        // Search Spare Parts
        $spares = Spare::where(function($q) use ($query) {
                $q->where('item_name', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('supplier', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        if ($spares->count() > 0) {
            $results[] = [
                'type' => 'spares',
                'title' => 'Spare Parts',
                'icon' => 'package',
                'color' => 'secondary',
                'count' => $spares->count(),
                'items' => $spares->map(function($spare) {
                    return [
                        'id' => $spare->id,
                        'title' => $spare->item_name,
                        'subtitle' => $spare->supplier ?? 'No Supplier',
                        'description' => $spare->category,
                        'status' => $spare->stock_quantity > 0 ? 'In Stock' : 'Out of Stock',
                        'url' => route('admin.spares.show', $spare->id),
                        'created_at' => $spare->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        // Search Approvals
        $approvals = SpareApprovalPerforma::with(['complaint', 'requestedBy'])
            ->where(function($q) use ($query) {
                $q->where('status', 'like', "%{$query}%");
            })
            ->orWhereHas('complaint', function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orWhereHas('requestedBy', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        if ($approvals->count() > 0) {
            $results[] = [
                'type' => 'approvals',
                'title' => 'Approvals',
                'icon' => 'check-circle',
                'color' => 'primary',
                'count' => $approvals->count(),
                'items' => $approvals->map(function($approval) {
                    return [
                        'id' => $approval->id,
                        'title' => 'Approval #' . $approval->id,
                        'subtitle' => $approval->complaint->getTicketNumberAttribute() ?? 'Unknown',
                        'description' => $approval->requestedBy->name ?? 'Unknown',
                        'status' => ucfirst($approval->status),
                        'url' => route('admin.approvals.show', $approval->id),
                        'created_at' => $approval->created_at->format('M d, Y')
                    ];
                })
            ];
        }

        return $results;
    }

    private function performQuickSearch($query)
    {
        $results = [];

        // Quick search for complaints
        $complaints = Complaint::with('client')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($complaints as $complaint) {
            $results[] = [
                'type' => 'complaint',
                'title' => $complaint->getTicketNumberAttribute(),
                'subtitle' => $complaint->client ? $complaint->client->client_name : 'Deleted Client',
                'url' => route('admin.complaints.show', $complaint->id),
                'icon' => 'alert-circle',
                'color' => 'primary'
            ];
        }

        // Quick search for users
        $users = User::where('username', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'title' => $user->username,
                'subtitle' => $user->email,
                'url' => route('admin.users.show', $user->id),
                'icon' => 'user',
                'color' => 'success'
            ];
        }

        // Quick search for clients
        $clients = Client::where('client_name', 'like', "%{$query}%")
            ->orWhere('contact_person', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($clients as $client) {
            $results[] = [
                'type' => 'client',
                'title' => $client->client_name,
                'subtitle' => $client->contact_person,
                'url' => route('admin.clients.show', $client->id),
                'icon' => 'briefcase',
                'color' => 'info'
            ];
        }

        // Quick search for spare parts
        $spares = Spare::where('item_name', 'like', "%{$query}%")
            ->orWhere('supplier', 'like', "%{$query}%")
            ->limit(3)
            ->get();

        foreach ($spares as $spare) {
            $results[] = [
                'type' => 'spare',
                'title' => $spare->item_name,
                'subtitle' => $spare->supplier ?? 'No Supplier',
                'url' => route('admin.spares.show', $spare->id),
                'icon' => 'package',
                'color' => 'secondary'
            ];
        }

        return $results;
    }
}
