<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\User;
use App\Models\Mosque;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TeacherApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء بيانات تجريبية
        $this->createTestData();
    }

    private function createTestData()
    {
        // إنشاء مسجد
        $mosque = Mosque::factory()->create([
            'name' => 'مسجد النور للاختبار'
        ]);

        // إنشاء مستخدم ومعلم
        $user = User::factory()->create([
            'name' => 'أحمد محمد للاختبار',
            'email' => 'teacher.test@example.com'
        ]);

        $this->teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'mosque_id' => $mosque->id,
            'is_active' => true
        ]);

        // إنشاء حلقة
        $this->circle = QuranCircle::factory()->create([
            'teacher_id' => $this->teacher->id,
            'mosque_id' => $mosque->id,
            'name' => 'حلقة الاختبار'
        ]);

        // إنشاء طلاب
        $this->students = Student::factory(5)->create([
            'mosque_id' => $mosque->id
        ]);

        // ربط الطلاب بالحلقة
        $this->circle->students()->attach($this->students->pluck('id'));
    }

    /** @test */
    public function it_can_get_teachers_list()
    {
        $response = $this->getJson('/api/teachers');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'نجح',
                    'رسالة',
                    'البيانات' => [
                        '*' => [
                            'id',
                            'الاسم',
                            'البريد_الإلكتروني',
                            'رقم_الهاتف',
                            'المسجد',
                            'نشط',
                            'عدد_الحلقات',
                            'عدد_الطلاب'
                        ]
                    ],
                    'معلومات_الصفحة'
                ])
                ->assertJson([
                    'نجح' => true,
                    'رسالة' => 'تم جلب قائمة المعلمين بنجاح'
                ]);
    }

    /** @test */
    public function it_can_get_teacher_details()
    {
        $response = $this->getJson("/api/teachers/{$this->teacher->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'نجح',
                    'رسالة',
                    'البيانات' => [
                        'معلومات_أساسية',
                        'إحصائيات',
                        'الحلقات',
                        'سجل_الحضور_الأخير'
                    ]
                ])
                ->assertJson([
                    'نجح' => true,
                    'رسالة' => 'تم جلب تفاصيل المعلم بنجاح'
                ]);
    }

    /** @test */
    public function it_can_get_teacher_circles()
    {
        $response = $this->getJson("/api/teachers/{$this->teacher->id}/circles");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'نجح',
                    'رسالة',
                    'اسم_المعلم',
                    'عدد_الحلقات',
                    'الحلقات' => [
                        '*' => [
                            'id',
                            'اسم_الحلقة',
                            'المستوى',
                            'عدد_الطلاب',
                            'تفاصيل_الطلاب'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_teacher_stats()
    {
        $response = $this->getJson("/api/teachers/{$this->teacher->id}/stats");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'نجح',
                    'رسالة',
                    'اسم_المعلم',
                    'الإحصائيات' => [
                        'الحلقات_والطلاب',
                        'الحضور',
                        'التسميع',
                        'المالية'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_teacher()
    {
        $response = $this->getJson('/api/teachers/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'نجح' => false,
                    'رسالة' => 'المعلم غير موجود'
                ]);
    }

    /** @test */
    public function it_can_filter_teachers_by_mosque()
    {
        $response = $this->getJson("/api/teachers?mosque_id={$this->teacher->mosque_id}");

        $response->assertStatus(200)
                ->assertJson([
                    'نجح' => true
                ]);
    }

    /** @test */
    public function it_can_search_teachers_by_name()
    {
        $response = $this->getJson('/api/teachers?search=أحمد');

        $response->assertStatus(200)
                ->assertJson([
                    'نجح' => true
                ]);
    }
}
