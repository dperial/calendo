<?php

final class CreateTest extends FeatureTestCase
{
    public function testCreateReturnsSuccess(): void
    {
        $url = base_url() . '/backend/appointments/create_appointment.php';

        // Make sure FK targets exist in *test* DB
        $userId = $this->ensureTestUserId();
        $catId  = $this->ensureTestCategoryId();

        $payload = [
            'user_id'     => $userId,
            'title'       => 'Test via PHPUnit',
            'description' => 'Created in test',
            'category_id' => $catId,
            'status'      => 'scheduled',
            'type'        => 'private',
            'start_date'  => (new DateTime('+1 day'))->format('Y-m-d'),
            'end_date'    => (new DateTime('+1 day'))->format('Y-m-d'),
            'start_time'  => '10:00',
            'end_time'    => '11:00',
        ];

        [$code, $body, $err] = http_post_json($url, $payload);
        if ($code === 0 || $body === false) {
            $this->fail("HTTP call failed: $err");
        }

        $this->assertSame(200, $code, "HTTP $code, body: $body");
        $json = json_decode($body, true);
        $this->assertIsArray($json, "Response not JSON: $body");
        $this->assertTrue($json['success'] ?? false, $json['error'] ?? "Unknown error: $body");
    }
}
