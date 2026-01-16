<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis Estimatif - {{ $devis->code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header-left {
            width: 30%;
        }

        .header-right {
            width: 65%;
            text-align: right;
        }

        .header-right h1 {
            margin: 0;
            font-size: 20px;
        }

        .header-right h2 {
            margin: 5px 0 10px 0;
            font-size: 16px;
        }

        .header-right p {
            margin: 0;
            font-weight: bold;
        }

        .info-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .info-left,
        .info-right {
            width: 48%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .table th {
            background-color: #f2f2f2;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            padding: 6px;
            border: 1px solid #ddd;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .niveau-title {
            background-color: #333;
            color: white;
            padding: 5px 10px;
            margin: 10px 0;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- HEADER avec logo -->
    <div class="header">
        <div class="header-left">
            @if(!empty($devis->batiment->projet->organisation->logo))
                <img src="{{ public_path('storage/' . $devis->batiment->projet->organisation->logo) }}"
                     alt="Logo Organisation"
                     style="max-height:80px;">
            @endif
        </div>
        <div class="header-right">
            <h1>DEVIS ESTIMATIF</h1>
            <h2>{{ $devis->intitule }}</h2>
            <p>Référence : {{ $devis->code }}</p>
        </div>
    </div>

    <!-- INFORMATIONS CLIENT -->
    <div class="info-section">
        <div class="info-left">
            <h3>CLIENT</h3>
            <p><strong>Organisation :</strong> {{ $devis->batiment->projet->organisation->nom }}</p>
            <p><strong>Projet :</strong> {{ $devis->batiment->projet->nom }}</p>
            <p><strong>Bâtiment :</strong> {{ $devis->batiment->nom }}</p>
            <p><strong>Date :</strong> {{ $devis->created_at->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- OBJET -->
    <div style="margin: 20px 0; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">
        <p><strong>Objet :</strong> Devis estimatif pour les travaux de {{ $devis->intitule }}</p>
        <p><strong>Validité :</strong> 30 jours à compter de la date d'émission</p>
        <p><strong>Devise :</strong> {{ $devise }}</p>
    </div>

    <!-- TABLEAUX PAR NIVEAU -->
    @foreach($totauxParNiveau->groupBy('niveau_nom') as $niveauNom => $items)
        <div class="niveau-title">
            NIVEAU : {{ strtoupper($niveauNom) }}
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10%;">Code</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 10%;">Unité</th>
                    <th style="width: 10%;">Qté</th>
                    <th style="width: 15%;">Prix unitaire</th>
                    <th style="width: 20%;">Montant ({{ $devise }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devis->composants->where('niveau.nom', $niveauNom) as $composant)
                    <tr>
                        <td>{{ $composant->code }}</td>
                        <td>{{ $composant->piece }}</td>
                        <td style="text-align: center;">{{ $composant->unite->libelle }}</td>
                        <td style="text-align: right;">{{ number_format($composant->qte, 2, ',', ' ') }}</td>
                        <td style="text-align: right;">{{ number_format($composant->prix_unitaire, 2, ',', ' ') }}</td>
                        <td style="text-align: right;">{{ number_format($composant->montant, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="5" style="text-align: right;">Total {{ $niveauNom }} :</td>
                    <td style="text-align: right;">{{ number_format($items->first()['total'], 2, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <!-- TOTAL GENERAL -->
    <div class="total-section">
        <h3 style="font-size: 16px; margin: 0;">
            TOTAL GÉNÉRAL HT : {{ number_format($totalGeneral, 2, ',', ' ') }} {{ $devise }}
        </h3>
        <p style="margin: 5px 0;">TVA non applicable, article 293 B du CGI</p>
    </div>

    <!-- CONDITIONS -->
    <div style="margin-top: 40px;">
        <p><strong>Conditions de règlement :</strong></p>
        <p>30% à la commande, 40% à mi-parcours, 30% à la livraison</p>
    </div>

    <!-- SIGNATURES -->
    <div style="margin-top: 60px; display: flex; justify-content: space-between;">
        <div style="width: 45%;">
            <p>Bon pour accord,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>Nom et signature du client</p>
            <p>Date : ____/____/________</p>
        </div>
        <div style="width: 45%;">
            <p>Le prestataire,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>Nom et signature</p>
            <p>Date : {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | Page 1/1</p>
    </div>
</body>
</html>
