<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Throwable;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        try {
            $response = $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token has expired. Please login again.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token is invalid. Please login again.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token error. Please login again.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (Throwable $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], $e instanceof \Illuminate\Http\Exception\HttpResponseException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            throw $e;
        }

        if ($request->expectsJson() && !($response instanceof JsonResponse)) {
            // Streamed responses (e.g. CSV exports) must be returned as-is and
            // must not be forced to a JSON content type.
            if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                return $response;
            }

            $contentType = $response->headers->get('Content-Type');
            $statusCode = $response->getStatusCode();

            if ($contentType && str_contains($contentType, 'text/html') && in_array($statusCode, [401, 403, 419, 500], true)) {
                $message = match ($statusCode) {
                    401 => 'Unauthorized. Please login again.',
                    403 => 'Forbidden. Please login again.',
                    419 => 'Session expired. Please login again.',
                    default => 'An error occurred while processing the request.',
                };

                return response()->json([
                    'status'  => 'error',
                    'message' => $message,
                ], $statusCode);
            }

            $response->header('Content-Type', 'application/json');
        }

        return $response;
    }
}
