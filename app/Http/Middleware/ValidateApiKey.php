<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        // Tolak jika header tidak ada
        if (! $apiKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing API Key: X-IAE-KEY header is required',
                'errors'  => null,
            ], 401);
        }

        // Terima: NIM numerik (9-15 digit) ATAU key yang terdaftar di .env
        $isNim = preg_match('/^\d{9,15}$/', $apiKey);
        $validKeys = array_filter([
            config('app.api_key'),
            env('API_KEY'),
            env('IAE_API_KEY'),
            '102022400084',
        ]);
        $isRegistered = in_array($apiKey, $validKeys);

        if (! $isNim && ! $isRegistered) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid API Key',
                'errors'  => null,
            ], 401);
        }

        return $next($request);
    }
}
