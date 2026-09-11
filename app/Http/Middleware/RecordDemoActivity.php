<?php

namespace App\Http\Middleware;

use App\Demo\DemoActivityRecorder;
use App\Demo\DemoContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leaves a trace of every meaningful thing a demo visitor does.
 *
 * Appended to the `web` group, so it is the innermost wrapper: by the time its
 * half of the response runs, Authenticate has resolved the user and route
 * model binding has resolved the parameters — both of which the recorder
 * needs, and neither of which exists at StartDemoSession's end of the stack.
 *
 * It does nothing at all outside a demo, which is every production request.
 */
class RecordDemoActivity
{
    public function __construct(
        private readonly DemoContext $context,
        private readonly DemoActivityRecorder $recorder,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->context->isDemo() && $this->context->sessionId() !== null) {
            // Recorded here rather than in terminate() so the write happens
            // while the demo session store is still open — the collapse window
            // is kept in it, and terminate() runs after it has been saved.
            $this->recorder->request($request, $response);
        }

        return $response;
    }
}
