<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class StyleDocs
{
    #[OA\Get(
        path: "/api/v1/styles",
        summary: "Get a paginated list of design styles",
        tags: ["Styles"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Search by title, description, or category", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category", in: "query", description: "Filter by category", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "image_url", type: "string", format: "url"),
                    new OA\Property(property: "category", type: "string"),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "string"))
                ])),
                new OA\Property(property: "links", type: "object"),
                new OA\Property(property: "meta", type: "object")
            ]))
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/styles",
        summary: "Upload a new style image",
        tags: ["Styles"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["image"],
                    properties: [
                        new OA\Property(property: "image", type: "string", format: "binary", description: "Image file (jpeg, png, jpg, webp). Max size 5MB."),
                        new OA\Property(property: "title", type: "string", maxLength: 255, nullable: true),
                        new OA\Property(property: "description", type: "string", maxLength: 2000, nullable: true),
                        new OA\Property(property: "category", type: "string", maxLength: 100, nullable: true),
                        new OA\Property(property: "tags[]", type: "array", items: new OA\Items(type: "string", maxLength: 50), description: "Array of tags", nullable: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Style uploaded successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/v1/styles/{style}",
        summary: "Get style details",
        tags: ["Styles"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "style", in: "path", description: "Style UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Style retrieved successfully"),
            new OA\Response(response: 404, description: "Style not found")
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: "/api/v1/styles/{style}",
        summary: "Update style metadata",
        tags: ["Styles"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "style", in: "path", description: "Style UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", maxLength: 255, nullable: true),
                    new OA\Property(property: "description", type: "string", maxLength: 2000, nullable: true),
                    new OA\Property(property: "category", type: "string", maxLength: 100, nullable: true),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "string", maxLength: 50), nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Style metadata updated successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/v1/styles/{style}",
        summary: "Delete a style",
        tags: ["Styles"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "style", in: "path", description: "Style UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Style deleted successfully"),
            new OA\Response(response: 404, description: "Style not found")
        ]
    )]
    public function destroy() {}
}
