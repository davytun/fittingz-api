<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class PaymentDocs
{
    #[OA\Get(
        path: "/api/v1/payments",
        summary: "Get a paginated list of payments",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "order_id", in: "query", description: "Filter by Order UUID", required: false, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid"),
                    new OA\Property(property: "order_id", type: "string", format: "uuid"),
                    new OA\Property(property: "amount", type: "number", format: "float"),
                    new OA\Property(property: "payment_method", type: "string", enum: ["cash", "bank_transfer", "pos", "other"]),
                    new OA\Property(property: "payment_date", type: "string", format: "date"),
                    new OA\Property(property: "reference", type: "string", nullable: true)
                ])),
                new OA\Property(property: "links", type: "object"),
                new OA\Property(property: "meta", type: "object")
            ]))
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/payments",
        summary: "Record a new payment",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_id", "amount", "payment_date", "payment_method"],
                properties: [
                    new OA\Property(property: "order_id", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
                    new OA\Property(property: "amount", type: "number", format: "float", minimum: 0.01, maximum: 99999999.99, example: 100.50, description: "Cannot exceed the outstanding balance of the order"),
                    new OA\Property(property: "payment_date", type: "string", format: "date", example: "2023-10-25", description: "Cannot be in the future"),
                    new OA\Property(property: "payment_method", type: "string", enum: ["cash", "bank_transfer", "pos", "other"], example: "bank_transfer"),
                    new OA\Property(property: "reference", type: "string", maxLength: 255, nullable: true, example: "TXN123456789"),
                    new OA\Property(property: "notes", type: "string", maxLength: 1000, nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Payment recorded successfully"),
            new OA\Response(response: 422, description: "Validation error (e.g., amount exceeds balance)")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/v1/payments/{payment}",
        summary: "Get payment details",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "payment", in: "path", description: "Payment UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Payment retrieved successfully"),
            new OA\Response(response: 404, description: "Payment not found")
        ]
    )]
    public function show() {}

    #[OA\Delete(
        path: "/api/v1/payments/{payment}",
        summary: "Delete a payment record",
        tags: ["Payments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "payment", in: "path", description: "Payment UUID", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Payment deleted successfully"),
            new OA\Response(response: 404, description: "Payment not found")
        ]
    )]
    public function destroy() {}
}
