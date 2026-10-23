<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mise à jour de commande</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f2f4f6;
      -webkit-font-smoothing: antialiased;
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    img { border: 0; outline: none; text-decoration: none; display: block; }
    a { color: #0d6efd; text-decoration: none; }

    @media only screen and (max-width: 620px) {
      .inner { padding: 16px !important; }
      .header-logo { width: 120px !important; }
      .button { width: 100% !important; display: block !important; }
    }
  </style>
</head>
<body>

  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" bgcolor="#f2f4f6">
    <tr>
      <td align="center" style="padding: 24px 10px;">

        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; width:100%; border-radius:8px; overflow:hidden;">
          
          <!-- Header -->
          <tr>
            <td align="center" style="background-color:#0d6efd; padding:30px; text-align:center;">
              <img src="{{ asset('logo/logo.png') }}" alt="Logo" width="150" class="header-logo" style="margin:0 auto 10px; display:block;">
              <h2 style="color:white; font-size:22px; font-weight:600; margin:0;">
                Mise à jour de votre commande
              </h2>
            </td>
          </tr>

          <!-- Content -->
          <tr>
            <td style="background:#ffffff; border:1px solid #e6e6e6; padding:25px;" class="inner">

              <p style="font-size:15px; color:#333; line-height:1.6; margin:0 0 16px 0;">
                Le statut de votre commande a été mis à jour.
              </p>

              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:14px 0 18px 0;">
                <tr>
                  <td style="background:#f8f9fa; padding:15px; border-radius:8px; color:#333; font-size:15px; line-height:1.6;">
                    <strong>Numéro de commande :</strong>
                    <div style="margin-top:5px;">{{ $order->order_number }}</div>

                    <strong style="display:block; margin-top:12px;">Nouveau statut :</strong>
                    <div style="margin-top:5px;">{{ ucfirst($order->status) }}</div>
                  </td>
                </tr>
              </table>

              <!-- Button -->
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td align="center">
                    <a href="{{ route('customer.orders.show', $order) }}"
                       target="_blank"
                       style="
                         background:#0d6efd;
                         padding:12px 22px;
                         border-radius:8px;
                         color:white;
                         font-weight:600;
                         text-decoration:none;
                         display:inline-block;
                         -webkit-text-size-adjust:none;
                       "
                       class="button">
                      Voir la commande
                    </a>
                  </td>
                </tr>
              </table>

              <p style="text-align:center; color:#777; font-size:14px; margin:26px 0 0;">
                Merci,<br>
                {{ config('app.name') }}
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:12px; color:#9aa0a6; font-size:12px;">
              &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
