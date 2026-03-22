<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class DashboardDocs
{
    #[OA\Get(
        path: "/api/v1/dashboard/stats",
        summary: "Get overall business statistics",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "total_clients", type: "integer"),
                    new OA\Property(property: "active_orders", type: "integer"),
                    new OA\Property(property: "total_revenue", type: "number", format: "float"),
                    new OA\Property(property: "pending_payments", type: "number", format: "float")
                ])
            ]))
        ]
    )]
    public function stats() {}

    #[OA\Get(
        path: "/api/v1/dashboard/recent-orders",
        summary: "Get list of recently created orders",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function recentOrders() {}

    #[OA\Get(
        path: "/api/v1/dashboard/pending-payments",
        summary: "Get list of orders with pending balances",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function pendingPayments() {}

    #[OA\Get(
        path: "/api/v1/dashboard/upcoming-deliveries",
        summary: "Get orders that are due for delivery soon",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function upcomingDeliveries() {}

    #[OA\Get(
        path: "/api/v1/dashboard/overdue-orders",
        summary: "Get orders that have passed their due date and aren't completed",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function overdueOrders() {}

    #[OA\Get(
        path: "/api/v1/dashboard/revenue-analytics",
        summary: "Get revenue analytics for charts",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "period", in: "query", description: "Time period to group by", required: false, schema: new OA\Schema(type: "string", enum: ["month", "year"], default: "month"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                    new OA\Property(property: "period", type: "string", example: "Oct 2023"),
                    new OA\Property(property: "revenue", type: "number", format: "float", example: 12500.50)
                ]))
            ]))
        ]
    )]
    public function revenueAnalytics() {}

    #[OA\Get(
        path: "/api/v1/dashboard/top-clients",
        summary: "Get clients with the highest spending or most orders",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function topClients() {}
}
