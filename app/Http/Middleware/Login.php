<?php

namespace App\Http\Middleware;

use Closure;
use App\Log;

class Login
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return response()->json([$request->ip()]);
        // dd($request->ip());   
        if ($this->checkCredentials($request)) {
            $this->addLog($request, true);
            return $next($request);
        }
        else {
            $this->addLog($request, false);
            return response()->json(["Erreur : token ou IP non-autorisés"]);
        }
    }

    private function checkCredentials($request){
        $tokens = array_map(function($appli){
            return $appli['token'];
        }, config('whitelist')['applications']);

        $ips = array_map(function($computer){
            return $computer['ip'];
        }, config('whitelist')['computers']);
        
        return in_array($request->token, $tokens) && in_array($request->ip(), $ips);
    }

    private function addLog($request, $login_result){
        $log = new Log([
            'request' => json_encode($request->all()),
            'ip' => $request->ip(), 
            'login_result' => $login_result,
        ]);

        $log->save();
    }
}
