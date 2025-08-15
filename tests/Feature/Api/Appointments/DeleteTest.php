<?php

final class DeleteTest extends FeatureTestCase
{
    /** Happy path: delete an existing appointment (with children) */
    public function testDeleteAppointmentSuccess(): void
    {
        $pdo      = test_pdo();
        $userId   = $this->ensureTestUserId();      // from FeatureTestCase
        $catId    = $this->ensureTestCategoryId();  // sets icon_class too
        $shareUid = $this->ensureNamedUserId('phpunit-share', 'phpunit-share@example.com');

        // Seed an appointment in the *test* DB
        $appId = $this->seedAppointment($pdo, $userId, $catId);

        // Seed children so we know cascading works
        $this->seedShare($pdo, $appId, $shareUid);
        $this->seedRecurring($pdo, $appId, 'weekly', '2026-12-31');

        // Sanity: should exist before delete
        $this->assertSame(1, $this->countById($pdo, 'appointments', $appId), 'Seeded appointment missing before delete');

        // Call endpoint and FORCE test DB (both header + query are accepted by your script)
        $url     = base_url() . '/backend/appointments/delete_appointment.php?env=test';
        $headers = ['X-Test-Env: 1', 'Content-Type: application/json'];

        [$code, $body, $err] = http_post_json($url, ['id' => $appId], $headers);
        $this->assertSame(200, $code, "Delete HTTP $code ($err): $body");

        $json = json_decode($body, true);
        $this->assertIsArray($json, "Response not JSON: $body");
        $this->assertTrue(($json['success'] ?? false), $json['error'] ?? "Delete failed: $body");

        // Verify hard-delete in DB (parent + children)
        $this->assertSame(0, $this->countById($pdo, 'appointments', $appId), 'Appointment not deleted');
        $this->assertSame(0, $this->countByAppointmentId($pdo, 'appointment_shares', $appId), 'Shares not deleted');
        $this->assertSame(0, $this->countByAppointmentId($pdo, 'recurring_appointments', $appId), 'Recurring not deleted');
    }

    /** Missing id should 422 */
    public function testDeleteWithoutIdReturns422(): void
    {
        $url     = base_url() . '/backend/appointments/delete_appointment.php?env=test';
        $headers = ['X-Test-Env: 1', 'Content-Type: application/json'];

        [$code, $body, $err] = http_post_json($url, [], $headers);
        $this->assertSame(422, $code, "Expected 422, got $code ($err): $body");

        $json = json_decode($body, true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success'] ?? true);
    }

    /** Non-existing id should return success:false with “No appointment matched that ID” */
    public function testDeleteNonExistingReturnsFalse(): void
    {
        $url     = base_url() . '/backend/appointments/delete_appointment.php?env=test';
        $headers = ['X-Test-Env: 1', 'Content-Type: application/json'];

        $nonId = 99999999;
        [$code, $body, $err] = http_post_json($url, ['id' => $nonId], $headers);
        $this->assertSame(200, $code, "Delete HTTP $code ($err): $body");

        $json = json_decode($body, true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success'] ?? true, "Expected success=false for non-existing id");
        $this->assertStringContainsString('No appointment matched', $json['message'] ?? '');
    }

}
