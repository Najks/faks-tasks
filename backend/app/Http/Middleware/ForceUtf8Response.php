<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Http\JsonResponse;

class ForceUtf8Response
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            $response->setEncodingOptions(
                $response->getEncodingOptions() | JSON_UNESCAPED_UNICODE
            );

            $response->setData($data);

            $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if ($contentType !== '' && !str_contains(strtolower($contentType), 'charset=')) {
            if (str_starts_with(strtolower($contentType), 'text/')
                || str_contains(strtolower($contentType), 'application/json')
                || str_contains(strtolower($contentType), 'application/xml')
            ) {
                $response->headers->set('Content-Type', $contentType . '; charset=UTF-8');
            }
        }

        return $response;
    }
}
