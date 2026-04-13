<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class DashboardDocs
{
    #[OA\Get(
        path: "/api/v1/dashboard",
        summary: "Get comprehensive dashboard data including batch data and analytics",
        description: "Fetches all dashboard statistics, recent clients, orders, and updates in a single API call for better performance.",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "entities",
                in: "query",
                description: "Comma-separated list of entities to fetch",
                required: false,
                schema: new OA\Schema(
                    type: "string",
                    default: "clients,orders,projects,events,gallery",
                    example: "clients,orders,projects,events,gallery"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Comprehensive dashboard data retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "summary",
                            type: "object",
                            properties: [
                                new OA\Property(property: "totalClients", type: "integer", example: 42),
                                new OA\Property(property: "totalOrders", type: "integer", example: 120),
                                new OA\Property(property: "totalRevenue", type: "number", format: "float", example: 85000.00)
                            ]
                        ),
                        new OA\Property(
                            property: "recentClients",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid"),
                                    new OA\Property(property: "name", type: "string"),
                                    new OA\Property(property: "phone", type: "string", nullable: true),
                                    new OA\Property(property: "email", type: "string", nullable: true),
                                    new OA\Property(property: "gender", type: "string", enum: ["Male", "Female", "Other"], nullable: true),
                                    new OA\Property(property: "adminId", type: "string", format: "uuid"),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "measurements", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(property: "styleImages", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(
                                        property: "_count",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "measurements", type: "integer"),
                                            new OA\Property(property: "styleImages", type: "integer")
                                        ]
                                    )
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "recentOrders",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid"),
                                    new OA\Property(property: "orderNumber", type: "string"),
                                    new OA\Property(property: "price", type: "number", format: "float"),
                                    new OA\Property(property: "currency", type: "string", enum: ["NGN", "USD", "GBP", "EUR"]),
                                    new OA\Property(property: "status", type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"]),
                                    new OA\Property(property: "outstandingAmount", type: "number", format: "float"),
                                    new OA\Property(
                                        property: "client",
                                        type: "object",
                                        nullable: true,
                                        properties: [
                                            new OA\Property(property: "id", type: "string", format: "uuid"),
                                            new OA\Property(property: "name", type: "string")
                                        ]
                                    ),
                                    new OA\Property(property: "payments", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(property: "styleImages", type: "array", items: new OA\Items(type: "object")),
                                    new OA\Property(property: "details", type: "object", nullable: true),
                                    new OA\Property(property: "dueDate", type: "string", format: "date", nullable: true),
                                    new OA\Property(property: "deposit", type: "number", format: "float"),
                                    new OA\Property(property: "styleDescription", type: "string", nullable: true),
                                    new OA\Property(property: "note", type: "string", nullable: true),
                                    new OA\Property(property: "totalPaid", type: "number", format: "float"),
                                    new OA\Property(property: "outstandingBalance", type: "number", format: "float"),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "updatedAt", type: "string", format: "date-time"),
                                    new OA\Property(property: "project", type: "object", nullable: true),
                                    new OA\Property(property: "event", type: "object", nullable: true),
                                    new OA\Property(property: "measurement", type: "object", nullable: true)
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "orderStats",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "status", type: "string", enum: ["pending_payment", "in_progress", "completed", "delivered", "cancelled"]),
                                    new OA\Property(property: "_count", type: "integer"),
                                    new OA\Property(property: "_sum", type: "number", format: "float")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "recentUpdates",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string"),
                                    new OA\Property(property: "type", type: "string", enum: ["CLIENT_CREATED", "ORDER_CREATED"]),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "description", type: "string"),
                                    new OA\Property(property: "entityId", type: "string", format: "uuid"),
                                    new OA\Property(property: "entityType", type: "string", enum: ["client", "order"]),
                                    new OA\Property(property: "createdAt", type: "string", format: "date-time")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 500, description: "Internal server error")
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: "/api/v1/dashboard/stats",
        summary: "Get overall business statistics",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Dashboard stats retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Dashboard stats retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "clients", type: "object", properties: [
                        new OA\Property(property: "total", type: "integer", example: 42),
                        new OA\Property(property: "new_this_month", type: "integer", example: 5)
                    ]),
                    new OA\Property(property: "orders", type: "object", properties: [
                        new OA\Property(property: "total", type: "integer", example: 120),
                        new OA\Property(property: "pending_payment", type: "integer", example: 10),
                        new OA\Property(property: "in_progress", type: "integer", example: 25),
                        new OA\Property(property: "completed", type: "integer", example: 60),
                        new OA\Property(property: "delivered", type: "integer", example: 20),
                        new OA\Property(property: "cancelled", type: "integer", example: 5)
                    ]),
                    new OA\Property(property: "revenue", type: "object", description: "Amounts keyed by currency code (e.g. NGN, USD)", properties: [
                        new OA\Property(property: "by_currency", type: "object", example: ["NGN" => 85000.00, "USD" => 200.00]),
                        new OA\Property(property: "paid_by_currency", type: "object", example: ["NGN" => 72000.00, "USD" => 150.00]),
                        new OA\Property(property: "outstanding_by_currency", type: "object", example: ["NGN" => 13000.00, "USD" => 50.00]),
                        new OA\Property(property: "this_month_by_currency", type: "object", example: ["NGN" => 9500.00])
                    ]),
                    new OA\Property(property: "payments", type: "object", properties: [
                        new OA\Property(property: "this_month_by_currency", type: "object", example: ["NGN" => 8200.00]),
                        new OA\Property(property: "orders_with_balance", type: "integer", example: 18)
                    ])
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function stats() {}

    #[OA\Get(
        path: "/api/v1/dashboard/recent-orders",
        summary: "Get list of recently created orders",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", description: "Number of orders to return (default 10)", required: false, schema: new OA\Schema(type: "integer", default: 10))
        ],
        responses: [
            new OA\Response(response: 200, description: "Recent orders retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Recent orders retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "orders", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "total", type: "integer", example: 10)
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function recentOrders() {}

    #[OA\Get(
        path: "/api/v1/dashboard/pending-payments",
        summary: "Get orders with outstanding balances, sorted by balance descending",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Pending payments retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Pending payments retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "orders", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "total_orders", type: "integer", example: 18),
                    new OA\Property(property: "total_outstanding", type: "number", format: "float", example: 13000.00)
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function pendingPayments() {}

    #[OA\Get(
        path: "/api/v1/dashboard/upcoming-deliveries",
        summary: "Get orders due for delivery within a given number of days",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "days", in: "query", description: "Number of days ahead to look (default 7)", required: false, schema: new OA\Schema(type: "integer", default: 7))
        ],
        responses: [
            new OA\Response(response: 200, description: "Upcoming deliveries retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Upcoming deliveries retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "orders", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "total", type: "integer", example: 4),
                    new OA\Property(property: "period", type: "string", example: "7 days")
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function upcomingDeliveries() {}

    #[OA\Get(
        path: "/api/v1/dashboard/overdue-orders",
        summary: "Get pending or in-progress orders that have passed their due date",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Overdue orders retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Overdue orders retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "orders", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "total", type: "integer", example: 3)
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function overdueOrders() {}

    #[OA\Get(
        path: "/api/v1/dashboard/revenue-analytics",
        summary: "Get revenue analytics. Monthly period returns daily breakdown; yearly period returns monthly breakdown.",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "period", in: "query", description: "Time period: 'month' (daily breakdown of current month) or 'year' (monthly breakdown of current year)", required: false, schema: new OA\Schema(type: "string", enum: ["month", "year"], default: "month"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Revenue analytics retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Revenue analytics retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "period", type: "string", example: "month"),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "day", type: "integer", example: 1, description: "Day of month (only present when period=month)"),
                        new OA\Property(property: "date", type: "string", format: "date", example: "2024-04-01", description: "Date (only present when period=month)"),
                        new OA\Property(property: "month", type: "string", example: "Jan", description: "Month abbreviation (only present when period=year)"),
                        new OA\Property(property: "revenue", type: "object", description: "Revenue keyed by currency code", example: ["NGN" => 1200.00]),
                        new OA\Property(property: "payments", type: "object", description: "Payments keyed by currency code", example: ["NGN" => 900.00])
                    ]))
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function revenueAnalytics() {}

    #[OA\Get(
        path: "/api/v1/dashboard/top-clients",
        summary: "Get top clients ranked by total amount spent",
        tags: ["Dashboard"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", description: "Number of clients to return (default 10)", required: false, schema: new OA\Schema(type: "integer", default: 10))
        ],
        responses: [
            new OA\Response(response: 200, description: "Top clients retrieved successfully", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Top clients retrieved successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "clients", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "id", type: "string", format: "uuid"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "email", type: "string", nullable: true),
                        new OA\Property(property: "phone", type: "string", nullable: true),
                        new OA\Property(property: "total_orders", type: "integer", example: 8),
                        new OA\Property(property: "total_spent", type: "number", format: "float", example: 12500.00)
                    ])),
                    new OA\Property(property: "total", type: "integer", example: 10)
                ])
            ])),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function topClients() {}
}
