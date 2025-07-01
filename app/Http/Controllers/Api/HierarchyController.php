<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mosque;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API إدارة الهيكل التدريجي: مسجد → مدرسة قرآنية → حلقات فرعية
 */
class HierarchyController extends Controller
{
    /**
     * عرض الهيكل الكامل للمساجد والمدارس القرآنية والحلقات الفرعية
     */
    public function getFullHierarchy(Request $request): JsonResponse
    {
        try {
            $mosques = Mosque::with([
                'quranCircles' => function($query) {
                    $query->with([
                        'circleGroups' => function($subQuery) {
                            $subQuery->with('teacher:id,name');
                        }
                    ]);
                }
            ])->get();

            $hierarchy = [];

            foreach ($mosques as $mosque) {
                $mosqueData = [
                    'mosque_id' => $mosque->id,
                    'mosque_name' => $mosque->name,
                    'neighborhood' => $mosque->neighborhood,
                    'contact_number' => $mosque->contact_number,
                    'total_quran_schools' => $mosque->quranCircles->count(),
                    'quran_schools' => []
                ];

                foreach ($mosque->quranCircles as $circle) {
                    $circleData = [
                        'quran_school_id' => $circle->id,
                        'quran_school_name' => $circle->name,
                        'circle_type' => $circle->circle_type,
                        'circle_status' => $circle->circle_status,
                        'time_period' => $circle->time_period,
                        'has_ratel' => $circle->has_ratel,
                        'has_qias' => $circle->has_qias,
                        'total_sub_circles' => $circle->circleGroups->count(),
                        'sub_circles' => []
                    ];

                    foreach ($circle->circleGroups as $group) {
                        $groupData = [
                            'sub_circle_id' => $group->id,
                            'sub_circle_name' => $group->name,
                            'status' => $group->status,
                            'description' => $group->description,
                            'meeting_days' => $group->meeting_days,
                            'teacher' => $group->teacher ? [
                                'teacher_id' => $group->teacher->id,
                                'teacher_name' => $group->teacher->name
                            ] : null,
                            'additional_info' => $group->additional_info
                        ];

                        $circleData['sub_circles'][] = $groupData;
                    }

                    $mosqueData['quran_schools'][] = $circleData;
                }

                $hierarchy[] = $mosqueData;
            }

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الهيكل الكامل بنجاح',
                'total_mosques' => count($hierarchy),
                'total_quran_schools' => QuranCircle::count(),
                'total_sub_circles' => CircleGroup::count(),
                'data' => $hierarchy
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض مدارس قرآنية وحلقاتها الفرعية لمسجد محدد
     */
    public function getMosqueHierarchy($mosqueId): JsonResponse
    {
        try {
            $mosque = Mosque::with([
                'quranCircles' => function($query) {
                    $query->with([
                        'circleGroups' => function($subQuery) {
                            $subQuery->with('teacher:id,name');
                        }
                    ]);
                }
            ])->find($mosqueId);

            if (!$mosque) {
                return response()->json([
                    'success' => false,
                    'message' => 'المسجد غير موجود'
                ], 404);
            }

            $mosqueData = [
                'mosque_id' => $mosque->id,
                'mosque_name' => $mosque->name,
                'neighborhood' => $mosque->neighborhood,
                'contact_number' => $mosque->contact_number,
                'total_quran_schools' => $mosque->quranCircles->count(),
                'quran_schools' => []
            ];

            foreach ($mosque->quranCircles as $circle) {
                $circleData = [
                    'quran_school_id' => $circle->id,
                    'quran_school_name' => $circle->name,
                    'circle_type' => $circle->circle_type,
                    'circle_status' => $circle->circle_status,
                    'time_period' => $circle->time_period,
                    'total_sub_circles' => $circle->circleGroups->count(),
                    'sub_circles' => []
                ];

                foreach ($circle->circleGroups as $group) {
                    $groupData = [
                        'sub_circle_id' => $group->id,
                        'sub_circle_name' => $group->name,
                        'status' => $group->status,
                        'teacher' => $group->teacher ? [
                            'teacher_id' => $group->teacher->id,
                            'teacher_name' => $group->teacher->name
                        ] : null
                    ];

                    $circleData['sub_circles'][] = $groupData;
                }

                $mosqueData['quran_schools'][] = $circleData;
            }

            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات المسجد بنجاح',
                'data' => $mosqueData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض الحلقات الفرعية لمدرسة قرآنية محددة
     */
    public function getQuranSchoolSubCircles($quranCircleId): JsonResponse
    {
        try {
            $circle = QuranCircle::with([
                'mosque:id,name',
                'circleGroups' => function($query) {
                    $query->with('teacher:id,name');
                }
            ])->find($quranCircleId);

            if (!$circle) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة القرآنية غير موجودة'
                ], 404);
            }

            $circleData = [
                'quran_school_id' => $circle->id,
                'quran_school_name' => $circle->name,
                'circle_type' => $circle->circle_type,
                'circle_status' => $circle->circle_status,
                'time_period' => $circle->time_period,
                'mosque' => [
                    'mosque_id' => $circle->mosque->id,
                    'mosque_name' => $circle->mosque->name
                ],
                'total_sub_circles' => $circle->circleGroups->count(),
                'sub_circles' => []
            ];

            foreach ($circle->circleGroups as $group) {
                $groupData = [
                    'sub_circle_id' => $group->id,
                    'sub_circle_name' => $group->name,
                    'status' => $group->status,
                    'description' => $group->description,
                    'meeting_days' => $group->meeting_days,
                    'teacher' => $group->teacher ? [
                        'teacher_id' => $group->teacher->id,
                        'teacher_name' => $group->teacher->name
                    ] : null
                ];

                $circleData['sub_circles'][] = $groupData;
            }

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الحلقات الفرعية بنجاح',
                'data' => $circleData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض إحصائيات مبسطة للهيكل
     */
    public function getHierarchyStats(): JsonResponse
    {
        try {
            $stats = [
                'total_mosques' => Mosque::count(),
                'total_quran_schools' => QuranCircle::count(),
                'total_sub_circles' => CircleGroup::count(),
                'active_quran_schools' => QuranCircle::where('circle_status', 'تعمل')->count(),
                'inactive_quran_schools' => QuranCircle::where('circle_status', '!=', 'تعمل')->count(),
                'active_sub_circles' => CircleGroup::where('status', 'active')->count(),
                'mosques_with_schools' => Mosque::has('quranCircles')->count(),
                'schools_with_sub_circles' => QuranCircle::has('circleGroups')->count()
            ];

            // إحصائيات حسب نوع المدرسة القرآنية
            $circleTypes = QuranCircle::selectRaw('circle_type, COUNT(*) as count')
                ->groupBy('circle_type')
                ->pluck('count', 'circle_type')
                ->toArray();

            $stats['circle_types'] = $circleTypes;

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الإحصائيات بنجاح',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
