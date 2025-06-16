<?php

namespace App\Events;

use App\Models\Teacher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherLoginEvent
{
    use Dispatchable, SerializesModels;

    public $teacher;
    public $loginTime;
    public $ipAddress;
    public $userAgent;

    /**
     * Create a new event instance.
     */
    public function __construct(Teacher $teacher, string $ipAddress = null, string $userAgent = null)
    {
        $this->teacher = $teacher;
        $this->loginTime = now();
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}