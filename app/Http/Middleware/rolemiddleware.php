<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class rolemiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
       try {
        $payload = JWTAuth::parseToken()->getPayload();
        $userRoleId = $payload->get('role');


        $roleMap = [
           2 => 'creator',
           3 => 'owner'
        ];

        $userRole = $roleMap[$userRoleId] ?? null;
        

        if (!in_array($userRole, explode('|', $role)))
        {
            return response()->json([], 403);
        }
        
        

        return $next($request);

       } catch(\Exception $e){
           return response()->json([], 401);
       }
    }
}
