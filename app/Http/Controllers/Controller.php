<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Buddy Script API",
    version: "1.0.0",
    description: "API Documentation for the Buddy Script Backend API"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server Host"
)]
abstract class Controller
{
    #[OA\Get(
        path: "/api/up",
        summary: "API Health Check",
        description: "Check if the API is up and running",
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            )
        ]
    )]
    public function healthCheck()
    {
        // Dummy route documentation
    }
}
