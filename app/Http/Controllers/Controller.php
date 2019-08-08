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
 *     name="Account",
 *     description="Accounts endpoints",
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="Users endpoints",
 * )
 * @OA\Tag(
 *     name="Workflows",
 *     description="Workflows endpoints",
 * )
 * @OA\Tag(
 *     name="GroupWorkflows",
 *     description="Groups Workflows endpoints",
 * )
 * @OA\Tag(
 *     name="Times",
 *     description="Times endpoints",
 * )
 * @OA\Tag(
 *     name="Days",
 *     description="Days endpoints",
 * )
 * @OA\Tag(
 *     name="WorkflowContactKeys",
 *     description="Workflow Contact Key endpoints",
 * )
 * @OA\Tag(
 *     name="Keys",
 *     description="Keys endpoints",
 * )
 * @OA\Tag(
 *     name="KeyEventTypes",
 *     description="Key Event Types endpoints",
 * )
 * @OA\Tag(
 *     name="WorkflowsContact",
 *     description="Workflows Contact endpoints",
 * )
 * @OA\Tag(
 *     name="Contacts",
 *     description="Contacts endpoints",
 * )
 * @OA\Tag(
 *     name="Groups",
 *     description="Groups endpoints",
 * )
 * @OA\Tag(
 *     name="GroupContact",
 *     description="GroupContact endpoints",
 * )
 * @OA\Tag(
 *     name="Rols",
 *     description="Rols endpoints",
 * )
 * @OA\Tag(
 *     name="Business",
 *     description="Business endpoints",
 * )
 * @OA\Tag(
 *     name="Reservations",
 *     description="Reservations endpoints",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
