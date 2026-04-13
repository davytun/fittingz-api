<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

class StyleDocs
{
    /**
     * ============================================================================
     * ADMIN STYLE ENDPOINTS (Admin-only image management)
     * ============================================================================
     */
    #[OA\Post(
        path: '/api/v1/admin/styles/upload',
        summary: 'Upload style images (admin only)',
        description: 'Admin uploads design/style reference images for use in client projects. Images are stored and can be reused across multiple clients.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['images'],
                properties: [
                    new OA\Property(
                        property: 'images',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary'),
                        description: 'Array of image files (JPEG, PNG, JPG, WebP). Each max 5MB.'
                    ),
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        maxLength: 100,
                        nullable: true,
                        example: 'contemporary',
                        description: 'Optional style category or classification'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        maxLength: 2000,
                        nullable: true,
                        example: 'Modern dress with asymmetric design',
                        description: 'Optional detailed description of the style'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Images uploaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                                    new OA\Property(property: 'publicId', type: 'string'),
                                    new OA\Property(property: 'category', type: 'string', nullable: true),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'clientId', type: 'string', nullable: true),
                                    new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error - invalid images or size exceeds limit'),
        ]
    )]
    public function uploadAdmin() {}

    #[OA\Get(
        path: '/api/v1/admin/styles',
        summary: 'Get all uploaded style images (admin only)',
        description: 'Retrieve paginated list of all style images uploaded by the authenticated admin.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number for pagination',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'pageSize',
                in: 'query',
                description: 'Number of images per page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Style images retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                                    new OA\Property(property: 'publicId', type: 'string'),
                                    new OA\Property(property: 'category', type: 'string', nullable: true),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'client', type: 'object', nullable: true, properties: [
                                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                        new OA\Property(property: 'name', type: 'string'),
                                    ]),
                                    new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'pageSize', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'totalPages', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function indexAdmin() {}

    #[OA\Get(
        path: '/api/v1/admin/styles/count',
        summary: 'Get total count of style images',
        description: 'Retrieve the total number of style images in the system.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Count retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'count', type: 'integer', example: 42),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function countAdmin() {}

    #[OA\Post(
        path: '/api/v1/admin/styles/delete-multiple',
        summary: 'Delete multiple style images (admin only)',
        description: 'Batch delete style images by their IDs. Only images owned by the authenticated admin can be deleted.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['imageIds'],
                properties: [
                    new OA\Property(
                        property: 'imageIds',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'uuid'),
                        minItems: 1,
                        example: ['uuid-1', 'uuid-2', 'uuid-3'],
                        description: 'Array of image IDs to delete'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Images deleted with results summary',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: '2 image(s) deleted successfully.'),
                        new OA\Property(property: 'deletedCount', type: 'integer', example: 2),
                        new OA\Property(property: 'failedCount', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'failedImages',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'reason', type: 'string', enum: ['Not found', 'Forbidden']),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'No image IDs provided'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function deleteMultipleAdmin() {}

    /**
     * ============================================================================
     * CLIENT STYLE ENDPOINTS (Client-specific style management)
     * ============================================================================
     */
    #[OA\Post(
        path: '/api/v1/clients/{clientId}/styles/upload',
        summary: 'Upload style images for a specific client',
        description: 'Client (or their admin) uploads design/style reference images specifically for a client project. Images are associated with the client and can be used in their orders.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['images'],
                properties: [
                    new OA\Property(
                        property: 'images',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary'),
                        description: 'Array of image files (JPEG, PNG, JPG, WebP). Each max 5MB.'
                    ),
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        maxLength: 100,
                        nullable: true,
                        example: 'casual',
                        description: 'Optional style category'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        maxLength: 2000,
                        nullable: true,
                        example: "Client's preferred style - minimalist",
                        description: "Optional description of client's style preference"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Client style images uploaded successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                            new OA\Property(property: 'publicId', type: 'string'),
                            new OA\Property(property: 'category', type: 'string', nullable: true),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                            new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client'),
            new OA\Response(response: 404, description: 'Client not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function uploadClient() {}

    #[OA\Get(
        path: '/api/v1/clients/{clientId}/styles',
        summary: 'Get style images for a specific client',
        description: 'Retrieve paginated list of style images associated with a specific client.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number for pagination',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'pageSize',
                in: 'query',
                description: 'Number of images per page',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Client style images retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                                    new OA\Property(property: 'publicId', type: 'string'),
                                    new OA\Property(property: 'category', type: 'string', nullable: true),
                                    new OA\Property(property: 'description', type: 'string', nullable: true),
                                    new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer'),
                                new OA\Property(property: 'pageSize', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'totalPages', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client'),
            new OA\Response(response: 404, description: 'Client not found'),
        ]
    )]
    public function indexClient() {}

    #[OA\Get(
        path: '/api/v1/clients/{clientId}/styles/{imageId}',
        summary: 'Get a single style image for a client',
        description: 'Retrieve detailed information about a specific style image that belongs to a client.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'imageId',
                in: 'path',
                description: 'Style image UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Style image retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                        new OA\Property(property: 'publicId', type: 'string'),
                        new OA\Property(property: 'category', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client'),
            new OA\Response(response: 404, description: 'Client or style image not found'),
        ]
    )]
    public function showClient() {}

    #[OA\Patch(
        path: '/api/v1/clients/{clientId}/styles/{imageId}',
        summary: 'Update style image metadata',
        description: "Update the category and/or description of a client's style image.",
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'imageId',
                in: 'path',
                description: 'Style image UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'category',
                        type: 'string',
                        maxLength: 100,
                        nullable: true,
                        example: 'formal',
                        description: 'Updated style category'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        maxLength: 2000,
                        nullable: true,
                        example: 'Updated description of the style',
                        description: 'Updated style description'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Style image updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                        new OA\Property(property: 'publicId', type: 'string'),
                        new OA\Property(property: 'category', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client'),
            new OA\Response(response: 404, description: 'Client or style image not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateClient() {}

    #[OA\Delete(
        path: '/api/v1/clients/{clientId}/styles/{imageId}',
        summary: "Delete a client's style image",
        description: "Remove a style image from a client's collection.",
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'imageId',
                in: 'path',
                description: 'Style image UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Style image deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client'),
            new OA\Response(response: 404, description: 'Client or style image not found'),
        ]
    )]
    public function destroyClient() {}

    /**
     * ============================================================================
     * CLIENT ORDER STYLE ENDPOINTS (Link styles to orders)
     * ============================================================================
     */
    #[OA\Post(
        path: '/api/v1/clients/{clientId}/orders/{orderId}/styles/attach',
        summary: 'Attach a style image to an order',
        description: 'Link a style/design image to a specific order. This allows the designer to reference the style while fulfilling the order.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'orderId',
                in: 'path',
                description: 'Order UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['style_image_id'],
                properties: [
                    new OA\Property(
                        property: 'style_image_id',
                        type: 'string',
                        format: 'uuid',
                        example: '550e8400-e29b-41d4-a716-446655440000',
                        description: 'UUID of the style image to attach'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Style linked to order successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Style linked to order successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'imageUrl', type: 'string', format: 'url'),
                                new OA\Property(property: 'publicId', type: 'string'),
                                new OA\Property(property: 'category', type: 'string', nullable: true),
                                new OA\Property(property: 'description', type: 'string', nullable: true),
                                new OA\Property(property: 'clientId', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'adminId', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client or order'),
            new OA\Response(response: 404, description: 'Client, order, or style image not found'),
            new OA\Response(response: 422, description: 'Validation error or style image not found'),
        ]
    )]
    public function attachStyle() {}

    #[OA\Delete(
        path: '/api/v1/clients/{clientId}/orders/{orderId}/styles/{styleImageId}/detach',
        summary: 'Detach a style image from an order',
        description: 'Remove the association between a style/design image and a specific order.',
        tags: ['Styles'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clientId',
                in: 'path',
                description: 'Client UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'orderId',
                in: 'path',
                description: 'Order UUID',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'styleImageId',
                in: 'path',
                description: 'Style image UUID to detach',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Style unlinked from order successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Style unlinked from order successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Unauthorized - user does not own this client or order'),
            new OA\Response(response: 404, description: 'Client, order, or style image not found, or style not linked to order'),
        ]
    )]
    public function detachStyle() {}
}
