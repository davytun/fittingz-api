<?php

namespace Tests\Unit;

use App\Helpers\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    // ─── success() ───────────────────────────────────────────────────────────

    public function test_success_includes_data_key_when_data_is_provided(): void
    {
        $response = ApiResponse::success('OK', ['foo' => 'bar']);
        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertSame('OK', $json['message']);
        $this->assertArrayHasKey('data', $json);
        $this->assertSame(['foo' => 'bar'], $json['data']);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_success_omits_data_key_when_data_is_null(): void
    {
        $response = ApiResponse::success('Done');
        $json = $response->getData(true);

        $this->assertTrue($json['success']);
        $this->assertSame('Done', $json['message']);
        $this->assertArrayNotHasKey('data', $json);
    }

    public function test_success_uses_custom_status_code(): void
    {
        $response = ApiResponse::success('Created', ['id' => 1], 201);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_success_includes_data_key_when_data_is_empty_array(): void
    {
        $response = ApiResponse::success('Empty list', []);
        $json = $response->getData(true);

        $this->assertArrayHasKey('data', $json);
        $this->assertSame([], $json['data']);
    }

    public function test_success_includes_data_key_when_data_is_zero(): void
    {
        $response = ApiResponse::success('Zero', 0);
        $json = $response->getData(true);

        $this->assertArrayHasKey('data', $json);
        $this->assertSame(0, $json['data']);
    }

    // ─── error() ─────────────────────────────────────────────────────────────

    public function test_error_includes_errors_key_when_errors_is_provided(): void
    {
        $response = ApiResponse::error('Bad request', ['field' => 'required'], 400);
        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame('Bad request', $json['message']);
        $this->assertArrayHasKey('errors', $json);
        $this->assertSame(['field' => 'required'], $json['errors']);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_error_omits_errors_key_when_errors_is_null(): void
    {
        $response = ApiResponse::error('Something went wrong');
        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame('Something went wrong', $json['message']);
        $this->assertArrayNotHasKey('errors', $json);
    }

    public function test_error_uses_custom_status_code(): void
    {
        $response = ApiResponse::error('Not found', null, 404);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_error_defaults_to_400_status_code(): void
    {
        $response = ApiResponse::error('Bad');

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_error_includes_errors_key_when_errors_is_empty_array(): void
    {
        $response = ApiResponse::error('Validation', []);
        $json = $response->getData(true);

        // empty array is not null, so it should be included
        $this->assertArrayHasKey('errors', $json);
    }

    // ─── validationError() ───────────────────────────────────────────────────

    public function test_validation_error_returns_422_with_flattened_errors(): void
    {
        $errors = [
            'email' => ['Email is required', 'Email is invalid'],
            'name'  => ['Name is required'],
        ];

        $response = ApiResponse::validationError($errors);
        $json = $response->getData(true);

        $this->assertFalse($json['success']);
        $this->assertSame('Validation failed', $json['message']);
        $this->assertSame(422, $response->getStatusCode());
        // Only first message per field
        $this->assertSame('Email is required', $json['errors']['email']);
        $this->assertSame('Name is required', $json['errors']['name']);
    }

    public function test_validation_error_handles_string_error_messages(): void
    {
        $errors = ['field' => 'The field is required'];

        $response = ApiResponse::validationError($errors);
        $json = $response->getData(true);

        $this->assertSame('The field is required', $json['errors']['field']);
    }

    public function test_validation_error_uses_custom_message(): void
    {
        $response = ApiResponse::validationError(['foo' => ['bar']], 'Custom error');
        $json = $response->getData(true);

        $this->assertSame('Custom error', $json['message']);
    }

    public function test_validation_error_takes_only_first_message_per_field(): void
    {
        $errors = [
            'password' => ['Too short', 'Must contain uppercase', 'Must contain number'],
        ];

        $response = ApiResponse::validationError($errors);
        $json = $response->getData(true);

        $this->assertSame('Too short', $json['errors']['password']);
    }
}