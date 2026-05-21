<?php

return [
    // Date de fin du concours (clôture des votes) — timezone appliquée par app.timezone
    'ends_at' => env('CONTEST_ENDS_AT', '2026-05-28 12:00:00'),

    // Date de clôture des participations (upload)
    'upload_ends_at' => env('CONTEST_UPLOAD_ENDS_AT', '2026-05-25 23:59:59'),

    // Date d'annonce des gagnants
    'announce_at' => env('CONTEST_ANNOUNCE_AT', '2026-05-28 15:00:00'),

    // Fenêtres de rappel avant fin du concours
    'reminder_days' => [3, 1],
];
