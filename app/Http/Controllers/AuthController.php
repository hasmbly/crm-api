<?php
namespace App\Http\Controllers;

use Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

use App\Mail\MailResetPass;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController 
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    private $key = null;
    private $value = null;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }
    /**
     * Create a new token.
     * 
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 300*300 // Expiration time default 60*60
        ];
        
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    } 
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     * 
     * @param  \App\User   $user 
     * @return mixed
     */
    public function authenticate(User $user) {
        $this->validate($this->request, [
            'username' => 'required_without_all:email',
            'email' => 'required_without_all:username',
            'password'  => 'required'
        ]);
        // Find the user by email or username
        
        if ($this->request->has('username')) {
            $this->key    = 'username';
            $this->value  = $this->request->input('username');
        } 
        
        if ($this->request->has('email')) {
            $this->key    = 'email';
            $this->value  = $this->request->input('email');
        }

        $user = User::where($this->key, $this->value)->first();
        
        if (!$user) {
            // You wil probably have some sort of helpers or whatever
            // to make sure that you have the same response format for
            // differents kind of responses. But let's return the 
            // below respose for now.
            return response()->json([
                'error' => 'Username or Email does not exist.'
            ], 400);
        }
        
        $pass = User::where('password', $this->request->input('password'))->first();
        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
        // if ($pass) {

            $roles = DB::table('users')
            ->join('roles','roles.id', '=', 'users.id_roles')
            ->where('users.id', $user->id)->pluck('roles.name');

            $instansi = DB::table('users')
            ->join('tbl_master_instansi','tbl_master_instansi.id_instansi', '=', 'users.id_instansi')
            ->where('users.id', $user->id)->pluck('tbl_master_instansi.nama_instansi');

            return response()->json([
                // 'token' => $this->jwt($user),
                'token'     => $this->jwt($user),
                'tokenType' => 'Bearer',
                'id'  => $user->id,
                'name'  => $user->name,
                'username'  => $user->username,
                'email'  => $user->email,
                'username'  => $user->username,
                'roles' => $roles[0],
                'instansi' => $instansi[0]
                // 'bycrypt' => password_hash('pusd4t1n', PASSWORD_BCRYPT)
            ], 200);
        }
        
        // Bad Request response
        return response()->json([
            'error' => 'Username/Email or password is wrong.'
        ], 400);
    }

  public function resetPassByEmail(Request $request) {
      $defaultPass = '123456';

      $email = $request->input('email');

      $GetEmail = DB::table('users')->where('email', $email)->pluck('email');

          if (empty($GetEmail[0])) {
              return response()->json([
              'error'        => true,
              'message'      => 'Maaf Email Anda tidak terdaftar'
            ], 404);          
      }

    try {

          $resetPass = DB::table('users')->where('email', $email)
          ->update([
            'password' => password_hash($defaultPass, PASSWORD_BCRYPT)
          ]);
        } catch (QueryException $e) {
            return response()->json([
                'error'       => true,
                'code'        => $e->getCode(),
                'message'     => $e->getMessage()
              ], 500);      
        }       

        $GetName = DB::table('users')->where('email', $email)->pluck('name');
        $GetName = $GetName[0];        

        Mail::to($email)->send(new MailResetPass($GetName, $defaultPass)); 

          return response()->json([
          'error'        => false,
          'message'      => 'Success, Password telah dikirim ke email anda'
        ], 200);  
    }
    
}