<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nouvelle commande reçue</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f2f4f6;
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      -webkit-font-smoothing: antialiased;
    }
    img { display: block; border: 0; }
    a { text-decoration: none; }

    @media only screen and (max-width: 620px) {
      .inner { padding: 16px !important; }
      .header-logo { width: 110px !important; }
      .button { width: 100% !important; display: block !important; }
    }
  </style>
</head>
<body>

  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f2f4f6" role="presentation">
    <tr>
      <td align="center" style="padding: 24px 10px;">

        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; border-radius:8px; overflow:hidden;">

          <!-- Header -->
          <tr>
            <td style="background:#0d6efd; padding:30px; text-align:center;">
              <img src="{{ asset('logo/logo.png') }}" alt="Logo" width="130" class="header-logo" style="margin:0 auto 10px;">
              <h2 style="color:#ffffff; font-size:22px; margin:0;">Nouvelle commande reçue</h2>
            </td>
          </tr>

          <!-- Content -->
          <tr>
            <td style="background:#ffffff; padding:25px; border:1px solid #e6e6e6;" class="inner">

              <p style="font-size:15px; color:#333; margin:0 0 16px;">
                Vous avez reçu une nouvelle commande.
              </p>

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:10px 0 20px;">
                <tr>
                  <td style="background:#f8f9fa; padding:12px; border-radius:8px; font-size:15px; color:#333; line-height:1.6;">
                    <strong>Numéro de commande :</strong><br>
                    {{ $order->order_number }}

                    <br><br>

                    <strong>Total :</strong><br>
                    {{ number_format($order->total_amount, 3) }} TND
                  </td>
                </tr>
              </table>

              <!-- Button -->
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td align="center">
                    <a href="{{ route('vendor.orders') }}"
                       target="_blank"
                       class="button"
                       style="
                         display:inline-block;
                         background:#0d6efd;
                         padding:12px 22px;
                         color:white;
                         font-weight:600;
                         border-radius:8px;
                         text-decoration:none;
                       ">
                      Voir mes commandes
                    </a>
                  </td>
                </tr>
              </table>

              <p style="font-size:14px; color:#777; margin-top:24px; text-align:left;">
                Merci,<br>
                {{ config('app.name') }}
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:12px; font-size:12px; color:#999;">
              &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
