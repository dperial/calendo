<?php

/* helper function to validate appointment status against start and end dates */
// Returns null if valid, or an error message if invalid
function validateStatusVsDates(string $status, \DateTimeInterface $startDT, ?\DateTimeInterface $endDT = null): ?string {
  if (!$startDT) {
    return ["Bad start date/time format."];
  }
  if ($endDT === false) $endDT = null; // if endDT is not provided, treat it as null
  $now = new DateTime('now');

  switch ($status) {
    case 'completed':
      if ($endDT ? $endDT > $now : $startDT > $now) {
        return "Completed appointments must be in the past.";
      }
      break;

    case 'scheduled':
      if ($startDT < $now) {
        return "Scheduled appointments can’t start in the past.";
      }
      break;

    case 'ongoing':
      if (!$endDT) {               // if you only have start
        if ($startDT > $now) {
          return "Ongoing appointments must have started already.";
        }
      } else {
        if ($now < $startDT || $now > $endDT) {
          return "Ongoing appointments must be happening right now.";
        }
      }
      break;

    case 'cancelled':
      /* no date constraint */
      break;
  }
  return null; // no error
}
