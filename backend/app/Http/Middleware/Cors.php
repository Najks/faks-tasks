<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->withCorsHeaders(response()->noContent(), $request);
        }

        $response = $next($request);

        return $this->withCorsHeaders($response, $request);
    }

    private function withCorsHeaders(Response $response, Request $request): Response
    {
        $origin = $request->headers->get('origin');
        $allowedOrigins = $this->allowedOrigins();

        if (!$origin || !$this->isOriginAllowed($origin, $allowedOrigins)) {
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', $this->formatList(config('cors.allowed_methods')));
        $response->headers->set('Access-Control-Allow-Headers', $this->formatList(config('cors.allowed_headers')));
        $response->headers->set('Access-Control-Expose-Headers', $this->formatList(config('cors.exposed_headers')));
        $response->headers->set('Access-Control-Allow-Credentials', config('cors.supports_credentials') ? 'true' : 'false');
        $response->headers->set('Access-Control-Max-Age', (string) (config('cors.max_age') ?? 0));
        $response->headers->set('Vary', 'Origin');

        return $response;
    }

    private function allowedOrigins(): array
    {
        $origins = config('cors.allowed_origins', []);
        return array_values(array_filter(array_map('trim', (array) $origins)));
    }

    private function isOriginAllowed(?string $origin, array $allowedOrigins): bool
    {
        if (!$origin) {
            return false;
        }

        if (in_array('*', $allowedOrigins, true)) {
            return true;
        }

        return in_array($origin, $allowedOrigins, true);
    }

    private function formatList(array|string|null $values): string
    {
        if (!$values) {
            return '';
        }

        if (is_string($values)) {
            return strtoupper($values);
        }

        return strtoupper(implode(', ', array_filter(array_map('trim', $values))));
    }
}

