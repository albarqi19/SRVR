<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiIds
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $parameter = 'id'): Response
    {
        $id = $request->route($parameter);
        
        // التحقق من أن المعرف رقم صحيح
        if (!is_numeric($id) || $id <= 0) {
            return response()->json([
                'نجح' => false,
                'رسالة' => 'معرف غير صحيح، يجب أن يكون رقم صحيح أكبر من صفر',
                'خطأ' => "المعرف المرسل: {$id}"
            ], 400);
        }

        return $next($request);
    }
}
