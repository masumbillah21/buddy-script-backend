<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    #[OA\Post(
        path: "/api/register",
        summary: "User Registration",
        description: "Register a new user account",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["first_name", "last_name", "email", "password"],
                properties: [
                    new OA\Property(property: "first_name", type: "string", example: "John"),
                    new OA\Property(property: "last_name", type: "string", example: "Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $dto = RegisterDTO::fromRequest($request);
        $user = $this->authService->register($dto);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "User Login",
        description: "Log in with email and password to receive a bearer token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|abcdef123456...")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Invalid credentials")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $dto = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "User Logout",
        description: "Revoke user auth tokens",
        security: [["sanctum" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    #[OA\Get(
        path: "/api/me",
        summary: "Get Authenticated Profile",
        description: "Retrieve profile of the currently logged-in user",
        security: [["sanctum" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response();
    }
}

// Swagger Schemas for documentation completeness
namespace App\Http\Controllers\Api;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "first_name", type: "string", example: "John"),
        new OA\Property(property: "last_name", type: "string", example: "Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class UserSchema {}
