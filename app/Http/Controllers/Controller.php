<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


/**
 *
 * @OA\Info(
 *      version="1.0.0",
 *      title="Callburn API",
 *      description="Callburn API OpenApi description",
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="L5 Swagger OpenApi server"
 *  )
 *
 * @OA\SecurityScheme(
 *     securityScheme="tokenBased",
 *     type="http",
 *     scheme="bearer",
 * )
 *
* @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
