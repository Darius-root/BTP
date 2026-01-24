<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .corps-etat {
            background-color: #d9d9d9;
            font-weight: bold;
        }

        .lot {
            background-color: #efefef;
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            background-color: #e6e6e6;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    {{-- =========================
        EN-TÊTE
    ========================== --}}
    <div class="text-center mb-20">
        <h2>{{ $organisation->raison_sociale ?? '' }}</h2>
        <div>{{ $organisation->adresse ?? '' }}</div>
        <div>{{ $organisation->telephone ?? '' }}</div>
    </div>

    <table class="no-border mb-20">
        <tr>
            <td width="60%">
                <strong>Projet :</strong> {{ $projet->intitule ?? '' }}<br>
                <strong>Bâtiment :</strong> {{ $batiment->intitule ?? '' }}<br>
                <strong>Localisation :</strong> {{ $batiment->localisation ?? '' }}
            </td>
            <td width="40%">
                <strong>Devis N° :</strong> {{ $devis->code }}<br>
                <strong>Date :</strong> {{ optional($devis->created_at)->format('d/m/Y') }}<br>
                <strong>Devise :</strong> {{ $devise }}
            </td>
        </tr>
    </table>

    <h3 class="text-center mb-10">
        DEVIS ESTIMATIF QUANTITATIF
    </h3>

    {{-- =========================
        TABLEAU PRINCIPAL
    ========================== --}}
    <table>
        <thead>
            <tr>
                <th width="8%">Code</th>
                <th>Désignation</th>
                <th width="8%" class="text-center">Unité</th>
                <th width="10%" class="text-right">Quantité</th>
                <th width="12%" class="text-right">P.U</th>
                <th width="14%" class="text-right">Montant</th>
            </tr>
        </thead>

        <tbody>
        @foreach ($corpsEtats as $corpsEtat)
            {{-- Corps d’état --}}
            <tr class="corps-etat">
                <td colspan="6">
                    {{ $corpsEtat['intitule'] }}
                </td>
            </tr>

            @foreach ($corpsEtat['lots'] as $lot)
                {{-- Lot --}}
                <tr class="lot">
                    <td>{{ $lot['code'] }}</td>
                    <td colspan="4">{{ $lot['intitule'] }}</td>
                    <td class="text-right">
                        {{ number_format($lot['sous_total'], 0, ',', ' ') }}
                    </td>
                </tr>

                {{-- Composants --}}
                @foreach ($lot['composants'] as $comp)
                    <tr>
                        <td>{{ $comp['code'] }}</td>
                        <td>{{ $comp['designation'] }}</td>
                        <td class="text-center">{{ $comp['unite']['code'] ?? '' }}</td>
                        <td class="text-right">
                            {{ number_format($comp['quantite'], 2, ',', ' ') }}
                        </td>
                        <td class="text-right">
                            {{ number_format($comp['prix_unitaire'], 0, ',', ' ') }}
                        </td>
                        <td class="text-right">
                            {{ number_format($comp['montant'], 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Total corps d’état --}}
            <tr class="total">
                <td colspan="5" class="text-right">
                    Total {{ $corpsEtat['intitule'] }}
                </td>
                <td class="text-right">
                    {{ number_format($corpsEtat['total'], 0, ',', ' ') }}
                </td>
            </tr>
        @endforeach
        </tbody>

        {{-- =========================
            TOTAL GÉNÉRAL
        ========================== --}}
        <tfoot>
            <tr class="total">
                <td colspan="5" class="text-right">
                    TOTAL GÉNÉRAL
                </td>
                <td class="text-right">
                    {{ number_format($totalGeneral, 0, ',', ' ') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- =========================
        SIGNATURES
    ========================== --}}
    <table class="no-border mb-20" style="margin-top: 40px;">
        <tr>
            <td width="50%" class="text-center">
                <strong>Le Maître d’Ouvrage</strong><br><br><br>
                ___________________________
            </td>
            <td width="50%" class="text-center">
                <strong>L’Entreprise</strong><br><br><br>
                ___________________________
            </td>
        </tr>
    </table>

</body>
</html>
