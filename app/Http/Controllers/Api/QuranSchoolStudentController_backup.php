<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use App\Models\Student;
use App\Models\Mosque;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class QuranSchoolStudentController extends Controller
{
    /**
     * جلب معلومات المدرسة القرآنية مع الحلقات الفرعية النشطة
     * 
     * @param int $quranCircleId
     * @return JsonResponse
     */
    public function getQuranSchoolInfo($quranCircleId): JsonResponse
    {
        try {
            // جلب المدرسة القرآنية مع معلومات المسجد
            $quranCircle = QuranCircle::with(['mosque:id,name,neighborhood'])
                ->select('id', 'name', 'mosque_id', 'circle_type')
                ->find($quranCircleId);

            if (!$quranCircle) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة القرآنية غير موجودة'
                ], 404);
            }

            // التأكد من أن هذه مدرسة قرآنية أو حلقة جماعية
            if (!in_array($quranCircle->circle_type, ['مدرسة قرآنية', 'حلقة جماعية'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا النوع من الحلقات لا يدعم إضافة طلاب'
                ], 400);
            }

            // جلب الحلقات الفرعية النشطة فقط
            $activeGroups = CircleGroup::where('quran_circle_id', $quranCircleId)
                ->where('status', 'نشطة')
                ->with(['teacher:id,name,phone'])
                ->select('id', 'name', 'teacher_id', 'status', 'description', 'meeting_days')
                ->get();

            // إحصائيات المدرسة القرآنية
            $totalStudents = Student::where('quran_circle_id', $quranCircleId)->count();
            $activeStudents = Student::where('quran_circle_id', $quranCircleId)
                ->where('is_active', true)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'quran_school' => [
                        'id' => $quranCircle->id,
                        'name' => $quranCircle->name,
                        'mosque' => [
                            'id' => $quranCircle->mosque->id,
                            'name' => $quranCircle->mosque->name,
                            'neighborhood' => $quranCircle->mosque->neighborhood,
                        ]
                    ],
                    'circle_groups' => $activeGroups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'description' => $group->description,
                            'meeting_days' => $group->meeting_days,
                            'teacher' => $group->teacher ? [
                                'id' => $group->teacher->id,
                                'name' => $group->teacher->name,
                                'phone' => $group->teacher->phone,
                            ] : null,
                            'students_count' => Student::where('circle_group_id', $group->id)->count()
                        ];
                    }),
                    'statistics' => [
                        'total_students' => $totalStudents,
                        'active_students' => $activeStudents,
                        'total_groups' => $activeGroups->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب معلومات المدرسة القرآنية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إضافة طالب جديد للمدرسة القرآنية
     * 
     * @param Request $request
     * @param int $quranCircleId
     * @return JsonResponse
     */
    public function addStudent(Request $request, $quranCircleId): JsonResponse
    {
        try {
            // التحقق من وجود المدرسة القرآنية
            $quranCircle = QuranCircle::with('mosque:id,name')->find($quranCircleId);
            
            if (!$quranCircle) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة القرآنية غير موجودة'
                ], 404);
            }

            if (!in_array($quranCircle->circle_type, ['مدرسة قرآنية', 'حلقة جماعية'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إضافة طلاب لهذا النوع من الحلقات'
                ], 400);
            }

            // قواعد التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'identity_number' => 'required|string|max:20|unique:students,identity_number',
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'guardian_name' => 'required|string|max:255',
                'guardian_phone' => 'required|string|max:20',
                'birth_date' => 'nullable|date|before:today',
                'nationality' => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'neighborhood' => 'nullable|string|max:255',
                'circle_group_id' => 'required|exists:circle_groups,id',
                'enrollment_date' => 'nullable|date',
                'memorization_plan' => 'nullable|string',
                'review_plan' => 'nullable|string',
            ], [
                'identity_number.required' => 'رقم الهوية مطلوب',
                'identity_number.unique' => 'رقم الهوية موجود مسبقاً',
                'name.required' => 'اسم الطالب مطلوب',
                'guardian_name.required' => 'اسم ولي الأمر مطلوب',
                'guardian_phone.required' => 'رقم جوال ولي الأمر مطلوب',
                'circle_group_id.required' => 'الحلقة الفرعية مطلوبة',
                'circle_group_id.exists' => 'الحلقة الفرعية غير موجودة',
                'birth_date.before' => 'تاريخ الميلاد يجب أن يكون في الماضي',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من أن الحلقة الفرعية تنتمي للمدرسة القرآنية المحددة
            $circleGroup = CircleGroup::where('id', $request->circle_group_id)
                ->where('quran_circle_id', $quranCircleId)
                ->where('status', 'نشطة')
                ->first();

            if (!$circleGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'الحلقة الفرعية غير متاحة أو غير نشطة في هذه المدرسة القرآنية'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // إنشاء كلمة مرور افتراضية (آخر 4 أرقام من رقم الهوية)
                $defaultPassword = substr($request->identity_number, -4);
                
                // إنشاء الطالب
                $student = Student::create([
                    'identity_number' => $request->identity_number,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => Hash::make($defaultPassword),
                    'plain_password' => $defaultPassword, // للعرض لمرة واحدة فقط
                    'must_change_password' => true,
                    'birth_date' => $request->birth_date,
                    'nationality' => $request->nationality ?? 'سعودي',
                    'education_level' => $request->education_level,
                    'neighborhood' => $request->neighborhood,
                    'guardian_name' => $request->guardian_name,
                    'guardian_phone' => $request->guardian_phone,
                    'quran_circle_id' => $quranCircleId,
                    'circle_group_id' => $request->circle_group_id,
                    'mosque_id' => $quranCircle->mosque_id,
                    'enrollment_date' => $request->enrollment_date ?? now(),
                    'memorization_plan' => $request->memorization_plan,
                    'review_plan' => $request->review_plan,
                    'is_active' => true,
                    'is_active_user' => true,
                ]);

                // تحميل العلاقات للعرض
                $student->load([
                    'quranCircle:id,name',
                    'circleGroup:id,name',
                    'mosque:id,name'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'تم إضافة الطالب بنجاح',
                    'data' => [
                        'student' => [
                            'id' => $student->id,
                            'identity_number' => $student->identity_number,
                            'name' => $student->name,
                            'phone' => $student->phone,
                            'guardian_name' => $student->guardian_name,
                            'guardian_phone' => $student->guardian_phone,
                            'birth_date' => $student->birth_date?->format('Y-m-d'),
                            'nationality' => $student->nationality,
                            'education_level' => $student->education_level,
                            'neighborhood' => $student->neighborhood,
                            'enrollment_date' => $student->enrollment_date,
                            'memorization_plan' => $student->memorization_plan,
                            'review_plan' => $student->review_plan,
                            'default_password' => $defaultPassword, // يُعرض مرة واحدة فقط
                            'quran_school' => [
                                'id' => $student->quranCircle->id,
                                'name' => $student->quranCircle->name,
                            ],
                            'circle_group' => [
                                'id' => $student->circleGroup->id,
                                'name' => $student->circleGroup->name,
                            ],
                            'mosque' => [
                                'id' => $student->mosque->id,
                                'name' => $student->mosque->name,
                            ]
                        ]
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الطالب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب طلاب المدرسة القرآنية مع الفلترة
     * 
     * @param Request $request
     * @param int $quranCircleId
     * @return JsonResponse
     */
    public function getStudents(Request $request, $quranCircleId): JsonResponse
    {
        try {
            $quranCircle = QuranCircle::find($quranCircleId);
            
            if (!$quranCircle) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدرسة القرآنية غير موجودة'
                ], 404);
            }

            $query = Student::where('quran_circle_id', $quranCircleId)
                ->with([
                    'circleGroup:id,name,teacher_id',
                    'circleGroup.teacher:id,name'
                ]);

            // فلترة حسب الحلقة الفرعية
            if ($request->has('circle_group_id') && $request->circle_group_id) {
                $query->where('circle_group_id', $request->circle_group_id);
            }

            // فلترة حسب الحالة
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // بحث بالاسم أو رقم الهوية
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('identity_number', 'like', "%{$searchTerm}%");
                });
            }

            $students = $query->select([
                'id', 'identity_number', 'name', 'phone', 'guardian_name', 
                'guardian_phone', 'enrollment_date', 'circle_group_id', 
                'is_active', 'education_level', 'memorization_plan'
            ])
            ->orderBy('enrollment_date', 'desc')
            ->paginate($request->get('per_page', 15));

            // تنسيق البيانات لتشمل معلومات المعلم بشكل واضح
            $formattedStudents = $students->getCollection()->map(function ($student) {
                return [
                    'id' => $student->id,
                    'identity_number' => $student->identity_number,
                    'name' => $student->name,
                    'phone' => $student->phone,
                    'guardian_name' => $student->guardian_name,
                    'guardian_phone' => $student->guardian_phone,
                    'enrollment_date' => $student->enrollment_date,
                    'is_active' => $student->is_active,
                    'education_level' => $student->education_level,
                    'memorization_plan' => $student->memorization_plan,
                    'circle_group' => $student->circleGroup ? [
                        'id' => $student->circleGroup->id,
                        'name' => $student->circleGroup->name,
                        'teacher' => $student->circleGroup->teacher ? [
                            'id' => $student->circleGroup->teacher->id,
                            'name' => $student->circleGroup->teacher->name,
                            'phone' => $student->circleGroup->teacher->phone ?? null,
                        ] : null
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'students' => $formattedStudents,
                    'pagination' => [
                        'current_page' => $students->currentPage(),
                        'per_page' => $students->perPage(),
                        'total' => $students->total(),
                        'last_page' => $students->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الطلاب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث معلومات طالب
     * 
     * @param Request $request
     * @param int $quranCircleId
     * @param int $studentId
     * @return JsonResponse
     */
    public function updateStudent(Request $request, $quranCircleId, $studentId): JsonResponse
    {
        try {
            $student = Student::where('id', $studentId)
                ->where('quran_circle_id', $quranCircleId)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المدرسة القرآنية'
                ], 404);
            }

            // قواعد التحقق (أقل صرامة للتحديث)
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'guardian_name' => 'sometimes|required|string|max:255',
                'guardian_phone' => 'sometimes|required|string|max:20',
                'birth_date' => 'nullable|date|before:today',
                'nationality' => 'nullable|string|max:50',
                'education_level' => 'nullable|string|max:100',
                'neighborhood' => 'nullable|string|max:255',
                'circle_group_id' => 'sometimes|required|exists:circle_groups,id',
                'memorization_plan' => 'nullable|string',
                'review_plan' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من الحلقة الفرعية إذا تم تغييرها
            if ($request->has('circle_group_id')) {
                $circleGroup = CircleGroup::where('id', $request->circle_group_id)
                    ->where('quran_circle_id', $quranCircleId)
                    ->where('status', 'نشطة')
                    ->first();

                if (!$circleGroup) {
                    return response()->json([
                        'success' => false,
                        'message' => 'الحلقة الفرعية غير متاحة في هذه المدرسة القرآنية'
                    ], 400);
                }
            }

            // تحديث البيانات
            $student->update($request->only([
                'name', 'phone', 'guardian_name', 'guardian_phone', 'birth_date',
                'nationality', 'education_level', 'neighborhood', 'circle_group_id',
                'memorization_plan', 'review_plan', 'is_active'
            ]));

            $student->load([
                'quranCircle:id,name',
                'circleGroup:id,name',
                'mosque:id,name'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث معلومات الطالب بنجاح',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'identity_number' => $student->identity_number,
                        'name' => $student->name,
                        'phone' => $student->phone,
                        'guardian_name' => $student->guardian_name,
                        'guardian_phone' => $student->guardian_phone,
                        'birth_date' => $student->birth_date?->format('Y-m-d'),
                        'nationality' => $student->nationality,
                        'education_level' => $student->education_level,
                        'neighborhood' => $student->neighborhood,
                        'enrollment_date' => $student->enrollment_date,
                        'memorization_plan' => $student->memorization_plan,
                        'review_plan' => $student->review_plan,
                        'is_active' => $student->is_active,
                        'quran_school' => [
                            'id' => $student->quranCircle->id,
                            'name' => $student->quranCircle->name,
                        ],
                        'circle_group' => [
                            'id' => $student->circleGroup->id,
                            'name' => $student->circleGroup->name,
                        ],
                        'mosque' => [
                            'id' => $student->mosque->id,
                            'name' => $student->mosque->name,
                        ]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث معلومات الطالب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف طالب (إلغاء تفعيل)
     * 
     * @param int $quranCircleId
     * @param int $studentId
     * @return JsonResponse
     */
    public function deactivateStudent($quranCircleId, $studentId): JsonResponse
    {
        try {
            $student = Student::where('id', $studentId)
                ->where('quran_circle_id', $quranCircleId)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المدرسة القرآنية'
                ], 404);
            }

            // إلغاء تفعيل الطالب بدلاً من الحذف
            $student->update([
                'is_active' => false,
                'is_active_user' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء تفعيل الطالب بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء تفعيل الطالب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب بيانات الطالب لنموذج النقل (مع تعبئة البيانات مسبقاً)
     * 
     * @param int $quranCircleId
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentForTransfer($quranCircleId, $studentId): JsonResponse
    {
        try {
            $student = Student::where('id', $studentId)
                ->where('quran_circle_id', $quranCircleId)
                ->with([
                    'quranCircle:id,name',
                    'circleGroup:id,name,teacher_id',
                    'circleGroup.teacher:id,name',
                    'mosque:id,name'
                ])
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المدرسة القرآنية'
                ], 404);
            }

            // جلب جميع الحلقات الفرعية النشطة في نفس المدرسة القرآنية (لخيارات النقل)
            $availableGroups = CircleGroup::where('quran_circle_id', $quranCircleId)
                ->where('status', 'نشطة')
                ->where('id', '!=', $student->circle_group_id) // استبعاد الحلقة الحالية
                ->with(['teacher:id,name'])
                ->select('id', 'name', 'teacher_id', 'description')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'identity_number' => $student->identity_number,
                        'name' => $student->name,
                        'phone' => $student->phone,
                        'guardian_name' => $student->guardian_name,
                        'guardian_phone' => $student->guardian_phone,
                        'current_circle_group' => [
                            'id' => $student->circleGroup->id,
                            'name' => $student->circleGroup->name,
                            'teacher' => $student->circleGroup->teacher ? [
                                'id' => $student->circleGroup->teacher->id,
                                'name' => $student->circleGroup->teacher->name,
                            ] : null
                        ],
                        'quran_school' => [
                            'id' => $student->quranCircle->id,
                            'name' => $student->quranCircle->name,
                        ],
                        'mosque' => [
                            'id' => $student->mosque->id,
                            'name' => $student->mosque->name,
                        ]
                    ],
                    'available_groups' => $availableGroups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'description' => $group->description,
                            'teacher' => $group->teacher ? [
                                'id' => $group->teacher->id,
                                'name' => $group->teacher->name,
                            ] : null
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الطالب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * نقل طالب إلى حلقة فرعية أخرى داخل نفس المدرسة القرآنية
     * 
     * @param Request $request
     * @param int $quranCircleId
     * @param int $studentId
     * @return JsonResponse
     */
    public function transferStudent(Request $request, $quranCircleId, $studentId): JsonResponse
    {
        try {
            $student = Student::where('id', $studentId)
                ->where('quran_circle_id', $quranCircleId)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المدرسة القرآنية'
                ], 404);
            }

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'new_circle_group_id' => 'required|exists:circle_groups,id',
                'transfer_reason' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
            ], [
                'new_circle_group_id.required' => 'الحلقة الفرعية الجديدة مطلوبة',
                'new_circle_group_id.exists' => 'الحلقة الفرعية غير موجودة',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من أن الحلقة الفرعية الجديدة تنتمي لنفس المدرسة القرآنية وأنها نشطة
            $newCircleGroup = CircleGroup::where('id', $request->new_circle_group_id)
                ->where('quran_circle_id', $quranCircleId)
                ->where('status', 'نشطة')
                ->first();

            if (!$newCircleGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'الحلقة الفرعية الجديدة غير متاحة أو غير نشطة في هذه المدرسة القرآنية'
                ], 400);
            }

            // التحقق من أن الطالب ليس في نفس الحلقة الفرعية
            if ($student->circle_group_id == $request->new_circle_group_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب موجود بالفعل في هذه الحلقة الفرعية'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // حفظ بيانات النقل (للتتبع)
                $oldCircleGroup = $student->circleGroup;
                
                // تحديث الحلقة الفرعية للطالب
                $student->update([
                    'circle_group_id' => $request->new_circle_group_id
                ]);

                // تسجيل عملية النقل (يمكن إضافة جدول منفصل للتتبع لاحقاً)
                $transferLog = [
                    'student_id' => $student->id,
                    'from_circle_group' => $oldCircleGroup->name,
                    'to_circle_group' => $newCircleGroup->name,
                    'transfer_reason' => $request->transfer_reason,
                    'notes' => $request->notes,
                    'transferred_at' => now(),
                    'transferred_by' => 'system' // يمكن تحديث هذا ليكون المستخدم الحالي
                ];

                $student->load([
                    'quranCircle:id,name',
                    'circleGroup:id,name,teacher_id',
                    'circleGroup.teacher:id,name',
                    'mosque:id,name'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'تم نقل الطالب بنجاح',
                    'data' => [
                        'student' => [
                            'id' => $student->id,
                            'name' => $student->name,
                            'identity_number' => $student->identity_number,
                            'new_circle_group' => [
                                'id' => $student->circleGroup->id,
                                'name' => $student->circleGroup->name,
                                'teacher' => $student->circleGroup->teacher ? [
                                    'id' => $student->circleGroup->teacher->id,
                                    'name' => $student->circleGroup->teacher->name,
                                ] : null
                            ],
                            'quran_school' => [
                                'id' => $student->quranCircle->id,
                                'name' => $student->quranCircle->name,
                            ],
                            'mosque' => [
                                'id' => $student->mosque->id,
                                'name' => $student->mosque->name,
                            ]
                        ],
                        'transfer_info' => $transferLog
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء نقل الطالب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
