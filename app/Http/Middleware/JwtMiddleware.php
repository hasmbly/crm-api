<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JwtMiddleware
{

    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();
        $current_date_time = Carbon::now()->toDateTimeString();
        
        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error'     => True,
                'message'   => "Sorry, You're not authorized to access this resource.",
                'path'      => $request->path(),
                'timestamp' => $current_date_time
            ], 400);
         } 

        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'error'     => True,
                'message'   => 'Sorry, Your Provided token is expired.',
                'path'      => $request->path(),
                'timestamp' => $current_date_time                
            ], 400);
        } catch(Exception $e) {
            return response()->json([
                'error'   => True,
                'message' => 'An error while decoding token :',
                'path'      => $request->path(),
                'timestamp' => $current_date_time                
            ], 400);
        }

        $user = User::find($credentials->sub);

        $roles = DB::table('users')->where('id', $credentials->sub)->pluck('id_roles');
        
         if ( $roles[0] == 1 ) {
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);

        }        

            return response()->json([
                'error'     => True,
                'message'   => "Maaf, Anda tidak berhak mengakses resource ini",
                'path'      => $request->path(),
                'timestamp' => $current_date_time
            ], 400);
    }
}