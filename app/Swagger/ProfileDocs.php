<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Profile", description: "Manage the authenticated user's profile")]
class ProfileDocs
{
    #[OA\Get(
        path: "/api/v1/profile",
        summary: "Get admin profile",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile retrieved successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Profile retrieved successfully"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "business_name", type: "string"),
                        new OA\Property(property: "contact_phone", type: "string"),
                        new OA\Property(property: "business_address", type: "string"),
                        new OA\Property(property: "email_verified", type: "boolean"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                    ])
                ])
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: "/api/v1/profile",
        summary: "Update admin profile",
        tags: ["Profile"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "business_name", type: "string", minLength: 2, maxLength: 255, example: "Fittingz Tailoring"),
                    new OA\Property(property: "contact_phone", type: "string", minLength: 10, maxLength: 20, example: "+1234567890"),
                    new OA\Property(property: "business_address", type: "string", minLength: 5, maxLength: 500, example: "123 Fashion Ave"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "email_notifications", type: "boolean", example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile updated successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Profile updated successfully"),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "email", type: "string", format: "email"),
                        new OA\Property(property: "business_name", type: "string"),
                        new OA\Property(property: "contact_phone", type: "string"),
                        new OA\Property(property: "business_address", type: "string"),
                        new OA\Property(property: "email_verified", type: "boolean"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                    ])
                ])
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function update() {}
}
