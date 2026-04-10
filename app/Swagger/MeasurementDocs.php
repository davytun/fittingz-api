<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class MeasurementDocs
{
    #[OA\Get(
        path: "/api/v1/clients/{client}/measurements",
        summary: "Get a client's measurements",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "client_id", type: "string", format: "uuid"),
                    new OA\Property(property: "name", type: "string", example: "Pants Measurement"),
                    new OA\Property(property: "fields", type: "object", example: ["waist" => "32", "inseam" => "30", "thigh" => "22"]),
                    new OA\Property(property: "unit", type: "string", enum: ["cm", "inches"]),
                    new OA\Property(property: "notes", type: "string", nullable: true),
                    new OA\Property(property: "is_default", type: "boolean"),
                    new OA\Property(property: "measurement_date", type: "string", format: "date"),
                    new OA\Property(property: "created_at", type: "string", format: "date-time"),
                    new OA\Property(property: "updated_at", type: "string", format: "date-time")
                ]))
            ])),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Client not found")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/clients/{client}/measurements",
        summary: "Create new measurements for a client",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "fields", "unit", "measurement_date"],
                properties: [
                    new OA\Property(property: "name", type: "string", maxLength: 255, example: "Pants Measurement"),
                    new OA\Property(property: "fields", type: "object", minProperties: 1, example: ["waist" => "32", "inseam" => "30", "thigh" => "22"], description: "Key-value pairs of body parts and their measurements"),
                    new OA\Property(property: "unit", type: "string", enum: ["cm", "inches"], example: "inches"),
                    new OA\Property(property: "notes", type: "string", maxLength: 1000, example: "Client prefers a loose fit", nullable: true),
                    new OA\Property(property: "measurement_date", type: "string", format: "date", example: "2026-04-10", description: "Cannot be in the future"),
                    new OA\Property(property: "is_default", type: "boolean", example: false, nullable: true, description: "Set as default measurement for this client")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Measurement profile created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Client not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/v1/clients/{client}/measurements/{measurement}",
        summary: "Get measurement details",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "measurement", in: "path", description: "Measurement UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Measurement profile retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Measurement not found")
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}/measurements/{measurement}",
        summary: "Update existing measurements",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "measurement", in: "path", description: "Measurement UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", maxLength: 255, example: "Pants Measurement"),
                    new OA\Property(property: "fields", type: "object", minProperties: 1, example: ["waist" => "34", "inseam" => "30"], description: "Pass null for a key to remove it"),
                    new OA\Property(property: "unit", type: "string", enum: ["cm", "inches"], example: "inches"),
                    new OA\Property(property: "notes", type: "string", maxLength: 1000, nullable: true),
                    new OA\Property(property: "measurement_date", type: "string", format: "date"),
                    new OA\Property(property: "is_default", type: "boolean", example: false, nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Measurement profile updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Measurement not found"),
            new OA\Response(response: 422, description: "Validation error including attempt to remove all measurements")
        ]
    )]
    public function update() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}/measurements/{measurement}/set-default",
        summary: "Set a measurement profile as the client's default",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "measurement", in: "path", description: "Measurement UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Default measurement updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Measurement not found")
        ]
    )]
    public function setDefault() {}

    #[OA\Delete(
        path: "/api/v1/clients/{client}/measurements/{measurement}",
        summary: "Delete a measurement profile",
        tags: ["Measurements"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "measurement", in: "path", description: "Measurement UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Measurement profile deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Measurement not found")
        ]
    )]
    public function destroy() {}
}
