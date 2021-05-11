<?php

namespace App\Http\Middleware;

use App\Log;
use Closure;

class Login
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle(object $request, Closure $next)
    {
        if ($this->checkCredentials($request)) {
            $log = $this->addLog($request, true);
            $request->attributes->add(['log' => $log]);

            return $next($request);
        }

        $log = $this->addLog($request, false);

        return response((string) json_encode([
            'result' => 'error',
            'datas' => ['Erreur : token ou IP non-autorisés'],
        ]), 511);
    }

    private function checkCredentials(object $request): bool
    {
        $tokens = array_map(function ($appli) {
            return $appli['token'];
        }, config('whitelist')['applications']);

        $ips = array_map(function ($computer) {
            return $computer['ip'];
        }, config('whitelist')['computers']);

        return \in_array($request->token, $tokens) && \in_array($request->ip(), $ips);
    }

    private function addLog(object $request, bool $login_result): Log
    {
        $log = new Log([
            'request' => json_encode($request->all()),
            'ip' => $request->ip(),
            'login_result' => $login_result,
        ]);

        $log->save();

        return $log;
    }
}
