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
            new OA\Parameter(name: "status", in: "query", description: "Filter by order status", required: false, schema: new OA\Schema(type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"])),
            new OA\Parameter(name: "search", in: "query", description: "Search by order number or style description", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "start_date", in: "query", description: "Filter orders created on or after date", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "end_date", in: "query", description: "Filter orders created on or before date", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "include", in: "query", description: "Comma-separated relations to include: measurement, styles", required: false, schema: new OA\Schema(type: "string", example: "measurement,styles")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Orders retrieved successfully"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "order_number", type: "string"),
                    new OA\Property(property: "details", type: "object", nullable: true, example: ["fabric" => "cotton", "color" => "blue"]),
                    new OA\Property(property: "style_description", type: "string", nullable: true),
                    new OA\Property(property: "total_amount", type: "number", format: "float"),
                    new OA\Property(property: "currency", type: "string", enum: ["NGN", "USD", "GBP", "EUR"]),
                    new OA\Property(property: "total_paid", type: "number", format: "float"),
                    new OA\Property(property: "balance", type: "number", format: "float"),
                    new OA\Property(property: "payment_status", type: "string", enum: ["unpaid", "partial", "fully_paid"]),
                    new OA\Property(property: "status", type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"]),
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
                required: ["total_amount"],
                properties: [
                    new OA\Property(property: "measurement_id", type: "string", format: "uuid", nullable: true),
                    new OA\Property(property: "details", type: "object", nullable: true, example: ["fabric" => "cotton", "color" => "blue"], description: "Free-form key-value details about the garment"),
                    new OA\Property(property: "style_description", type: "string", maxLength: 2000, nullable: true, example: "Elegant evening gown"),
                    new OA\Property(property: "total_amount", type: "number", format: "float", minimum: 0, example: 25000),
                    new OA\Property(property: "currency", type: "string", enum: ["NGN", "USD", "GBP", "EUR"], default: "NGN", example: "NGN"),
                    new OA\Property(property: "status", type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"], default: "pending_payment"),
                    new OA\Property(property: "due_date", type: "string", format: "date", example: "2025-07-20", description: "Must be today or in the future"),
                    new OA\Property(property: "delivery_date", type: "string", format: "date", nullable: true, description: "Must be on or after due date"),
                    new OA\Property(property: "notes", type: "string", maxLength: 2000, nullable: true, example: "Rush order for wedding"),
                    new OA\Property(property: "deposit", type: "number", format: "float", nullable: true, minimum: 0, example: 5000, description: "Optional upfront deposit — recorded as an initial payment via cash")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Order created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Client or measurement not found"),
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
                    new OA\Property(property: "details", type: "object", nullable: true, example: ["fabric" => "linen", "color" => "navy"]),
                    new OA\Property(property: "style_description", type: "string", maxLength: 2000, nullable: true),
                    new OA\Property(property: "total_amount", type: "number", format: "float", minimum: 0),
                    new OA\Property(property: "currency", type: "string", enum: ["NGN", "USD", "GBP", "EUR"]),
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
                    new OA\Property(property: "status", type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"], example: "in_progress")
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
            new OA\Response(response: 404, description: "Order or measurement not found"),
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
