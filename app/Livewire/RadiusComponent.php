<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RadiusComponent extends Component
{

    public $isConnected = false;
    public $lastChecked;
    public $stats = [
        'nas_count' => 0,
        'users_count' => 0,
        'active_sessions' => 0
    ];

    protected $listeners = ['refresh' => 'checkConnection'];

    public function mount()
    {
        $this->checkConnection();
    }

    public function checkConnection()
    {
        try {
            $connection = DB::connection('radius');
            $connection->getPdo();

            $this->stats = [
                'nas_count' => $connection->table('nas')->count(),
                'users_count' => $connection->table('radcheck')->count(),
                'active_sessions' => $connection->table('radacct')->whereNull('acctstoptime')->count()
            ];

            $this->isConnected = true;
            $this->lastChecked = now()->format('H:i:s');
        } catch (\Exception $e) {
            $this->isConnected = false;
            $this->lastChecked = now()->format('H:i:s');
        }
    }

    public function render()
    {
        return view('livewire.radius-component');
    }
}
