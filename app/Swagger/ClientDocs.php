<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class ClientDocs
{
    #[OA\Get(
        path: "/api/v1/clients",
        summary: "Get a paginated list of clients",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Search by name, email, or phone", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "gender", in: "query", description: "Filter by gender", required: false, schema: new OA\Schema(type: "string", enum: ["Male", "Female", "Other"])),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Clients retrieved successfully"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "phone", type: "string"),
                    new OA\Property(property: "gender", type: "string", enum: ["Male", "Female", "Other"]),
                    new OA\Property(property: "orders_count", type: "integer"),
                    new OA\Property(property: "measurements_count", type: "integer"),
                    new OA\Property(property: "created_at", type: "string", format: "date-time")
                ])),
                new OA\Property(property: "meta", type: "object")
            ]))
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/clients",
        summary: "Create a new client",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "gender"],
                properties: [
                    new OA\Property(property: "name", type: "string", minLength: 2, maxLength: 255, example: "Jane Smith"),
                    new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "jane.smith@example.com", description: "Must be unique. Required if phone is missing."),
                    new OA\Property(property: "phone", type: "string", minLength: 10, maxLength: 20, example: "+1234567890", description: "Must be unique. Required if email is missing."),
                    new OA\Property(property: "gender", type: "string", enum: ["Male", "Female", "Other"], example: "Female")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Client created successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/v1/clients/{client}",
        summary: "Get client details",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Client retrieved successfully"),
            new OA\Response(response: 404, description: "Client not found")
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}",
        summary: "Update existing client",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", minLength: 2, maxLength: 255, example: "Jane Smith Updated"),
                    new OA\Property(property: "email", type: "string", format: "email", maxLength: 255, example: "jane.updated@example.com"),
                    new OA\Property(property: "phone", type: "string", minLength: 10, maxLength: 20, example: "+1234567890"),
                    new OA\Property(property: "gender", type: "string", enum: ["Male", "Female", "Other"], example: "Female")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Client updated successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 404, description: "Client not found")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/v1/clients/{client}",
        summary: "Delete a client",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Client deleted successfully"),
            new OA\Response(response: 404, description: "Client not found")
        ]
    )]
    public function destroy() {}

    #[OA\Get(
        path: "/api/v1/clients/{client}/profile",
        summary: "Get full client profile including measurements and orders",
        tags: ["Clients"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Client profile retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "client", type: "object"),
                    new OA\Property(property: "measurements", type: "object", properties: [
                        new OA\Property(property: "default", type: "object", nullable: true),
                        new OA\Property(property: "latest", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "total_count", type: "integer")
                    ])
                ])
            ])),
            new OA\Response(response: 404, description: "Client not found")
        ]
    )]
    public function profile() {}
}
