<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Fittingz API Documentation",
    version: "1.0.0",
    description: "REST API for fashion designers and tailoring businesses to manage clients, measurements, orders, and payments.\n\nAll endpoints require a Bearer token received from the `/api/v1/auth/login` endpoint (except authentication routes).",
    contact: new OA\Contact(email: "support@fittingz.com")
)]
#[OA\Server(url: "/", description: "API Server")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter your bearer token to access protected endpoints."
)]
#[OA\Tag(name: "Authentication", description: "User authentication, registration, and account management")]
#[OA\Tag(name: "Clients", description: "Manage client records and profiles")]
#[OA\Tag(name: "Measurements", description: "Manage client measurement profiles")]
#[OA\Tag(name: "Orders", description: "Manage tailoring orders and statuses")]
#[OA\Tag(name: "Payments", description: "Record and track order payments")]
#[OA\Tag(name: "Styles", description: "Manage design styles and images")]
#[OA\Tag(name: "Dashboard", description: "Analytics, statistics, and business overview")]
class BaseDocs {}
