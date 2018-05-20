<?php

namespace Hindsight\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Decahedron\StickyLogging\StickyContext;

class HindsightRequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        StickyContext::stack('hindsight')->add('actor_id', function () {
            return \Auth::id();
        });
        StickyContext::stack('hindsight')->add('request', [
            'id' => $requestId = Uuid::uuid4()->toString(),
            'ip' => $request->getClientIp(),
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $this->filterHeaders($request->headers->all()),
        ]);

        Log::debug('Request initiated', array_merge(array_filter([
            'data' => $request->except(config('hindsight.blacklist.fields', [])),
        ]), ['code' => 'hindsight.request-finished']));

        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        $data = $response->getContent();

        if ($jsonData = json_decode($data, JSON_OBJECT_AS_ARRAY)) {
            $data = $jsonData;

            if (config('hindsight.attach_request_id_to_response')) {
                $data['meta'] = array_merge($data['meta'] ?? [], ['request_id' => $requestId]);

                $response->setContent(json_encode($data));
            }
        }

        Log::debug('Request finished, sending response', [
            'response' => [
                'status' => $response->getStatusCode(),
                'body' => $data,
                'headers' => $this->filterHeaders($response->headers->all()),
            ],
            'code' => 'hindsight.request-finished',
        ]);

        return $response;
    }

    /**
     * Filter the headers to remove all blacklisted ones.
     *
     * @param $headers
     * @return array
     */
    private function filterHeaders($headers)
    {
        return array_filter($headers, function ($header) {
            return ! in_array(strtolower($header), array_map('strtolower', config('hindsight.blacklist.headers')));
        }, ARRAY_FILTER_USE_KEY);
    }
}
