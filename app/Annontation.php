<?php
namespace App;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Annotation extends Model implements AuthenticatableContract, AuthorizableContract
{
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         description="This is  documentation for CRM",
 *         version="1.0.0",
 *         title="(Lumen)  documentation CRM",
 *         @OA\Contact(
 *             email="justhasby@gmail.com"
 *         ),
 *         @OA\License(
 *             name="Dinas Sosial Pemrov DKI Jakarta"
 *         ),
 *		
 *     )
 * )
 */	
/**
 * @OA\Get(
 *     path="/",
 *     description="Home page",
 *     @OA\Response(response="default", description="Welcome page")
 * )
 */

    use Authenticatable, Authorizable;
}