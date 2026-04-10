<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class OrderDocs
{
    #[OA\Get(
        path: "/api/v1/clients/{client}/orders",
        summary: "Get a client's orders",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "status", in: "query", description: "Filter by order status", required: false, schema: new OA\Schema(type: "string", enum: ["pending", "in_progress", "completed", "delivered", "cancelled"])),
            new OA\Parameter(name: "search", in: "query", description: "Search title, order number, or description", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "start_date", in: "query", description: "Filter orders created on or after date", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "end_date", in: "query", description: "Filter orders created on or before date", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Orders retrieved successfully"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "quantity", type: "integer"),
                    new OA\Property(property: "total_amount", type: "number", format: "float"),
                    new OA\Property(property: "balance", type: "number", format: "float"),
                    new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed", "delivered", "cancelled"]),
                    new OA\Property(property: "due_date", type: "string", format: "date", nullable: true)
                ])),
                new OA\Property(property: "meta", type: "object")
            ])),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Client not found"),
            new OA\Response(response: 422, description: "Invalid date filters")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/clients/{client}/orders",
        summary: "Create a new order",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "quantity", "total_amount"],
                properties: [
                    new OA\Property(property: "measurement_id", type: "string", format: "uuid", nullable: true),
                    new OA\Property(property: "title", type: "string", maxLength: 255, example: "Custom Wedding Suit"),
                    new OA\Property(property: "description", type: "string", maxLength: 2000, nullable: true),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 1),
                    new OA\Property(property: "total_amount", type: "number", format: "float", minimum: 0, maximum: 99999999.99, example: 500.00),
                    new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed", "delivered", "cancelled"], default: "pending"),
                    new OA\Property(property: "due_date", type: "string", format: "date", example: "2023-11-01", description: "Must be today or in the future"),
                    new OA\Property(property: "delivery_date", type: "string", format: "date", nullable: true, description: "Must be after or equal to due date"),
                    new OA\Property(property: "notes", type: "string", maxLength: 2000, nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Order created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Client not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/v1/clients/{client}/orders/{order}",
        summary: "Get order details",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Order retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order not found")
        ]
    )]
    public function show() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}/orders/{order}",
        summary: "Update order details",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", maxLength: 255, example: "Custom Wedding Suit V2"),
                    new OA\Property(property: "description", type: "string", maxLength: 2000),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1),
                    new OA\Property(property: "total_amount", type: "number", format: "float", minimum: 0),
                    new OA\Property(property: "due_date", type: "string", format: "date"),
                    new OA\Property(property: "delivery_date", type: "string", format: "date"),
                    new OA\Property(property: "notes", type: "string", maxLength: 2000)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Order updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}/orders/{order}/status",
        summary: "Update order status",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed", "delivered", "cancelled"], example: "in_progress")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Order status updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function updateStatus() {}

    #[OA\Patch(
        path: "/api/v1/clients/{client}/orders/{order}/measurement",
        summary: "Link order to a measurement profile",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["measurement_id"],
                properties: [
                    new OA\Property(property: "measurement_id", type: "string", format: "uuid")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Order measurement updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function updateMeasurement() {}

    #[OA\Delete(
        path: "/api/v1/clients/{client}/orders/{order}",
        summary: "Delete an order",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Order deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order not found")
        ]
    )]
    public function destroy() {}

    #[OA\Post(
        path: "/api/v1/clients/{client}/orders/{order}/styles",
        summary: "Attach a style to an order",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["style_id"],
                properties: [
                    new OA\Property(property: "style_id", type: "string", format: "uuid")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Style linked to order successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order or style not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function stylesAttach() {}

    #[OA\Delete(
        path: "/api/v1/clients/{client}/orders/{order}/styles/{style}",
        summary: "Detach a style from an order",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "client", in: "path", description: "Client UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "order", in: "path", description: "Order UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "style", in: "path", description: "Style UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Style unlinked from order successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Order, style, or link not found")
        ]
    )]
    public function stylesDetach() {}
}
