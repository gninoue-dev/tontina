<?php

namespace Database\Seeders;

use App\Actions\RecordContribution;
use App\Actions\StartTontine;
use App\Frequency;
use App\Models\Tontine;
use App\Models\User;
use App\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Données de démonstration : un organisateur (demo@tontina.ci / password) et une tontine de 6 membres en cours.
     */
    public function run(StartTontine $start, RecordContribution $record): void
    {
        $user = User::factory()->create(['name' => 'Aminata Koné', 'email' => 'demo@tontina.ci']);

        $tontine = Tontine::factory()->for($user, 'organizer')->create([
            'name' => 'Tontine des commerçantes d\'Adjamé',
            'description' => "Cotisation à remettre avant le 5 du mois.\nRéunion le premier samedi chez Aminata.",
            'contribution_amount' => 25000,
            'frequency' => Frequency::Monthly,
            'starts_on' => now()->subMonths(2)->startOfMonth()->addDays(4)->toDateString(),
            'late_penalty' => 2000,
            'public_token' => 'demo-adjame',
        ]);

        foreach ([
            ['Aminata Koné', '0707123456'], ['Awa Traoré', '0505234567'], ['Mariam Ouattara', '0101345678'],
            ['Adjoua Kouassi', '0707456789'], ['Fatou Bamba', '0505567890'], ['Clarisse Yao', '0101678901'],
        ] as [$name, $phone]) {
            $tontine->members()->create(['name' => $name, 'phone' => $phone]);
        }
        $start->handle($tontine);

        $methods = PaymentMethod::cases();
        foreach ($tontine->rounds()->take(3)->get() as $round) {
            foreach ($tontine->members()->get() as $index => $member) {
                if ($round->number === 3 && $index >= 4) {
                    continue;
                }
                $record->handle($round, $member, $methods[$index % count($methods)],
                    $round->due_on->subDays(1)->min(now()->toImmutable()), $index % 2 ? 'MP'.random_int(100000, 999999) : null);
            }
            if ($round->number < 3) {
                $round->update(['paid_out_at' => $round->due_on->addDay()]);
            }
        }
    }
}
