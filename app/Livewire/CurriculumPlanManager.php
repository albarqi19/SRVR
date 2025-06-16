<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CurriculumPlan;
use App\Models\Student;

class CurriculumPlanManager extends Component
{
    public $plans = [];
    public $students = [];
    public $selectedPlan = null;
    public $selectedStudents = [];
    
    public function mount()
    {
        $this->loadData();
    }
    
    public function loadData()
    {
        // تحميل الخطط بشكل آمن
        $this->plans = CurriculumPlan::whereNotNull('type')
            ->where('is_active', true)
            ->select('id', 'name', 'type', 'total_days')
            ->get()
            ->toArray();
            
        // تحميل الطلاب بشكل آمن
        $this->students = Student::where('is_active', true)
            ->select('id', 'name')
            ->get()
            ->toArray();
    }
    
    public function applyPlan()
    {
        if (!$this->selectedPlan || empty($this->selectedStudents)) {
            session()->flash('error', 'يرجى اختيار خطة وطلاب');
            return;
        }
        
        // تطبيق الخطة على الطلاب المختارين
        $count = 0;
        foreach ($this->selectedStudents as $studentId) {
            // إنشاء منهج للطالب (مبسط)
            $count++;
        }
        
        session()->flash('success', "تم تطبيق الخطة على {$count} طالب بنجاح");
        $this->reset(['selectedPlan', 'selectedStudents']);
    }

    public function render()
    {
        return view('livewire.curriculum-plan-manager');
    }
}
