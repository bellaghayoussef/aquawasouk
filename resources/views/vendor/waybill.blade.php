<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bordereau de Livraison #{{ $order->order_number }}</title>
    <style>
        @page {
            margin: 15mm 12mm;
            size: A5 portrait;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px dashed #94a3b8;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .marketplace-badge {
            font-size: 18px;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-sub {
            font-size: 9px;
            color: #64748b;
        }
        .order-meta {
            text-align: right;
            float: right;
        }
        .order-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        .barcode-box {
            margin-top: 6px;
            text-align: right;
        }
        .barcode-bars {
            display: inline-block;
            font-family: monospace;
            font-size: 22px;
            letter-spacing: 3px;
            background: #f1f5f9;
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            font-weight: bold;
        }
        .clear {
            clear: both;
        }

        /* COD BOX (Cash on Delivery) - HIGHLIGHTED */
        .cod-alert-box {
            background-color: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 6px;
            padding: 10px 14px;
            margin: 12px 0;
            text-align: center;
        }
        .cod-label {
            font-size: 11px;
            font-weight: bold;
            color: #991b1b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cod-amount {
            font-size: 24px;
            font-weight: 800;
            color: #dc2626;
            margin: 2px 0;
        }
        .cod-note {
            font-size: 9px;
            color: #b91c1c;
        }

        /* 2-Columns for Expediteur & Destinataire */
        .parties-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .party-col {
            width: 50%;
            vertical-align: top;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        .party-col.dest {
            background-color: #f8fafc;
            border-left: 4px solid #0284c7;
        }
        .party-col.exp {
            background-color: #ffffff;
            border-left: 4px solid #64748b;
        }
        .party-title {
            font-size: 10px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .party-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .party-phone {
            font-size: 13px;
            font-weight: 800;
            color: #0284c7;
            margin: 3px 0;
        }
        .party-address {
            font-size: 10px;
            color: #334155;
            line-height: 1.3;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
        }
        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 10px;
        }
        .variant-pill {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 9px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 4px;
            margin-top: 2px;
        }

        /* Footer instructions */
        .footer {
            margin-top: 14px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            font-size: 8.5px;
            color: #64748b;
            text-align: center;
        }
        .signature-box {
            width: 100%;
            margin-top: 12px;
        }
        .sig-col {
            width: 48%;
            float: left;
            border: 1px dashed #94a3b8;
            padding: 6px;
            height: 40px;
            font-size: 8.5px;
            color: #64748b;
        }
        .sig-col.right {
            float: right;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div style="float: left; width: 50%;">
            <div class="marketplace-badge">AquawaSouk</div>
            <div class="badge-sub">BORDEREAU D'EXPÉDITION OFFICIEL (WAYBILL)</div>
        </div>
        <div class="order-meta">
            <div class="order-title">N° {{ $order->order_number }}</div>
            <div style="font-size: 9px; color: #64748b;">Date: {{ $order->created_at->format('d/m/Y H:i') }}</div>
            <div class="barcode-box">
                <div class="barcode-bars">*{{ $order->order_number }}*</div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- ENCAISSEMENT COD (CASH ON DELIVERY) -->
    @php
        $vendorItemsTotal = $items->sum(function($item) {
            return $item->price * $item->quantity;
        });
        $shipping = $vendor->shipping_price ?? 0;
        $totalToCollect = $vendorItemsTotal + $shipping;
    @endphp

    <div class="cod-alert-box">
        <div class="cod-label">⚠️ MONTANT TOTAL À ENCAISSER À LA LIVRAISON (COD)</div>
        <div class="cod-amount">{{ number_format($totalToCollect, 2) }} DT</div>
        <div class="cod-note">Articles: {{ number_format($vendorItemsTotal, 2) }} DT + Livraison: {{ number_format($shipping, 2) }} DT | Mode: Espèces</div>
    </div>

    <!-- 2 Colonnes : Expéditeur & Destinataire -->
    <table class="parties-table">
        <tr>
            <!-- DESTINATAIRE (CLIENT) -->
            <td class="party-col dest" style="margin-right: 6px;">
                <div class="party-title">📦 DESTINATAIRE (LIVRAISON À)</div>
                <div class="party-name">{{ $order->user ? $order->user->name : 'Client' }}</div>
                <div class="party-phone">📞 {{ $order->phone }}</div>
                <div class="party-address">
                    <strong>Gouvernorat :</strong> {{ $order->shipping_city }}<br>
                    <strong>Adresse :</strong> {{ $order->shipping_address }}
                    @if($order->shipping_postal_code)
                        ({{ $order->shipping_postal_code }})
                    @endif
                    @if($order->notes)
                        <br><strong style="color: #dc2626;">Note livraison :</strong> {{ $order->notes }}
                    @endif
                </div>
            </td>

            <!-- EXPÉDITEUR (BOUTIQUE VENDEUR) -->
            <td class="party-col exp" style="margin-left: 6px;">
                <div class="party-title">🏪 EXPÉDITEUR (BOUTIQUE)</div>
                <div class="party-name">{{ $vendor->shop_name }}</div>
                <div class="party-phone">📞 {{ $vendor->phone ?? 'Non spécifié' }}</div>
                <div class="party-address">
                    <strong>Ville :</strong> {{ $vendor->city ?? 'Tunisie' }}<br>
                    <strong>Adresse :</strong> {{ $vendor->address ?? 'Boutique vérifiée AquawaSouk' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- TABLEAU DES ARTICLES DU COLIS -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 55%;">Article & Déclinaison</th>
                <th style="width: 15%; text-align: center;">Qté</th>
                <th style="width: 15%; text-align: right;">Prix unit.</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <strong style="color: #0f172a;">{{ $item->product->name }}</strong>
                    @if(!empty($item->size) || !empty($item->color))
                        <br>
                        <span class="variant-pill">
                            @if(!empty($item->size)) Taille: {{ $item->size }} @endif
                            @if(!empty($item->size) && !empty($item->color)) | @endif
                            @if(!empty($item->color)) Couleur: {{ $item->color }} @endif
                        </span>
                    @endif
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 12px;">x{{ $item->quantity }}</td>
                <td style="text-align: right;">{{ number_format($item->price, 2) }} DT</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($item->price * $item->quantity, 2) }} DT</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature Livreurs -->
    <div class="signature-box">
        <div class="sig-col">
            Signature et cachet du transporteur :
        </div>
        <div class="sig-col right">
            Signature du client à la réception :
        </div>
        <div class="clear"></div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Bordereau édité via la marketplace AquawaSouk • En cas de problème de livraison, contactez le vendeur au {{ $vendor->phone ?? 'service client' }}.
    </div>
</body>
</html>
