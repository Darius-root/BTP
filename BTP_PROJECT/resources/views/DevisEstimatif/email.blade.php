<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis Estimatif</title>
</head>
<body>
    <h1>Bonjour {{ $devis->batiment->projet->client->nom ?? 'Client' }}</h1>

    <p>Veuillez trouver ci-joint le devis estimatif pour votre projet <strong>{{ $devis->batiment->projet->nom }}</strong>.</p>

    <p>Total général : {{ number_format($totalGeneral, 2, ',', ' ') }} {{ $devise }}</p>

    <h3>Détails par niveau :</h3>
    <ul>
        @foreach($totauxParNiveau as $row)
            <li>{{ $row['niveau_nom'] }} : {{ number_format($row['total'], 2, ',', ' ') }} {{ $devise }}</li>
        @endforeach
    </ul>

    @if($messagePersonnalise)
        <p style="margin-top:20px;">Message personnalisé : {{ $messagePersonnalise }}</p>
    @endif

    <p>Le devis complet est disponible en pièce jointe (PDF).</p>
</body>
</html>
