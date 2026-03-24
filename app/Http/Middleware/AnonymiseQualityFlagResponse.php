<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnonymiseQualityFlagResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process JSON responses
        if (! $response instanceof JsonResponse) {
            return $response;
        }

        // Admin roles see full data — skip anonymisation
        $user = $request->user();
        if ($user && $user->hasRole(['network_admin', 'system'])) {
            return $response;
        }

        $data = $response->getData(true);
        $data = $this->anonymise($data);
        $response->setData($data);

        return $response;
    }

    private function anonymise(array $data): array
    {
        foreach ($data as $key => &$value) {
            if ($key === 'facility_id') {
                unset($data[$key]);
            } elseif ($key === 'ulid' && is_string($value)) {
                $data['anonymous_reporter_token'] = hash('sha256', $value);
            } elseif (is_array($value)) {
                $value = $this->anonymise($value);
            }
        }

        return $data;
    }
}
