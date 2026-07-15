<?php

namespace App\Livewire\Others\BugReportRequest;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    #[Url]
    public $search = '';

    #[Url]
    public $typeFilter = '';

    #[Url]
    public $statusFilter = '';

    #[Url]
    public $priorityFilter = '';

    // Create ticket form fields
    public $title = '';
    public $description = '';
    public $type = 'bug';
    public $priority = 'medium';
    public $screenshot;
    public $iteration = 0;

    // Admin/Developer edit fields
    public $selectedTicketId = null;
    public $editStatus = 'open';
    public $developerResponse = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->type = 'bug';
        $this->priority = 'medium';
        $this->screenshot = null;
        $this->iteration++;
    }

    public function store()
    {
        $rules = [
            'title' => 'required|min:5|max:255',
            'description' => 'required|min:10',
            'type' => 'required|in:bug,request',
            'priority' => 'required|in:low,medium,high,critical',
            'screenshot' => 'nullable|image|max:2048', // max 2MB
        ];

        $this->validate($rules);

        $screenshotPath = null;
        if ($this->screenshot) {
            $screenshotPath = $this->screenshot->store('tickets', 'public');
        }

        Ticket::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => 'open',
            'screenshot' => $screenshotPath,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'addTicketModal');
        $this->dispatch('show-toast', type: 'success', message: 'Tiket berhasil dibuat!');
    }

    public function selectTicket($id)
    {
        $ticket = Ticket::find($id);
        if ($ticket) {
            $this->selectedTicketId = $ticket->id;
            $this->editStatus = $ticket->status;
            $this->developerResponse = $ticket->developer_response ?? '';
            $this->dispatch('open-modal', 'respondTicketModal');
        }
    }

    public function updateResponse()
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            $this->dispatch('show-toast', type: 'error', message: 'Anda tidak memiliki akses untuk memperbarui tiket ini.');
            return;
        }

        $this->validate([
            'editStatus' => 'required|in:open,in_progress,resolved,closed',
            'developerResponse' => 'nullable|string',
        ]);

        $ticket = Ticket::find($this->selectedTicketId);
        if ($ticket) {
            $completedAt = in_array($this->editStatus, ['resolved', 'closed']) ? now() : null;

            $ticket->update([
                'status' => $this->editStatus,
                'developer_response' => $this->developerResponse,
                'completed_at' => $completedAt,
            ]);

            $this->dispatch('close-modal', 'respondTicketModal');
            $this->dispatch('show-toast', type: 'success', message: 'Tiket berhasil diperbarui!');
        }
    }

    public function deleteTicket($id)
    {
        $ticket = Ticket::find($id);
        if ($ticket) {
            $user = Auth::user();
            // User can only delete their own open tickets, admin can delete any ticket
            if ($ticket->user_id === Auth::id() && $ticket->status === 'open' || ($user && $user->hasRole('admin'))) {
                if ($ticket->screenshot) {
                    Storage::disk('public')->delete($ticket->screenshot);
                }
                $ticket->delete();
                $this->dispatch('show-toast', type: 'success', message: 'Tiket berhasil dihapus.');
            } else {
                $this->dispatch('show-toast', type: 'error', message: 'Anda tidak diizinkan menghapus tiket ini.');
            }
        }
    }

    #[Computed]
    public function filteredTickets()
    {
        $query = Ticket::with('user');

        $user = Auth::user();
        // Regular users only see their own tickets, admin sees all
        if ($user && !$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $tickets = $this->filteredTickets()->paginate(10);

        // Fetch KPI stats
        $baseKpiQuery = Ticket::query();
        $user = Auth::user();
        if ($user && !$user->hasRole('admin')) {
            $baseKpiQuery->where('user_id', $user->id);
        }

        $kpis = [
            'total' => (clone $baseKpiQuery)->count(),
            'open' => (clone $baseKpiQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseKpiQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseKpiQuery)->where('status', 'resolved')->count(),
            'closed' => (clone $baseKpiQuery)->where('status', 'closed')->count(),
        ];

        return view('livewire.others.bug-report-request.index', [
            'tickets' => $tickets,
            'kpis' => $kpis,
            'isAdmin' => $user ? $user->hasRole('admin') : false,
        ])->layout('layouts.app');
    }
}
