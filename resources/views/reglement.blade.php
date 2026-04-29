@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 py-12">
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Concours</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">
            Règlement du concours<br>
            <span class="text-dinor-red">Un moment de cuisine avec maman</span>
        </h1>
        <p class="mt-3 text-sm text-gray-500">En vigueur à compter du {{ date('d/m/Y') }}</p>
    </div>

    <div class="prose prose-gray max-w-none space-y-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm text-gray-700">

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 1 — Organisation</h2>
            <p>Le concours « Un moment de cuisine avec maman » est organisé par l'équipe Dinor. Il est ouvert à toute personne souhaitant partager un souvenir de cuisine en famille.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 2 — Participation</h2>
            <p>Pour participer, il suffit de :</p>
            <ol class="mt-2 list-decimal space-y-1 pl-5">
                <li>Soumettre une photo via le formulaire en ligne (/participer)</li>
                <li>Fournir ses coordonnées (prénom, nom, téléphone, ville)</li>
                <li>Accepter le présent règlement</li>
            </ol>
            <p class="mt-2">La participation est <strong>gratuite</strong> et limitée à <strong>une photo par personne</strong> (contrôlé par numéro de téléphone).</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 3 — Critères de sélection des photos</h2>
            <p>Les photos soumises sont modérées par notre équipe. Pour être publiée, une photo doit :</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Être <strong>nette et de bonne qualité</strong></li>
                <li>Montrer clairement <strong>le participant et sa maman</strong> (ou une figure maternelle)</li>
                <li>Se dérouler dans un contexte de <strong>cuisine ou de repas familial</strong></li>
                <li>Ne comporter <strong>aucun contenu violent, offensant ou illicite</strong></li>
            </ul>
            <p class="mt-2">Toute photo ne respectant pas ces critères sera rejetée. Le participant en sera notifié avec le motif du rejet.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 4 — Votes</h2>
            <p>Les votes sont ouverts à tous, sans inscription. Chaque visiteur peut voter <strong>une fois par participant</strong> (contrôlé par adresse IP et session). Les votes automatisés ou frauduleux entraîneront la disqualification du participant concerné.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 5 — Désignation des gagnants</h2>
            <p>À la clôture du concours, les <strong>3 participants</strong> ayant recueilli le plus grand nombre de votes légitimes seront désignés gagnants. En cas d'égalité, la date de soumission la plus ancienne sera prise en compte.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 6 — Droits sur les photos</h2>
            <p>En soumettant une photo, le participant cède à l'organisateur le droit de l'utiliser à des fins de communication (réseaux sociaux, site web, supports promotionnels) en lien avec le concours. Aucune utilisation commerciale externe ne sera faite sans accord préalable.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 7 — Données personnelles (RGPD)</h2>
            <p>Les données collectées (nom, téléphone, email, ville) sont utilisées uniquement pour la gestion du concours et les notifications liées (accusé de réception, approbation, rejet). Elles ne sont pas cédées à des tiers. Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression en nous contactant.</p>
        </section>

        <section>
            <h2 class="font-display text-xl font-bold text-dinor-dark">Article 8 — Modification du règlement</h2>
            <p>L'organisateur se réserve le droit de modifier le présent règlement à tout moment. Les participants seront informés de toute modification substantielle.</p>
        </section>

    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('contest.form') }}"
           class="inline-flex items-center justify-center rounded-full bg-dinor-red px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
            Je participe
        </a>
    </div>
</div>
@endsection
