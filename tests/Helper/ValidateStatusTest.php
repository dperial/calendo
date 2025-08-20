<?php
use PHPUnit\Framework\TestCase;
use Project\Calendo\Validator;

final class ValidateStatusTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('cases')]
    public function testValidateStatus(
        ?string $expectError,
        string $status,
        string $start,
        string $end
    ): void {
        $tz      = new DateTimeZone('Europe/Berlin');
        $startDT = new DateTime($start, $tz);
        $endDT   = new DateTime($end,   $tz);

        $err = Validator::validateStatusVsDates($status, $startDT, $endDT);

        if ($expectError !== null) {
            $this->assertSame($expectError, $err);
        } else {
            $this->assertNull($err);
        }
    }

    public static function cases(): iterable
    {
        return [
            'ongoing now ok'          => [null, 'ongoing', '-1 hour', '+1 hour'],
            'scheduled future ok'     => [null, 'scheduled', '+1 day 10:00', '+1 day 11:00'],
            // This should be INVALID per your helper (scheduled cannot start in the past)
            'scheduled past bad'      => ['Scheduled appointments can’t start in the past.', 'scheduled', '-1 day 10:00', '-1 day 11:00'],
            'completed past ok'       => [null, 'completed', '-1 day 10:00', '-1 day 11:00'],
            'completed future bad'    => ['Completed appointments must be in the past.', 'completed', '+1 day 10:00', '+1 day 11:00'],
            'cancelled past ok'       => [null, 'cancelled', '-1 day 10:00', '-1 day 11:00'],
        ];
    }
}
