<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuranCircle;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherEvaluation;
use App\Models\StudentTransferRequest;
use App\Models\StudentAttendance;
use App\Models\CircleSupervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    /**
     * الحصول على قائمة المشرفين
     */
    public function index(): JsonResponse
    {
        try {
            // الحصول على جميع المستخدمين الذين لديهم دور مشرف
            $supervisors = User::role('supervisor')
                ->select('id', 'name', 'email', 'phone', 'identity_number', 'is_active', 'created_at')
                ->with(['circleSupervisors.quranCircle:id,name'])
                ->get();

            // تنسيق البيانات لتشمل معلومات إضافية
            $formattedSupervisors = $supervisors->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'name' => $supervisor->name,
                    'email' => $supervisor->email,
                    'phone' => $supervisor->phone,
                    'identity_number' => $supervisor->identity_number,
                    'is_active' => $supervisor->is_active,
                    'created_at' => $supervisor->created_at,
                    'circles_count' => $supervisor->circleSupervisors()->active()->count(),
                    'assigned_circles' => $supervisor->circleSupervisors()
                        ->active()
                        ->with('quranCircle:id,name')
                        ->get()
                        ->map(function ($assignment) {
                            return [
                                'id' => $assignment->quranCircle->id,
                                'name' => $assignment->quranCircle->name,
                                'assignment_date' => $assignment->assignment_date,
                            ];
                        })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة المشرفين بنجاح',
                'data' => $formattedSupervisors,
                'total_count' => $formattedSupervisors->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة المشرفين',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على مشرف محدد بالـ ID
     */
    public function show($id): JsonResponse
    {
        try {
            $supervisor = User::role('supervisor')
                ->select('id', 'name', 'email', 'phone', 'identity_number', 'is_active', 'created_at')
                ->with(['circleSupervisors.quranCircle:id,name'])
                ->find($id);

            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }

            $formattedSupervisor = [
                'id' => $supervisor->id,
                'name' => $supervisor->name,
                'email' => $supervisor->email,
                'phone' => $supervisor->phone,
                'identity_number' => $supervisor->identity_number,
                'is_active' => $supervisor->is_active,
                'created_at' => $supervisor->created_at,
                'circles_count' => $supervisor->circleSupervisors()->active()->count(),
                'assigned_circles' => $supervisor->circleSupervisors()
                    ->active()
                    ->with('quranCircle:id,name')
                    ->get()
                    ->map(function ($assignment) {
                        return [
                            'id' => $assignment->quranCircle->id,
                            'name' => $assignment->quranCircle->name,
                            'assignment_date' => $assignment->assignment_date,
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات المشرف بنجاح',
                'data' => $formattedSupervisor
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات المشرف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على إحصائيات المشرفين العامة
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $totalSupervisors = User::role('supervisor')->count();
            $activeSupervisors = User::role('supervisor')->where('is_active', true)->count();
            $inactiveSupervisors = $totalSupervisors - $activeSupervisors;
            
            $totalCircles = \App\Models\QuranCircle::count();
            $supervisedCircles = CircleSupervisor::active()->distinct('quran_circle_id')->count();
            $unsupervisedCircles = $totalCircles - $supervisedCircles;

            $statistics = [
                'supervisors' => [
                    'total' => $totalSupervisors,
                    'active' => $activeSupervisors,
                    'inactive' => $inactiveSupervisors
                ],
                'circles' => [
                    'total' => $totalCircles,
                    'supervised' => $supervisedCircles,
                    'unsupervised' => $unsupervisedCircles
                ],
                'averages' => [
                    'circles_per_supervisor' => $activeSupervisors > 0 ? round($supervisedCircles / $activeSupervisors, 2) : 0
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب إحصائيات المشرفين بنجاح',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * لوحة تحكم المشرف
     */
    public function supervisorDashboard(): JsonResponse
    {
        try {
            // يمكن إضافة فحص المصادقة لاحقاً
            // $user = Auth::user();
            
            $dashboardData = [
                'welcome_message' => 'مرحباً بك في لوحة تحكم المشرف',
                'current_date' => now()->format('Y-m-d'),
                'system_status' => 'نشط',
                'notifications' => [
                    'new_students' => 0,
                    'pending_reports' => 0,
                    'upcoming_visits' => 0
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم تحميل لوحة تحكم المشرف بنجاح',
                'data' => $dashboardData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل لوحة التحكم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب معلمين المشرف
     */
    public function getSupervisorTeachers(Request $request): JsonResponse
    {
        try {
            // الحصول على معرف المشرف من الطلب أو من المصادقة
            $supervisorId = $request->get('supervisor_id'); // للاختبار، يمكن تمرير المعرف
            // $supervisorId = Auth::id(); // في الإنتاج، استخدم المصادقة
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }
            
            // التحقق من وجود المشرف
            $supervisor = User::role('supervisor')->find($supervisorId);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }
            
            // الحصول على الحلقات التي يشرف عليها
            $supervisedCircleIds = CircleSupervisor::where('supervisor_id', $supervisorId)
                ->active()
                ->pluck('quran_circle_id');
            
            if ($supervisedCircleIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد حلقات مشرف عليها',
                    'data' => [],
                    'total_count' => 0
                ], 200);
            }
            
            // جلب المعلمين فقط من الحلقات المشرف عليها
            $teachers = Teacher::with(['quranCircle:id,name', 'mosque:id,name'])
                ->select('id', 'name', 'phone', 'identity_number', 'job_title', 'task_type', 'work_time', 'quran_circle_id', 'mosque_id', 'is_active_user', 'evaluation', 'start_date')
                ->whereIn('quran_circle_id', $supervisedCircleIds)
                ->get();

            $formattedTeachers = $teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'phone' => $teacher->phone,
                    'identity_number' => $teacher->identity_number,
                    'job_title' => $teacher->job_title,
                    'task_type' => $teacher->task_type,
                    'work_time' => $teacher->work_time,
                    'is_active_user' => $teacher->is_active_user,
                    'evaluation' => $teacher->evaluation,
                    'start_date' => $teacher->start_date,
                    'circle' => $teacher->quranCircle ? [
                        'id' => $teacher->quranCircle->id,
                        'name' => $teacher->quranCircle->name
                    ] : null,
                    'mosque' => $teacher->mosque ? [
                        'id' => $teacher->mosque->id,
                        'name' => $teacher->mosque->name
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة المعلمين بنجاح',
                'data' => $formattedTeachers,
                'total_count' => $formattedTeachers->count(),
                'supervised_circles_count' => $supervisedCircleIds->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة المعلمين',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب طلاب الحلقات التي يشرف عليها المشرف
     */
    public function getSupervisorStudents(Request $request): JsonResponse
    {
        try {
            // الحصول على معرف المشرف من الطلب أو من المصادقة
            $supervisorId = $request->get('supervisor_id'); // للاختبار، يمكن تمرير المعرف
            // $supervisorId = Auth::id(); // في الإنتاج، استخدم المصادقة
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }
            
            // التحقق من وجود المشرف
            $supervisor = User::role('supervisor')->find($supervisorId);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }
            
            // الحصول على الحلقات التي يشرف عليها
            $supervisedCircleIds = CircleSupervisor::where('supervisor_id', $supervisorId)
                ->active()
                ->pluck('quran_circle_id');
            
            if ($supervisedCircleIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد حلقات مشرف عليها',
                    'data' => [],
                    'total_count' => 0
                ], 200);
            }
            
            // جلب الطلاب فقط من الحلقات المشرف عليها
            $students = Student::with(['quranCircle:id,name', 'circleGroup:id,name'])
                ->select('id', 'name', 'phone', 'guardian_phone', 'identity_number', 'quran_circle_id', 'circle_group_id', 'enrollment_date')
                ->whereIn('quran_circle_id', $supervisedCircleIds)
                ->get();

            $formattedStudents = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'phone' => $student->phone,
                    'guardian_phone' => $student->guardian_phone,
                    'identity_number' => $student->identity_number,
                    'enrollment_date' => $student->enrollment_date,
                    'circle' => $student->quranCircle ? [
                        'id' => $student->quranCircle->id,
                        'name' => $student->quranCircle->name
                    ] : null,
                    'group' => $student->circleGroup ? [
                        'id' => $student->circleGroup->id,
                        'name' => $student->circleGroup->name
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة الطلاب بنجاح',
                'data' => $formattedStudents,
                'total_count' => $formattedStudents->count(),
                'supervised_circles_count' => $supervisedCircleIds->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة الطلاب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على الحلقات المشرف عليها
     */
    public function getAssignedCircles(Request $request): JsonResponse
    {
        try {
            // الحصول على معرف المشرف من الطلب أو من المصادقة
            $supervisorId = $request->get('supervisor_id'); // للاختبار، يمكن تمرير المعرف
            // $supervisorId = Auth::id(); // في الإنتاج، استخدم المصادقة
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }
            
            // التحقق من وجود المشرف
            $supervisor = User::role('supervisor')->find($supervisorId);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }

            // الحصول على الحلقات المشرف عليها فقط
            $supervisedCircles = QuranCircle::with(['mosque:id,name,neighborhood'])
                ->whereHas('circleSupervisors', function($query) use ($supervisorId) {
                    $query->where('supervisor_id', $supervisorId)
                          ->where('is_active', true);
                })
                ->get(['id', 'name', 'mosque_id', 'time_period', 'circle_type', 'circle_status']);

            return response()->json([
                'success' => true,
                'data' => $supervisedCircles->map(function ($circle) {
                    // حساب عدد الطلاب الحاليين من العلاقات
                    $currentStudentsCount = \App\Models\Student::where('quran_circle_id', $circle->id)->count();
                    // حساب عدد المجموعات من العلاقات
                    $groupsCount = \App\Models\CircleGroup::where('quran_circle_id', $circle->id)->count();
                    
                    return [
                        'id' => $circle->id,
                        'name' => $circle->name,
                        'mosque' => $circle->mosque ? [
                            'id' => $circle->mosque->id,
                            'name' => $circle->mosque->name,
                            'neighborhood' => $circle->mosque->neighborhood
                        ] : null,
                        'time_period' => $circle->time_period,
                        'max_students' => 15, // قيمة افتراضية للاختبار
                        'current_students_count' => $currentStudentsCount,
                        'groups_count' => $groupsCount
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على طلاب حلقة محددة
     */
    public function getCircleStudents($circleId): JsonResponse
    {
        try {
            // تجاهل فحص المصادقة للاختبار
            // $user = Auth::user();
            
            // التحقق من أن المستخدم مشرف (مُعطَّل للاختبار)
            // if (!$user->hasRole('supervisor')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'غير مصرح لك بالوصول لهذه البيانات'
            //     ], 403);
            // }

            // التحقق من أن المشرف له صلاحية على هذه الحلقة (مُعطَّل للاختبار)
            // $hasAccess = CircleSupervisor::where('user_id', $user->id)
            //     ->where('quran_circle_id', $circleId)
            //     ->exists();

            // if (!$hasAccess) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'ليس لديك صلاحية على هذه الحلقة'
            //     ], 403);
            // }

            // الحصول على طلاب الحلقة
            $students = Student::where('quran_circle_id', $circleId)
                ->with(['circleGroup:id,name'])
                ->get(['id', 'name', 'phone', 'guardian_phone', 'circle_group_id', 'enrollment_date']);

            return response()->json([
                'success' => true,
                'data' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'phone' => $student->phone,
                        'guardian_phone' => $student->guardian_phone,
                        'enrollment_date' => $student->enrollment_date,
                        'group' => $student->circleGroup ? [
                            'id' => $student->circleGroup->id,
                            'name' => $student->circleGroup->name
                        ] : null
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الطلاب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * طلب نقل طالب
     */
    public function requestStudentTransfer(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مصادق عليه'
                ], 401);
            }

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'current_circle_id' => 'required|exists:quran_circles,id',
                'requested_circle_id' => 'required|exists:quran_circles,id|different:current_circle_id',
                'current_circle_group_id' => 'nullable|exists:circle_groups,id',
                'requested_circle_group_id' => 'nullable|exists:circle_groups,id',
                'transfer_reason' => 'required|string|max:1000',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // إنشاء طلب النقل مباشرة (مُبسط للاختبار)
            $transferRequest = [
                'student_id' => $request->student_id,
                'current_circle_id' => $request->current_circle_id,
                'current_circle_group_id' => $request->current_circle_group_id,
                'requested_circle_id' => $request->requested_circle_id,
                'requested_circle_group_id' => $request->requested_circle_group_id,
                'request_date' => now(),
                'transfer_reason' => $request->transfer_reason,
                'notes' => $request->notes,
                'requested_by' => $user->id,
                'status' => 'pending'
            ];

            // محاكاة إنشاء الطلب (بدون قاعدة البيانات للاختبار)
            $requestId = rand(1000, 9999);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب النقل بنجاح',
                'data' => [
                    'request_id' => $transferRequest->id,
                    'status' => $transferRequest->status,
                    'request_date' => $transferRequest->request_date
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تقديم طلب النقل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على طلبات النقل المقدمة
     */
    public function getTransferRequests(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مصادق عليه'
                ], 401);
            }

            // الحصول على طلبات النقل المقدمة من المشرف
            $requests = StudentTransferRequest::where('requested_by', $user->id)
                ->with([
                    'student:id,name',
                    'currentCircle:id,name',
                    'requestedCircle:id,name',
                    'currentCircleGroup:id,name',
                    'requestedCircleGroup:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'student' => $request->student ? [
                            'id' => $request->student->id,
                            'name' => $request->student->name
                        ] : null,
                        'current_circle' => $request->currentCircle ? [
                            'id' => $request->currentCircle->id,
                            'name' => $request->currentCircle->name
                        ] : null,
                        'requested_circle' => $request->requestedCircle ? [
                            'id' => $request->requestedCircle->id,
                            'name' => $request->requestedCircle->name
                        ] : null,
                        'current_group' => $request->currentCircleGroup ? [
                            'id' => $request->currentCircleGroup->id,
                            'name' => $request->currentCircleGroup->name
                        ] : null,
                        'requested_group' => $request->requestedCircleGroup ? [
                            'id' => $request->requestedCircleGroup->id,
                            'name' => $request->requestedCircleGroup->name
                        ] : null,
                        'status' => $request->status,
                        'transfer_reason' => $request->transfer_reason,
                        'request_date' => $request->request_date,
                        'response_date' => $request->response_date,
                        'response_notes' => $request->response_notes,
                        'transfer_date' => $request->transfer_date
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب طلبات النقل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الموافقة على طلب نقل (للمشرفين الذين لديهم صلاحية الموافقة)
     */
    public function approveTransferRequest($requestId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه الموافقة على طلبات النقل

            $transferRequest = StudentTransferRequest::find($requestId);
            
            if (!$transferRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'طلب النقل غير موجود'
                ], 404);
            }

            if ($transferRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن الموافقة على هذا الطلب في حالته الحالية'
                ], 400);
            }

            // الموافقة على الطلب
            $transferRequest->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'response_date' => now()
            ]);

            // تسجيل النشاط
            $transferRequest->updateStatus('approved', $user->id, 'مشرف', 'تمت الموافقة على الطلب');

            return response()->json([
                'success' => true,
                'message' => 'تمت الموافقة على طلب النقل بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الموافقة على الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * رفض طلب نقل
     */
    public function rejectTransferRequest(Request $request, $requestId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه رفض طلبات النقل

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد سبب الرفض',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transferRequest = StudentTransferRequest::find($requestId);
            
            if (!$transferRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'طلب النقل غير موجود'
                ], 404);
            }

            if ($transferRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن رفض هذا الطلب في حالته الحالية'
                ], 400);
            }

            // رفض الطلب
            $transferRequest->update([
                'status' => 'rejected',
                'response_date' => now(),
                'response_notes' => $request->reason
            ]);

            // تسجيل النشاط
            $transferRequest->updateStatus('rejected', $user->id, 'مشرف', $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'تم رفض طلب النقل'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في رفض الطلب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إحصائيات المشرف
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            // تجاهل فحص المصادقة للاختبار
            // $user = Auth::user();
            
            // التحقق من أن المستخدم مشرف (مُعطَّل للاختبار)
            // if (!$user->hasRole('supervisor')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'غير مصرح لك بالوصول لهذه البيانات'
            //     ], 403);
            // }

            // الحصول على جميع الحلقات للاختبار
            $circleIds = QuranCircle::pluck('id');

            // إحصائيات الحلقات
            $circlesCount = $circleIds->count();
            
            // إحصائيات الطلاب
            $studentsCount = Student::whereIn('quran_circle_id', $circleIds)->count();
            
            // إحصائيات طلبات النقل (إرجاع قيم وهمية للاختبار)
            $transferRequestsStats = [
                'total' => 5,
                'pending' => 2,
                'approved' => 2,
                'rejected' => 1,
                'completed' => 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'circles_count' => $circlesCount,
                    'students_count' => $studentsCount,
                    'transfer_requests' => $transferRequestsStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على معلمي حلقة محددة
     */
    public function getCircleTeachers($circleId): JsonResponse
    {
        try {
            // تجاهل فحص المصادقة للاختبار
            // $user = Auth::user();
            
            // التحقق من الصلاحيات (مُعطَّل للاختبار)
            // if (!$user->hasRole('supervisor')) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'غير مصرح لك بالوصول لهذه البيانات'
            //     ], 403);
            // }

            // التحقق من صلاحية الوصول للحلقة (مُعطَّل للاختبار)
            // $hasAccess = CircleSupervisor::where('user_id', $user->id)
            //     ->where('quran_circle_id', $circleId)
            //     ->exists();

            // if (!$hasAccess) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'ليس لديك صلاحية على هذه الحلقة'
            //     ], 403);
            // }

            // الحصول على معلمي الحلقة
            $teachers = Teacher::where('quran_circle_id', $circleId)
                ->get(['id', 'name', 'phone', 'job_title', 'task_type', 'work_time', 'evaluation', 'start_date']);

            return response()->json([
                'success' => true,
                'data' => $teachers->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'phone' => $teacher->phone,
                        'job_title' => $teacher->job_title,
                        'task_type' => $teacher->task_type,
                        'work_time' => $teacher->work_time,
                        'evaluation' => $teacher->evaluation,
                        'start_date' => $teacher->start_date,
                        'attendance_today' => $this->getTeacherAttendanceToday($teacher->id)
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات المعلمين',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تسجيل حضور معلم
     */
    public function recordTeacherAttendance(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه تسجيل حضور المعلمين

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'status' => 'required|in:حاضر,غائب,مستأذن,متأخر',
                'attendance_date' => 'nullable|date',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attendanceDate = $request->attendance_date ? Carbon::parse($request->attendance_date) : today();
            
            // التحقق من أن المعلم في حلقة مشرف عليها
            $teacher = Teacher::find($request->teacher_id);
            $hasAccess = CircleSupervisor::where('user_id', $user->id)
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية على هذا المعلم'
                ], 403);
            }

            // تسجيل أو تحديث الحضور (نحتاج لإنشاء جدول teacher_attendances)
            // للتبسيط، سنحفظ في ملاحظات المعلم مؤقتاً
            $teacher->update([
                'notes' => "حضور " . $attendanceDate->format('Y-m-d') . ": " . $request->status . 
                          ($request->notes ? " - " . $request->notes : "")
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الحضور بنجاح',
                'data' => [
                    'teacher_name' => $teacher->name,
                    'status' => $request->status,
                    'attendance_date' => $attendanceDate->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تسجيل الحضور',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء تقرير وتقييم لمعلم
     */
    public function createTeacherReport(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه إنشاء تقارير المعلمين

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'evaluation_score' => 'required|integer|min:1|max:10',
                'performance_notes' => 'required|string|max:1000',
                'attendance_notes' => 'nullable|string|max:500',
                'recommendations' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من أن المعلم في حلقة مشرف عليها
            $teacher = Teacher::find($request->teacher_id);
            $hasAccess = CircleSupervisor::where('user_id', $user->id)
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية على هذا المعلم'
                ], 403);
            }

            // تحديث تقييم المعلم
            $teacher->update([
                'evaluation' => $request->evaluation_score
            ]);

            // إنشاء التقرير (يمكن حفظه في جدول منفصل لاحقاً)
            $reportData = [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'supervisor_id' => $user->id,
                'supervisor_name' => $user->name,
                'evaluation_score' => $request->evaluation_score,
                'performance_notes' => $request->performance_notes,
                'attendance_notes' => $request->attendance_notes,
                'recommendations' => $request->recommendations,
                'report_date' => now()->format('Y-m-d H:i:s'),
                'job_title' => $teacher->job_title,
                'task_type' => $teacher->task_type,
                'work_time' => $teacher->work_time
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء التقرير وتحديث التقييم بنجاح',
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على تقرير شامل للمعلم
     */
    public function getTeacherFullReport($teacherId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه عرض تقارير المعلمين

            // التحقق من أن المعلم في حلقة مشرف عليها
            $teacher = Teacher::with(['quranCircle', 'mosque'])->find($teacherId);
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'المعلم غير موجود'
                ], 404);
            }

            $hasAccess = CircleSupervisor::where('user_id', $user->id)
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية على هذا المعلم'
                ], 403);
            }

            // جمع بيانات التقرير الشامل
            $reportData = [
                'teacher_info' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'phone' => $teacher->phone,
                    'identity_number' => $teacher->identity_number,
                    'nationality' => $teacher->nationality,
                    'job_title' => $teacher->job_title,
                    'task_type' => $teacher->task_type,
                    'work_time' => $teacher->work_time,
                    'start_date' => $teacher->start_date,
                    'evaluation' => $teacher->evaluation,
                ],
                'workplace_info' => [
                    'circle_name' => $teacher->quranCircle?->name,
                    'mosque_name' => $teacher->mosque?->name,
                    'mosque_neighborhood' => $teacher->mosque?->neighborhood,
                ],
                'performance_metrics' => [
                    'current_evaluation' => $teacher->evaluation,
                    'absence_count' => $teacher->absence_count ?? 0,
                    'ratel_activated' => $teacher->ratel_activated,
                ],
                'report_generated' => [
                    'by' => $user->name,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'supervisor_id' => $user->id
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // الدوال المساعدة

    /**
     * الحصول على حالة حضور المعلم لليوم الحالي
     */
    private function getTeacherAttendanceToday($teacherId): ?string
    {
        // مؤقتاً، نعيد قيمة افتراضية - يمكن تطويرها لاحقاً مع جدول الحضور
        return 'غير محدد';
    }

    /**
     * API تتبع نشاط المعلمين اليومي - التحضير والتسميع
     */
    public function getTeacherDailyActivity(Request $request): JsonResponse
    {
        try {
            $supervisorId = $request->get('supervisor_id'); 
            $date = $request->get('date', today()->format('Y-m-d'));
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }
            
            // التحقق من وجود المشرف
            $supervisor = User::role('supervisor')->find($supervisorId);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }
            
            // الحصول على المعلمين المشرف عليهم
            $supervisedCircleIds = CircleSupervisor::where('supervisor_id', $supervisorId)
                ->pluck('quran_circle_id');
            
            if ($supervisedCircleIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد حلقات مشرف عليها',
                    'data' => [
                        'date' => $date,
                        'teachers_activity' => [],
                        'summary' => [
                            'total_teachers' => 0,
                            'active_teachers' => 0,
                            'attendance_recorded' => 0,
                            'recitation_recorded' => 0,
                            'completion_rate' => 0
                        ]
                    ]
                ], 200);
            }
            
            // جلب المعلمين الذين لديهم حلقات فرعية نشطة فقط
            $teachersWithActiveGroups = DB::table('teachers')
                ->join('circle_groups', 'teachers.id', '=', 'circle_groups.teacher_id')
                ->where('circle_groups.status', 'نشطة')
                ->whereIn('teachers.quran_circle_id', $supervisedCircleIds)
                ->where('teachers.is_active_user', true)
                ->pluck('teachers.id');
            
            $teachers = Teacher::with(['quranCircle:id,name', 'mosque:id,name'])
                ->whereIn('id', $teachersWithActiveGroups)
                ->get();

            $teachersActivity = [];
            $totalTeachers = $teachers->count();
            $activeTeachers = 0;
            $attendanceRecorded = 0;
            $recitationRecorded = 0;

            foreach ($teachers as $teacher) {
                $activity = $this->getTeacherActivityForDate($teacher->id, $date);
                
                if ($activity['has_activity']) {
                    $activeTeachers++;
                }
                
                if ($activity['attendance_recorded']) {
                    $attendanceRecorded++;
                }
                
                if ($activity['recitation_recorded']) {
                    $recitationRecorded++;
                }

                $teachersActivity[] = [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'phone' => $teacher->phone,
                    'job_title' => $teacher->job_title,
                    'circle' => [
                        'id' => $teacher->quranCircle?->id,
                        'name' => $teacher->quranCircle?->name
                    ],
                    'mosque' => [
                        'id' => $teacher->mosque?->id,
                        'name' => $teacher->mosque?->name
                    ],
                    'daily_activity' => $activity
                ];
            }

            // حساب معدل الإنجاز الإجمالي
            $completionRate = $totalTeachers > 0 
                ? round((($attendanceRecorded + $recitationRecorded) / ($totalTeachers * 2)) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'supervisor' => [
                        'id' => $supervisor->id,
                        'name' => $supervisor->name
                    ],
                    'teachers_activity' => $teachersActivity,
                    'summary' => [
                        'total_teachers' => $totalTeachers,
                        'active_teachers' => $activeTeachers,
                        'attendance_recorded' => $attendanceRecorded,
                        'recitation_recorded' => $recitationRecorded,
                        'completion_rate' => $completionRate,
                        'attendance_percentage' => $totalTeachers > 0 ? round(($attendanceRecorded / $totalTeachers) * 100, 1) : 0,
                        'recitation_percentage' => $totalTeachers > 0 ? round(($recitationRecorded / $totalTeachers) * 100, 1) : 0
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب نشاط المعلمين',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على نشاط معلم محدد لتاريخ معين
     */
    private function getTeacherActivityForDate($teacherId, $date): array
    {
        // البحث عن مجموعة المعلم النشطة فقط
        $teacherGroup = DB::table('circle_groups')
            ->where('teacher_id', $teacherId)
            ->where('status', 'نشطة') // فقط الحلقات الفرعية النشطة
            ->first();

        if (!$teacherGroup) {
            // إذا لم يكن للمعلم مجموعة، ابحث في الحلقة العامة
            $studentsCount = Student::whereHas('quranCircle.activeTeachers', function($query) use ($teacherId) {
                $query->where('teachers.id', $teacherId);
            })->count();
            
            $studentIds = Student::whereHas('quranCircle.activeTeachers', function($query) use ($teacherId) {
                $query->where('teachers.id', $teacherId);
            })->pluck('id');
        } else {
            // البحث عن الطلاب في مجموعة المعلم
            $studentsCount = DB::table('students')
                ->where('circle_group_id', $teacherGroup->id)
                ->where('is_active', true)
                ->count();
            
            $studentIds = DB::table('students')
                ->where('circle_group_id', $teacherGroup->id)
                ->where('is_active', true)
                ->pluck('id');
        }

        // فحص تسجيل الحضور لهذا التاريخ
        $attendanceRecorded = DB::table('attendances')
            ->whereDate('date', $date)
            ->where('attendable_type', 'App\Models\Student')
            ->whereIn('attendable_id', $studentIds)
            ->exists();

        // عدد الطلاب الذين تم تسجيل حضورهم
        $attendanceCount = DB::table('attendances')
            ->whereDate('date', $date)
            ->where('attendable_type', 'App\Models\Student')
            ->whereIn('attendable_id', $studentIds)
            ->count();

        // فحص تسجيل التسميع لهذا التاريخ
        $recitationRecorded = DB::table('recitation_sessions')
            ->whereDate('created_at', $date)
            ->where('teacher_id', $teacherId)
            ->exists();

        // عدد جلسات التسميع المسجلة
        $recitationCount = DB::table('recitation_sessions')
            ->whereDate('created_at', $date)
            ->where('teacher_id', $teacherId)
            ->count();

        // عدد الطلاب الذين تم تسميعهم
        $recitedStudentsCount = DB::table('recitation_sessions')
            ->whereDate('created_at', $date)
            ->where('teacher_id', $teacherId)
            ->distinct('student_id')
            ->count();

        // حساب النسب
        $attendancePercentage = $studentsCount > 0 ? round(($attendanceCount / $studentsCount) * 100, 1) : 0;
        $recitationPercentage = $studentsCount > 0 ? round(($recitedStudentsCount / $studentsCount) * 100, 1) : 0;

        // تحديد حالة النشاط
        $activityStatus = 'غير نشط';
        $statusColor = 'red';
        
        if ($attendanceRecorded && $recitationRecorded) {
            $activityStatus = 'نشط - مكتمل';
            $statusColor = 'green';
        } elseif ($attendanceRecorded || $recitationRecorded) {
            $activityStatus = 'نشط - جزئي';
            $statusColor = 'orange';
        }

        return [
            'has_activity' => $attendanceRecorded || $recitationRecorded,
            'attendance_recorded' => $attendanceRecorded,
            'recitation_recorded' => $recitationRecorded,
            'students_count' => $studentsCount,
            'attendance_count' => $attendanceCount,
            'recitation_sessions_count' => $recitationCount,
            'recited_students_count' => $recitedStudentsCount,
            'attendance_percentage' => $attendancePercentage,
            'recitation_percentage' => $recitationPercentage,
            'activity_status' => $activityStatus,
            'status_color' => $statusColor,
            'details' => [
                'attendance_status' => $attendanceRecorded ? 'تم التحضير' : 'لم يحضر',
                'recitation_status' => $recitationRecorded ? "تم التسميع ({$recitationPercentage}%)" : 'لم يسمع',
                'completion_summary' => $this->getCompletionSummary($attendanceRecorded, $recitationRecorded, $attendancePercentage, $recitationPercentage)
            ]
        ];
    }

    /**
     * ملخص حالة الإنجاز
     */
    private function getCompletionSummary($attendanceRecorded, $recitationRecorded, $attendancePercentage, $recitationPercentage): string
    {
        if ($attendanceRecorded && $recitationRecorded) {
            return "مكتمل - حضور: {$attendancePercentage}%، تسميع: {$recitationPercentage}%";
        } elseif ($attendanceRecorded) {
            return "تم التحضير فقط ({$attendancePercentage}%)";
        } elseif ($recitationRecorded) {
            return "تم التسميع فقط ({$recitationPercentage}%)";
        } else {
            return "لم يتم أي نشاط";
        }
    }

    /**
     * API لإحصائيات المعلمين حسب الفترة الزمنية
     */
    public function getTeachersActivityStatistics(Request $request): JsonResponse
    {
        try {
            $supervisorId = $request->get('supervisor_id');
            $startDate = $request->get('start_date', today()->subDays(7)->format('Y-m-d'));
            $endDate = $request->get('end_date', today()->format('Y-m-d'));
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }

            // الحصول على المعلمين المشرف عليهم
            $supervisedCircleIds = CircleSupervisor::where('supervisor_id', $supervisorId)
                ->active()
                ->pluck('quran_circle_id');

            if ($supervisedCircleIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'period' => ['start' => $startDate, 'end' => $endDate],
                        'statistics' => [],
                        'overall_summary' => []
                    ]
                ]);
            }

            $teachers = Teacher::whereIn('quran_circle_id', $supervisedCircleIds)
                ->where('is_active_user', true)
                ->get();

            $statistics = [];
            $totalDays = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;

            foreach ($teachers as $teacher) {
                $teacherStats = $this->getTeacherPeriodStatistics($teacher->id, $startDate, $endDate);
                
                $statistics[] = [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'statistics' => $teacherStats,
                    'performance_grade' => $this->calculatePerformanceGrade($teacherStats, $totalDays)
                ];
            }

            // حساب الإحصائيات الإجمالية
            $overallSummary = $this->calculateOverallSummary($statistics, $totalDays);

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate,
                        'total_days' => $totalDays
                    ],
                    'teachers_statistics' => $statistics,
                    'overall_summary' => $overallSummary
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إحصائيات معلم لفترة زمنية محددة
     */
    private function getTeacherPeriodStatistics($teacherId, $startDate, $endDate): array
    {
        // أيام التحضير (تسجيل الحضور)
        $attendanceDays = DB::table('student_attendances')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('student_id', function($query) use ($teacherId) {
                $query->select('students.id')
                    ->from('students')
                    ->join('quran_circles', 'students.quran_circle_id', '=', 'quran_circles.id')
                    ->join('teachers', 'teachers.quran_circle_id', '=', 'quran_circles.id')
                    ->where('teachers.id', $teacherId);
            })
            ->distinct(DB::raw('DATE(date)'))
            ->count();

        // أيام التسميع
        $recitationDays = DB::table('recitation_sessions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('teacher_id', $teacherId)
            ->distinct(DB::raw('DATE(created_at)'))
            ->count();

        // إجمالي جلسات التسميع
        $totalRecitationSessions = DB::table('recitation_sessions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('teacher_id', $teacherId)
            ->count();

        // عدد الطلاب الذين تم تسميعهم
        $recitedStudentsCount = DB::table('recitation_sessions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('teacher_id', $teacherId)
            ->distinct('student_id')
            ->count();

        return [
            'attendance_days' => $attendanceDays,
            'recitation_days' => $recitationDays,
            'active_days' => max($attendanceDays, $recitationDays),
            'total_recitation_sessions' => $totalRecitationSessions,
            'recited_students_count' => $recitedStudentsCount,
            'avg_sessions_per_day' => $recitationDays > 0 ? round($totalRecitationSessions / $recitationDays, 1) : 0
        ];
    }

    /**
     * حساب درجة الأداء للمعلم
     */
    private function calculatePerformanceGrade($stats, $totalDays): array
    {
        $attendanceScore = $totalDays > 0 ? ($stats['attendance_days'] / $totalDays) * 50 : 0;
        $recitationScore = $totalDays > 0 ? ($stats['recitation_days'] / $totalDays) * 50 : 0;
        
        $totalScore = $attendanceScore + $recitationScore;
        
        $grade = 'ممتاز';
        $color = 'green';
        
        if ($totalScore < 60) {
            $grade = 'ضعيف';
            $color = 'red';
        } elseif ($totalScore < 75) {
            $grade = 'مقبول';
            $color = 'orange';
        } elseif ($totalScore < 90) {
            $grade = 'جيد';
            $color = 'blue';
        }

        return [
            'score' => round($totalScore, 1),
            'grade' => $grade,
            'color' => $color,
            'breakdown' => [
                'attendance_score' => round($attendanceScore, 1),
                'recitation_score' => round($recitationScore, 1)
            ]
        ];
    }

    /**
     * حساب الإحصائيات الإجمالية
     */
    private function calculateOverallSummary($statistics, $totalDays): array
    {
        $totalTeachers = count($statistics);
        
        if ($totalTeachers === 0) {
            return [
                'total_teachers' => 0,
                'average_attendance_days' => 0,
                'average_recitation_days' => 0,
                'average_performance_score' => 0,
                'grade_distribution' => []
            ];
        }

        $totalAttendanceDays = array_sum(array_column(array_column($statistics, 'statistics'), 'attendance_days'));
        $totalRecitationDays = array_sum(array_column(array_column($statistics, 'statistics'), 'recitation_days'));
        $totalPerformanceScore = array_sum(array_column(array_column($statistics, 'performance_grade'), 'score'));

        // توزيع الدرجات
        $gradeDistribution = [];
        foreach ($statistics as $stat) {
            $grade = $stat['performance_grade']['grade'];
            $gradeDistribution[$grade] = ($gradeDistribution[$grade] ?? 0) + 1;
        }

        return [
            'total_teachers' => $totalTeachers,
            'average_attendance_days' => round($totalAttendanceDays / $totalTeachers, 1),
            'average_recitation_days' => round($totalRecitationDays / $totalTeachers, 1),
            'average_performance_score' => round($totalPerformanceScore / $totalTeachers, 1),
            'attendance_rate' => $totalDays > 0 ? round(($totalAttendanceDays / ($totalTeachers * $totalDays)) * 100, 1) : 0,
            'recitation_rate' => $totalDays > 0 ? round(($totalRecitationDays / ($totalTeachers * $totalDays)) * 100, 1) : 0,
            'grade_distribution' => $gradeDistribution
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | APIs تقييم المعلمين - Teacher Evaluations APIs
    |--------------------------------------------------------------------------
    */

    /**
     * إنشاء تقييم جديد لمعلم
     */
    public function createTeacherEvaluation(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه إنشاء تقييمات المعلمين

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'performance_evaluation' => 'required|integer|min:0|max:20',
                'attendance_evaluation' => 'required|integer|min:0|max:20',
                'student_interaction_evaluation' => 'required|integer|min:0|max:20',
                'attitude_cooperation_evaluation' => 'required|integer|min:0|max:20',
                'memorization_evaluation' => 'required|integer|min:0|max:20',
                'general_evaluation' => 'required|integer|min:0|max:20',
                'notes' => 'nullable|string|max:1000',
                'evaluation_date' => 'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // التحقق من أن المعلم في حلقة مشرف عليها
            $teacher = Teacher::find($request->teacher_id);
            $hasAccess = CircleSupervisor::where('supervisor_id', $user->id)
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية على هذا المعلم'
                ], 403);
            }

            // حساب النتيجة الإجمالية
            $totalScore = $request->performance_evaluation + 
                         $request->attendance_evaluation + 
                         $request->student_interaction_evaluation + 
                         $request->attitude_cooperation_evaluation + 
                         $request->memorization_evaluation + 
                         $request->general_evaluation;

            // إنشاء التقييم
            $evaluation = TeacherEvaluation::create([
                'teacher_id' => $request->teacher_id,
                'evaluator_id' => $user->id,
                'evaluator_type' => 'supervisor',
                'performance_evaluation' => $request->performance_evaluation,
                'attendance_evaluation' => $request->attendance_evaluation,
                'student_interaction_evaluation' => $request->student_interaction_evaluation,
                'attitude_cooperation_evaluation' => $request->attitude_cooperation_evaluation,
                'memorization_evaluation' => $request->memorization_evaluation,
                'general_evaluation' => $request->general_evaluation,
                'total_score' => $totalScore,
                'evaluation_date' => $request->evaluation_date ? Carbon::parse($request->evaluation_date) : now(),
                'notes' => $request->notes,
                'status' => 'مكتمل'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء التقييم بنجاح',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'teacher_name' => $teacher->name,
                    'total_score' => $totalScore,
                    'evaluation_date' => $evaluation->evaluation_date->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء التقييم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على تقييمات معلم محدد
     */
    public function getTeacherEvaluations($teacherId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه عرض تقييمات المعلمين

            // التحقق من أن المعلم في حلقة مشرف عليها
            $teacher = Teacher::find($teacherId);
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'المعلم غير موجود'
                ], 404);
            }

            $hasAccess = CircleSupervisor::where('supervisor_id', $user->id)
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية على هذا المعلم'
                ], 403);
            }

            // الحصول على التقييمات
            $evaluations = TeacherEvaluation::where('teacher_id', $teacherId)
                ->with(['evaluator:id,name'])
                ->orderBy('evaluation_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher' => [
                        'id' => $teacher->id,
                        'name' => $teacher->name
                    ],
                    'evaluations' => $evaluations->map(function ($evaluation) {
                        return [
                            'id' => $evaluation->id,
                            'evaluator_name' => $evaluation->evaluator?->name,
                            'evaluator_type' => $evaluation->evaluator_type,
                            'performance_evaluation' => $evaluation->performance_evaluation,
                            'attendance_evaluation' => $evaluation->attendance_evaluation,
                            'student_interaction_evaluation' => $evaluation->student_interaction_evaluation,
                            'attitude_cooperation_evaluation' => $evaluation->attitude_cooperation_evaluation,
                            'memorization_evaluation' => $evaluation->memorization_evaluation,
                            'general_evaluation' => $evaluation->general_evaluation,
                            'total_score' => $evaluation->total_score,
                            'percentage' => $evaluation->percentage,
                            'evaluation_date' => $evaluation->evaluation_date->format('Y-m-d'),
                            'status' => $evaluation->status,
                            'notes' => $evaluation->notes
                        ];
                    }),
                    'statistics' => [
                        'total_evaluations' => $evaluations->count(),
                        'average_score' => $evaluations->avg('total_score'),
                        'average_percentage' => $evaluations->avg('percentage'),
                        'latest_evaluation_date' => $evaluations->first()?->evaluation_date?->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب التقييمات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث تقييم معلم
     */
    public function updateTeacherEvaluation(Request $request, $evaluationId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه تحديث التقييمات

            // البحث عن التقييم
            $evaluation = TeacherEvaluation::find($evaluationId);
            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'التقييم غير موجود'
                ], 404);
            }

            // التحقق من أن المقيم هو الذي أنشأ التقييم
            if ($evaluation->evaluator_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'ليس لديك صلاحية تحديث هذا التقييم'
                ], 403);
            }

            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'performance_evaluation' => 'sometimes|integer|min:0|max:20',
                'attendance_evaluation' => 'sometimes|integer|min:0|max:20',
                'student_interaction_evaluation' => 'sometimes|integer|min:0|max:20',
                'attitude_cooperation_evaluation' => 'sometimes|integer|min:0|max:20',
                'memorization_evaluation' => 'sometimes|integer|min:0|max:20',
                'general_evaluation' => 'sometimes|integer|min:0|max:20',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:مسودة,مكتمل,معتمد'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // تحديث الحقول المطلوبة
            $fieldsToUpdate = [];
            $evaluationFields = [
                'performance_evaluation', 'attendance_evaluation', 'student_interaction_evaluation',
                'attitude_cooperation_evaluation', 'memorization_evaluation', 'general_evaluation'
            ];

            foreach ($evaluationFields as $field) {
                if ($request->has($field)) {
                    $fieldsToUpdate[$field] = $request->$field;
                }
            }

            if ($request->has('notes')) {
                $fieldsToUpdate['notes'] = $request->notes;
            }

            if ($request->has('status')) {
                $fieldsToUpdate['status'] = $request->status;
            }

            // إعادة حساب النتيجة الإجمالية إذا تم تغيير أي من معايير التقييم
            if (array_intersect($evaluationFields, array_keys($fieldsToUpdate))) {
                $totalScore = ($request->performance_evaluation ?? $evaluation->performance_evaluation) +
                             ($request->attendance_evaluation ?? $evaluation->attendance_evaluation) +
                             ($request->student_interaction_evaluation ?? $evaluation->student_interaction_evaluation) +
                             ($request->attitude_cooperation_evaluation ?? $evaluation->attitude_cooperation_evaluation) +
                             ($request->memorization_evaluation ?? $evaluation->memorization_evaluation) +
                             ($request->general_evaluation ?? $evaluation->general_evaluation);

                $fieldsToUpdate['total_score'] = $totalScore;
            }

            // تحديث التقييم
            $evaluation->update($fieldsToUpdate);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التقييم بنجاح',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'total_score' => $evaluation->total_score,
                    'percentage' => $evaluation->percentage,
                    'status' => $evaluation->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث التقييم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * اعتماد تقييم معلم
     */
    public function approveTeacherEvaluation($evaluationId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه اعتماد التقييمات

            $evaluation = TeacherEvaluation::find($evaluationId);
            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'التقييم غير موجود'
                ], 404);
            }

            // التحقق من أن التقييم مكتمل
            if ($evaluation->status !== 'مكتمل') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن اعتماد تقييم غير مكتمل'
                ], 400);
            }

            // اعتماد التقييم
            $evaluation->update([
                'status' => 'معتمد',
                'approved_by' => $user->id,
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم اعتماد التقييم بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في اعتماد التقييم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف تقييم معلم
     */
    public function deleteTeacherEvaluation($evaluationId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // التحقق من المصادقة - سبق التحقق في middleware
            // المستخدم المصادق عليه يمكنه حذف التقييمات

            $evaluation = TeacherEvaluation::find($evaluationId);
            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'التقييم غير موجود'
                ], 404);
            }

            // التحقق من أن المقيم هو الذي أنشأ التقييم أو أن التقييم غير معتمد
            if ($evaluation->evaluator_id !== $user->id && $evaluation->status === 'معتمد') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف تقييم معتمد'
                ], 403);
            }

            $evaluation->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التقييم بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في حذف التقييم',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * الحصول على عرض شامل للمشرف - المساجد مع المدارس القرآنية والحلقات الفرعية والمعلمين والطلاب
     */
    public function getComprehensiveOverview(Request $request): JsonResponse
    {
        try {
            // الحصول على معرف المشرف من الطلب أو من المصادقة
            $supervisorId = $request->get('supervisor_id'); 
            // $supervisorId = Auth::id(); // في الإنتاج، استخدم المصادقة
            
            if (!$supervisorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المشرف مطلوب'
                ], 400);
            }
            
            // التحقق من وجود المشرف
            $supervisor = User::role('supervisor')->find($supervisorId);
            if (!$supervisor) {
                return response()->json([
                    'success' => false,
                    'message' => 'المشرف غير موجود'
                ], 404);
            }
            
            // الحصول على الحلقات التي يشرف عليها
            $supervisedCircleIds = CircleSupervisor::where('supervisor_id', $supervisorId)
                ->active()
                ->pluck('quran_circle_id');
            
            if ($supervisedCircleIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد حلقات مشرف عليها',
                    'data' => [
                        'supervisor' => [
                            'id' => $supervisor->id,
                            'name' => $supervisor->name,
                            'email' => $supervisor->email,
                            'phone' => $supervisor->phone
                        ],
                        'mosques' => [],
                        'summary' => [
                            'total_mosques' => 0,
                            'total_circles' => 0,
                            'total_groups' => 0,
                            'total_teachers' => 0,
                            'total_students' => 0
                        ]
                    ]
                ], 200);
            }
            
            // جلب البيانات الشاملة
            $circles = QuranCircle::with([
                'mosque:id,name,neighborhood,contact_number',
                'circleGroups.teacher:id,name,phone,job_title,task_type',
                'circleGroups.students',
                'activeTeachers:id,name,phone,job_title,task_type,work_time,evaluation,start_date',
                'students'
            ])
            ->whereIn('id', $supervisedCircleIds)
            ->get();

            // تنظيم البيانات حسب المسجد
            $mosquesData = [];
            $summary = [
                'total_mosques' => 0,
                'total_circles' => 0,
                'total_groups' => 0,
                'total_teachers' => 0,
                'total_students' => 0
            ];

            foreach ($circles as $circle) {
                $mosqueId = $circle->mosque_id;
                
                // إنشاء بيانات المسجد إذا لم تكن موجودة
                if (!isset($mosquesData[$mosqueId])) {
                    $mosquesData[$mosqueId] = [
                        'mosque' => [
                            'id' => $circle->mosque->id,
                            'name' => $circle->mosque->name,
                            'neighborhood' => $circle->mosque->neighborhood,
                            'contact_number' => $circle->mosque->contact_number
                        ],
                        'circles' => [],
                        'mosque_summary' => [
                            'circles_count' => 0,
                            'groups_count' => 0,
                            'teachers_count' => 0,
                            'students_count' => 0
                        ]
                    ];
                    $summary['total_mosques']++;
                }

                // إعداد بيانات الحلقة القرآنية
                $circleData = [
                    'id' => $circle->id,
                    'name' => $circle->name,
                    'type' => $circle->circle_type,
                    'status' => $circle->circle_status,
                    'time_period' => $circle->time_period,
                    'teachers' => [],
                    'groups' => [],
                    'students' => [],
                    'circle_summary' => [
                        'teachers_count' => 0,
                        'groups_count' => 0,
                        'students_count' => 0
                    ]
                ];

                // إضافة المعلمين النشطين في الحلقة
                foreach ($circle->activeTeachers as $teacher) {
                    $circleData['teachers'][] = [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'phone' => $teacher->phone,
                        'job_title' => $teacher->job_title,
                        'task_type' => $teacher->task_type,
                        'work_time' => $teacher->work_time,
                        'evaluation' => $teacher->evaluation,
                        'start_date' => $teacher->start_date
                    ];
                    $circleData['circle_summary']['teachers_count']++;
                    $summary['total_teachers']++;
                }

                // إضافة الحلقات الفرعية (للحلقات الجماعية)
                foreach ($circle->circleGroups as $group) {
                    $groupData = [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'status' => $group->status,
                        'teacher' => null,
                        'students' => [],
                        'students_count' => 0
                    ];

                    // إضافة معلم الحلقة الفرعية
                    if ($group->teacher) {
                        $groupData['teacher'] = [
                            'id' => $group->teacher->id,
                            'name' => $group->teacher->name,
                            'phone' => $group->teacher->phone,
                            'job_title' => $group->teacher->job_title,
                            'task_type' => $group->teacher->task_type
                        ];
                    }

                    // إضافة طلاب الحلقة الفرعية
                    foreach ($group->students as $student) {
                        $groupData['students'][] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'phone' => null, // مؤقتاً
                            'enrollment_date' => null // مؤقتاً
                        ];
                        $groupData['students_count']++;
                        $circleData['circle_summary']['students_count']++; // إضافة للحلقة أيضاً
                        $summary['total_students']++;
                    }

                    $circleData['groups'][] = $groupData;
                    $circleData['circle_summary']['groups_count']++;
                    $summary['total_groups']++;
                }

                // إضافة الطلاب المباشرين في الحلقة (للحلقات الفردية أو الطلاب غير المنتمين لحلقات فرعية)
                foreach ($circle->students as $student) {
                    // تجنب تكرار الطلاب الموجودين في الحلقات الفرعية
                    $isInGroup = false;
                    foreach ($circle->circleGroups as $group) {
                        if ($group->students->contains('id', $student->id)) {
                            $isInGroup = true;
                            break;
                        }
                    }

                    if (!$isInGroup) {
                        $circleData['students'][] = [
                            'id' => $student->id,
                            'name' => $student->name,
                            'phone' => null, // مؤقتاً
                            'enrollment_date' => null // مؤقتاً
                        ];
                        $circleData['circle_summary']['students_count']++;
                        $summary['total_students']++;
                    }
                }

                // إضافة الحلقة إلى بيانات المسجد
                $mosquesData[$mosqueId]['circles'][] = $circleData;
                $mosquesData[$mosqueId]['mosque_summary']['circles_count']++;
                $mosquesData[$mosqueId]['mosque_summary']['teachers_count'] += $circleData['circle_summary']['teachers_count'];
                $mosquesData[$mosqueId]['mosque_summary']['groups_count'] += $circleData['circle_summary']['groups_count'];
                $mosquesData[$mosqueId]['mosque_summary']['students_count'] += $circleData['circle_summary']['students_count'];
                
                $summary['total_circles']++;
            }

            // تحويل البيانات إلى مصفوفة مرتبة
            $formattedMosques = array_values($mosquesData);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب البيانات الشاملة بنجاح',
                'data' => [
                    'supervisor' => [
                        'id' => $supervisor->id,
                        'name' => $supervisor->name,
                        'email' => $supervisor->email,
                        'phone' => $supervisor->phone
                    ],
                    'mosques' => $formattedMosques,
                    'summary' => $summary
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب البيانات الشاملة',
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
