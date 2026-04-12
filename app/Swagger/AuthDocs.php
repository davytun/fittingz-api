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
            new OA\Response(response: 201, description: "Registration successful. Verification code sent to email.", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Registration successful. Please check your email for your verification code."),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "user", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "business_name", type: "string"),
                        new OA\Property(property: "email", type: "string"),
                        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true)
                    ])
                ])
            ])),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Registration failed")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/api/v1/auth/verify-email",
        summary: "Verify email address with a 4-digit code",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "code"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "code", type: "string", example: "0123", minLength: 4, maxLength: 4, pattern: "^\d{4}$", description: "4-digit verification code sent to the email address")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Email verified successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Email verified successfully. You can now log in."),
                new OA\Property(property: "data", type: "object", nullable: true)
            ])),
            new OA\Response(response: 400, description: "Invalid or expired verification code"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function verifyEmail() {}

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
                    new OA\Property(property: "token", type: "string", description: "Bearer token for authenticated requests"),
                    new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                    new OA\Property(property: "expires_in", type: "object", properties: [
                        new OA\Property(property: "minutes", type: "integer", example: 10080),
                        new OA\Property(property: "days", type: "integer", example: 7)
                    ])
                ])
            ])),
            new OA\Response(response: 401, description: "Invalid email or password"),
            new OA\Response(response: 403, description: "Email not yet verified"),
            new OA\Response(response: 423, description: "Account locked due to too many failed attempts")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/api/v1/auth/resend-verification",
        summary: "Resend email verification code",
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
            new OA\Response(response: 200, description: "Verification code sent or generic success if the account does not exist"),
            new OA\Response(response: 400, description: "This email address is already verified"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function resendVerification() {}

    #[OA\Post(
        path: "/api/v1/auth/forgot-password",
        summary: "Send password reset code to email",
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
            new OA\Response(response: 200, description: "Generic success response (reset code sent if account exists; email not found is not disclosed to prevent enumeration)"),
            new OA\Response(response: 422, description: "Validation error")
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
                    new OA\Property(property: "token", type: "string", example: "0123", minLength: 4, maxLength: 4, pattern: "^\d{4}$", description: "4-digit reset code sent to the email address")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Reset code verified"),
            new OA\Response(response: 400, description: "Invalid or expired reset code"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function verifyResetCode() {}

    #[OA\Post(
        path: "/api/v1/auth/reset-password",
        summary: "Reset user password using verified reset code",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "token", type: "string", example: "0123", minLength: 4, maxLength: 4, pattern: "^\d{4}$", description: "4-digit reset code sent to the email address"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password reset successfully"),
            new OA\Response(response: 400, description: "Invalid or expired reset code"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function resetPassword() {}

    #[OA\Post(
        path: "/api/v1/auth/logout",
        summary: "Logout user and revoke current token",
        tags: ["Authentication"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
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
                    new OA\Property(property: "token", type: "string", description: "New bearer token"),
                    new OA\Property(property: "expires_in", type: "object", properties: [
                        new OA\Property(property: "minutes", type: "integer", example: 10080),
                        new OA\Property(property: "days", type: "integer", example: 7)
                    ])
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
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
                required: ["current_password", "new_password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password"),
                    new OA\Property(property: "new_password", type: "string", format: "password", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password changed successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Password changed successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "token", type: "string", description: "New bearer token")
                ])
            ])),
            new OA\Response(response: 400, description: "Current password is incorrect"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function changePassword() {}
}
