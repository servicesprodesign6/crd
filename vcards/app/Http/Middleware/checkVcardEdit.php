<?php

namespace App\Http\Middleware;

use App\Models\Vcard;
use App\Utils\ResponseUtil;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class checkVcardEdit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $vcard = $request->vcard;
        $user = getLogInUser();

        if ($vcard->isEditableBy($user)) {
        return $next($request);
        }

        if (! empty($user->organisation_id)) {
            abort(404);
        }

        return Response::json(ResponseUtil::makeError('Seems, you are not allowed to access this record."'), 422);
    }
}
