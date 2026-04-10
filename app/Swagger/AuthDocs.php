<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class AuthDocs
{
    #[OA\Post(
        path: "/api/v1/auth/register",
        summary: "Register a new user",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password", "password_confirmation", "business_name", "contact_phone", "business_address"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com", maxLength: 255),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "business_name", type: "string", example: "Fittingz Tailoring", minLength: 2, maxLength: 255),
                    new OA\Property(property: "contact_phone", type: "string", example: "+1234567890", minLength: 10, maxLength: 20),
                    new OA\Property(property: "business_address", type: "string", example: "123 Fashion Ave", minLength: 5, maxLength: 500)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User registered successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Registration successful"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "user", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "business_name", type: "string"),
                        new OA\Property(property: "email", type: "string")
                    ]),
                    new OA\Property(property: "token", type: "string")
                ])
            ])),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/api/v1/auth/login",
        summary: "Login user and return token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login successful", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Login successful"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "user", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "business_name", type: "string"),
                        new OA\Property(property: "email", type: "string")
                    ]),
                    new OA\Property(property: "token", type: "string")
                ])
            ])),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 403, description: "Account locked")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/api/v1/auth/resend-verification",
        summary: "Resend email verification link",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Verification link sent"),
            new OA\Response(response: 400, description: "Email already verified")
        ]
    )]
    public function resendVerification() {}

    #[OA\Post(
        path: "/api/v1/auth/forgot-password",
        summary: "Send password reset code",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Reset code sent"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function forgotPassword() {}

    #[OA\Post(
        path: "/api/v1/auth/verify-reset-code",
        summary: "Verify password reset code",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "token", type: "string", example: "1234", minLength: 4, maxLength: 4)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Reset code verified"),
            new OA\Response(response: 400, description: "Invalid code")
        ]
    )]
    public function verifyResetCode() {}

    #[OA\Post(
        path: "/api/v1/auth/reset-password",
        summary: "Reset user password",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "token", type: "string", example: "1234", minLength: 4, maxLength: 4),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password reset successful"),
            new OA\Response(response: 400, description: "Invalid code or token")
        ]
    )]
    public function resetPassword() {}

    #[OA\Post(
        path: "/api/v1/auth/logout",
        summary: "Logout user and revoke token",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logged out efficiently")
        ]
    )]
    public function logout() {}

    #[OA\Post(
        path: "/api/v1/auth/refresh",
        summary: "Refresh access token",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Token refreshed successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Token refreshed successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "token", type: "string")
                ])
            ]))
        ]
    )]
    public function refresh() {}

    #[OA\Post(
        path: "/api/v1/auth/change-password",
        summary: "Change account password",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password changed successfully"),
            new OA\Response(response: 400, description: "Incorrect current password")
        ]
    )]
    public function changePassword() {}
}
