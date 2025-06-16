<?php

namespace Tests\Feature;

use App\Models\Curriculum;
use App\Models\CurriculumPlan;
use App\Models\CurriculumLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test curriculum
        $this->curriculum = Curriculum::create([
            'name' => 'Test Curriculum',
            'description' => 'Test Description',
            'type' => 'منهج طالب',
            'is_active' => true,
        ]);        // Create a test level if needed
        $this->level = CurriculumLevel::create([
            'curriculum_id' => $this->curriculum->id,
            'name' => 'Test Level',
            'description' => 'Test Level Description',
            'level_order' => 1,
            'is_active' => true,
        ]);
    }

    /**
     * Test creating a single surah curriculum plan
     */
    public function test_single_surah_plan_content_populated(): void
    {        $plan = CurriculumPlan::create([
            'curriculum_id' => $this->curriculum->id,
            'curriculum_level_id' => $this->level->id,
            'name' => 'Test Single Surah Plan',
            'plan_type' => 'الدرس',
            'content_type' => 'quran',
            'range_type' => 'single_surah',
            'surah_number' => 1, // Al-Fatiha
            'start_verse' => 1,
            'end_verse' => 7,
            'expected_days' => 1,
        ]);

        $this->assertNotNull($plan->content, 'Content field should not be null for single surah plan');
        $this->assertNotEmpty($plan->content, 'Content field should not be empty for single surah plan');
        $this->assertEquals($plan->formatted_content, $plan->content, 'Content should match formatted_content for single surah');
    }

    /**
     * Test creating a multi-surah curriculum plan
     */
    public function test_multi_surah_plan_content_populated(): void
    {        $plan = CurriculumPlan::create([
            'curriculum_id' => $this->curriculum->id,
            'curriculum_level_id' => $this->level->id,
            'name' => 'Test Multi Surah Plan',
            'plan_type' => 'الدرس',
            'content_type' => 'quran',
            'range_type' => 'multi_surah',
            'start_surah_number' => 1, // Al-Fatiha
            'start_surah_verse' => 1,
            'end_surah_number' => 2, // Al-Baqarah
            'end_surah_verse' => 5,
            'expected_days' => 1,
        ]);

        $this->assertNotNull($plan->content, 'Content field should not be null for multi surah plan');
        $this->assertNotEmpty($plan->content, 'Content field should not be empty for multi surah plan');
        $this->assertEquals($plan->multi_surah_formatted_content, $plan->content, 'Content should match multi_surah_formatted_content for multi surah');
    }

    /**
     * Test creating a text-based curriculum plan
     */
    public function test_text_plan_content_populated(): void
    {
        $content = 'This is a test text content';
          $plan = CurriculumPlan::create([
            'curriculum_id' => $this->curriculum->id,
            'curriculum_level_id' => $this->level->id,
            'name' => 'Test Text Plan',
            'plan_type' => 'الدرس',
            'content_type' => 'text',
            'content' => $content,
            'expected_days' => 1,
        ]);

        $this->assertNotNull($plan->content, 'Content field should not be null for text plan');
        $this->assertEquals($content, $plan->content, 'Content should match the provided text content');
    }

    /**
     * Test bulk plan creation via BulkPlanCreator logic
     */    public function test_bulk_plan_creation_with_content(): void
    {
        // Test creating a plan using the same logic as BulkPlanCreator
        $planData = [
            'curriculum_id' => $this->curriculum->id,
            'curriculum_level_id' => $this->level->id,
            'name' => 'سورة الفاتحة من الآية 1 إلى الآية 7',
            'plan_type' => 'الدرس',
            'content' => 'سورة الفاتحة من الآية 1 إلى الآية 7', // This is what BulkPlanCreator sets
            'expected_days' => 1,
        ];

        $plan = CurriculumPlan::create($planData);

        $this->assertNotNull($plan->content, 'Content field should not be null when set explicitly');
        $this->assertEquals($planData['content'], $plan->content, 'Content should match the provided value');
    }

    /**
     * Test updating existing plan content
     */
    public function test_plan_content_updated_on_save(): void
    {        // Create a plan without Quran content first
        $plan = CurriculumPlan::create([
            'curriculum_id' => $this->curriculum->id,
            'curriculum_level_id' => $this->level->id,
            'name' => 'Test Plan',
            'plan_type' => 'الدرس',
            'content_type' => 'text',
            'content' => 'Initial content',
            'expected_days' => 1,
        ]);

        // Update to Quran content
        $plan->update([
            'content_type' => 'quran',
            'range_type' => 'single_surah',
            'surah_number' => 1,
            'start_verse' => 1,
            'end_verse' => 7,
        ]);

        $plan->refresh();

        $this->assertNotNull($plan->content, 'Content field should still be populated after update');
        $this->assertEquals($plan->formatted_content, $plan->content, 'Content should be updated to formatted content');
    }
}
