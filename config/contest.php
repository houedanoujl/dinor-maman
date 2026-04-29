<?php

return [
    // Date de fin du concours (timezone appliquée par app.timezone)
    'ends_at' => env('CONTEST_ENDS_AT', '2026-05-31 23:59:59'),

    // Fenêtres de rappel avant fin du concours
    'reminder_days' => [3, 1],
];
