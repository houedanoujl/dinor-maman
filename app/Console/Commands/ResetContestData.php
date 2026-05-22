<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\SmsLog;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetContestData extends Command
{
    protected $signature = 'contest:reset
                            {--force : Bypass confirmation (production safety)}
                            {--keep-admins=1 : Preserve admin users}';

    protected $description = 'Supprime participants, votes, votants et logs SMS. Garde les comptes admin par défaut.';

    public function handle(): int
    {
        $keepAdmins = (bool) $this->option('keep-admins');

        $counts = [
            'votes'        => Vote::count(),
            'participants' => Participant::count(),
            'participants_users' => User::where('role', User::ROLE_PARTICIPANT)->count(),
            'sms_logs'     => SmsLog::count(),
        ];

        $this->table(['Table', 'Rows'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values());

        if (! $this->option('force')) {
            if (! $this->confirm('⚠ Supprimer toutes ces données ? Action irréversible.', false)) {
                $this->warn('Annulé.');
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($keepAdmins) {
            // Ordre : votes → media (cascade via participants) → participants → users non-admin → sms_logs
            Vote::query()->delete();

            // Spatie media : delete via model pour purger fichiers disque
            Participant::query()->each(function (Participant $p) {
                $p->clearMediaCollection('photo');
                $p->delete();
            });

            // Reset compteurs
            DB::table('participants')->update(['vote_count' => 0]);

            $usersQuery = User::query();
            if ($keepAdmins) {
                $usersQuery->where('role', '!=', User::ROLE_ADMIN);
            }
            $usersQuery->delete();

            SmsLog::query()->delete();

            // Truncate rate-limit caches connus
            try { DB::table('cache')->where('key', 'like', '%vote:user:%')->delete(); } catch (\Throwable $e) {}
            try { DB::table('cache')->where('key', 'like', '%contest-submit%')->delete(); } catch (\Throwable $e) {}
            try { DB::table('cache')->where('key', 'like', '%login:%')->delete(); } catch (\Throwable $e) {}

            // Reset auto-increment (MySQL)
            if (DB::connection()->getDriverName() === 'mysql') {
                foreach (['votes', 'participants', 'sms_logs'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    }
                }
            }
        });

        $this->info('✅ Données de concours réinitialisées.');
        $this->newLine();
        $this->table(['Reste', 'Count'], [
            ['Admins', User::where('role', User::ROLE_ADMIN)->count()],
            ['Autres users', User::where('role', '!=', User::ROLE_ADMIN)->count()],
            ['Participants', Participant::count()],
            ['Votes', Vote::count()],
            ['SMS logs', SmsLog::count()],
        ]);

        return self::SUCCESS;
    }
}
