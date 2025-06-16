<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupervisorLoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $supervisor;
    public \Carbon\Carbon $loginTime;
    public string $ipAddress;

    /**
     * Create a new event instance.
     */
    public function __construct(User $supervisor, string $ipAddress = null)
    {
        $this->supervisor = $supervisor;
        $this->loginTime = now();
        $this->ipAddress = $ipAddress ?? request()->ip() ?? 'غير محدد';
    }
}
