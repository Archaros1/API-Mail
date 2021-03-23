<?php

namespace App\Http\Middleware;

use Closure;
use App\TemporaryKey as TmpKey;


class CheckForTmpKey
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

        if (TmpKey::where('tmpKey', request('token'))->exists()) {
            return $next($request);
        }
        else {
            return response()->json(["ERREUR : clé publique non-valide"]);
        }

    }
}
