<?php
use PHPUnit\Framework\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        // uses your tests/bootstrap.php helper
        self::$pdo = test_pdo();
    }

    // FeatureTestCase.php
    protected bool $wrapInTransaction = false; // default false for HTTP/integration tests

    protected function setUp(): void {
        parent::setUp();
        if ($this->wrapInTransaction) {
            test_pdo()->beginTransaction();
        }
    }

    protected function tearDown(): void {
        if ($this->wrapInTransaction && test_pdo()->inTransaction()) {
            test_pdo()->rollBack();
        }
        parent::tearDown();
    }
    /**
     * Helper to ensure a test user exists in the test DB.
     * Returns the user ID.
     */
    protected function ensureTestUserId(): int {
        $pdo = test_pdo();                   // your helper from bootstrap.php
        // try to reuse the same user if already present
        $email = 'phpunit-user@example.com';
        $id = $pdo->query("SELECT id FROM users WHERE email=".$pdo->quote($email))->fetchColumn();
        if ($id) return (int)$id;

        // insert in autocommit mode (no BEGIN), so it’s visible to other connections
        $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
        $stmt->execute(['PHPUnit User', $email, password_hash('test', PASSWORD_BCRYPT)]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Helper to ensure a test category exists in the test DB.
     * Returns the category ID.
     */
    protected function ensureTestCategoryId(): int {
        $pdo = test_pdo();
        $name = 'PHPUnit Category';
        $id = $pdo->query("SELECT id FROM categories WHERE name=".$pdo->quote($name))->fetchColumn();
        if ($id) return (int)$id;

        $stmt = $pdo->prepare("INSERT INTO categories (name, color, icon_class) VALUES (?, ?, ?)");
        $stmt->execute([$name, '#999999', 'bi-book']);
        return (int)$pdo->lastInsertId();
    }
    /**
     * Helper to ensure a test appointment exists in the test DB.
     * Returns the appointment ID.
     */
    protected function ensureTestAppointmentId(): int {
        $pdo = test_pdo();
        $user = $this->ensureTestUserId();
        $cat = $this->ensureTestCategoryId();
            // Minimal valid row per schema
        $stmt = $pdo->prepare("
            INSERT INTO appointments
            (user_id, title, description, category_id, status, type,
            start_date, end_date, start_time, end_time)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user,
            'PHPUnit seed',
            'Seeded by tests',
            $cat,
            'scheduled',
            'private',
            '2025-01-01',     // DATE
            '2025-01-01',     // DATE
            '10:00:00',       // TIME
            '11:00:00',       // TIME
        ]);
        return (int)$pdo->lastInsertId();
    }
        /* ---------------- helpers (local to this test) ---------------- */

    protected function seedAppointment(PDO $pdo, int $userId, int $catId): int
    {
        $sql = "INSERT INTO appointments
                  (user_id, title, description, category_id, status, type, start_date, end_date, start_time, end_time)
                VALUES
                  (:user_id, :title, :description, :category_id, :status, :type, :start_date, :end_date, :start_time, :end_time)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => 'Delete Me (PHPUnit)',
            ':description' => 'Seeded for delete test',
            ':category_id' => $catId,
            ':status'      => 'scheduled',
            ':type'        => 'private',
            ':start_date'  => (new DateTime('+1 day'))->format('Y-m-d'),
            ':end_date'    => (new DateTime('+1 day'))->format('Y-m-d'),
            ':start_time'  => '10:00:00',
            ':end_time'    => '11:00:00',
        ]);
        return (int)$pdo->lastInsertId();
    }

    protected function seedShare(PDO $pdo, int $appointmentId, int $sharedWithUserId): void
    {
        $stmt = $pdo->prepare("INSERT INTO appointment_shares (appointment_id, shared_with_user_id) VALUES (?, ?)");
        $stmt->execute([$appointmentId, $sharedWithUserId]);
    }

    protected function seedRecurring(PDO $pdo, int $appointmentId, string $pattern, string $until): void
    {
        $stmt = $pdo->prepare("INSERT INTO recurring_appointments (appointment_id, recurrence_pattern, repeat_until) VALUES (?, ?, ?)");
        $stmt->execute([$appointmentId, $pattern, $until]);
    }

    protected function countById(PDO $pdo, string $table, int $id): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    protected function countByAppointmentId(PDO $pdo, string $table, int $appointmentId): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE appointment_id = ?");
        $stmt->execute([$appointmentId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Ensure an extra user exists (used for appointment_shares).
     * If you already have this in FeatureTestCase, you can remove this and call it from there.
     */
    protected function ensureNamedUserId(string $username, string $email): int
    {
        $pdo = test_pdo();
        $id  = $pdo->query("SELECT id FROM users WHERE username=" . $pdo->quote($username))->fetchColumn();
        if ($id) return (int)$id;

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        // Using a dummy password hash; replace if your schema requires specific constraints
        $stmt->execute([$username, $email, password_hash('secret', PASSWORD_BCRYPT)]);
        return (int)$pdo->lastInsertId();
    }
}
