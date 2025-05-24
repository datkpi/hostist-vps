<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title> Order confirmation </title>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width; initial-scale=1.0;" />
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);

        body {
            margin: 0;
            padding: 0;
            background: #e1e1e1;
        }

        div,
        p,
        a,
        li,
        td {
            -webkit-text-size-adjust: none;
        }

        .ReadMsgBody {
            width: 100%;
            background-color: #ffffff;
        }

        .ExternalClass {
            width: 100%;
            background-color: #ffffff;
        }

        body {
            width: 100%;
            height: 100%;
            background-color: #e1e1e1;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        html {
            width: 100%;
        }

        p {
            padding: 0 !important;
            margin-top: 0 !important;
            margin-right: 0 !important;
            margin-bottom: 0 !important;
            margin-left: 0 !important;
        }

        .visibleMobile {
            display: none;
        }

        .hiddenMobile {
            display: block;
        }

        @media only screen and (max-width: 600px) {
            body {
                width: auto !important;
            }

            table[class=fullTable] {
                width: 96% !important;
                clear: both;
            }

            table[class=fullPadding] {
                width: 85% !important;
                clear: both;
            }

            table[class=col] {
                width: 45% !important;
            }

            .erase {
                display: none;
            }
        }

        @media only screen and (max-width: 420px) {
            table[class=fullTable] {
                width: 100% !important;
                clear: both;
            }

            table[class=fullPadding] {
                width: 85% !important;
                clear: both;
            }

            table[class=col] {
                width: 100% !important;
                clear: both;
            }

            table[class=col] td {
                text-align: left !important;
            }

            .erase {
                display: none;
                font-size: 0;
                max-height: 0;
                line-height: 0;
                padding: 0;
            }

            .visibleMobile {
                display: block !important;
            }

            .hiddenMobile {
                display: none !important;
            }
        }
    </style>

</head>

<body>

    <!-- Header -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
        bgcolor="#e1e1e1">
        <tr>
            <td height="20"></td>
        </tr>
        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                    bgcolor="#ffffff" style="border-radius: 10px 10px 0 0;">
                    <tr class="hiddenMobile">
                        <td height="40"></td>
                    </tr>
                    <tr class="visibleMobile">
                        <td height="30"></td>
                    </tr>

                    <tr>
                        <td>
                            <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td>
                                            <table width="220" border="0" cellpadding="0" cellspacing="0"
                                                align="left" class="col">
                                                <tbody>
                                                    <tr>
                                                        <td align="left"> <img
                                                                src="http://www.supah.it/dribbble/017/logo.png"
                                                                width="32" height="32" alt="logo"
                                                                border="0" /></td>
                                                    </tr>
                                                    <tr class="hiddenMobile">
                                                        <td height="40"></td>
                                                    </tr>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 12px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: left;">
                                                            Hello, {{ $user->name ?? 'Customer' }}.
                                                            <br> Thank you for shopping from our store and for your
                                                            order.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table width="220" border="0" cellpadding="0" cellspacing="0"
                                                align="right" class="col">
                                                <tbody>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td height="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 21px; color: #ff0000; letter-spacing: -1px; font-family: 'Open Sans', sans-serif; line-height: 1; vertical-align: top; text-align: right;">
                                                            Invoice
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                    <tr class="hiddenMobile">
                                                        <td height="50"></td>
                                                    </tr>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 12px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: right;">
                                                            <small>ORDER</small> #800000025<br />
                                                            <small>CREATED DATE: {{ $quoteDate ?? '20/05/2025' }}<br />
                                                                VALID FOR: {{ $validity ?? '30 days' }}</small>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- /Header -->
    <!-- Tiêu đề nội dung -->
    <table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#dddddd"
        style="margin-top: 10px;">
        <tr>
            <td align="center" class="bg-gray bold">
                CONTENTS: QUOTATION FOR {{ strtoupper($cart->items[0]->product->type ?? 'SSL') }} PACKAGE FOR WEBSITE
            </td>
        </tr>
    </table>
    <!-- Order Details -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
        bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                        class="fullTable" bgcolor="#ffffff">
                        <tbody>
                            <tr>
                            <tr class="hiddenMobile">
                                <td height="60"></td>
                            </tr>
                            <tr class="visibleMobile">
                                <td height="40"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                        class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;"
                                                    width="52%" align="left">
                                                    ITEM/DESCRIPTION
                                                </th>
                                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                    align="left">
                                                    <small>SERVER</small>
                                                </th>
                                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                    align="center">
                                                    Quantity
                                                </th>
                                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                    align="right">
                                                    Subtotal
                                                </th>
                                            </tr>
                                            <tr>
                                                <td height="1" style="background: #bebebe;" colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td height="10" colspan="4"></td>
                                            </tr>
                                            @foreach ($cart->items as $index => $item)
                                                @php
                                                    $options = json_decode($item->options, true) ?: [];
                                                    $period = $options['period'] ?? 1;
                                                    $domain = $options['domain'] ?? null;
                                                    $server = isset($options['server'])
                                                        ? $options['server']
                                                        : 'Không giới hạn';
                                                    $keypair = isset($options['keypair'])
                                                        ? $options['keypair']
                                                        : 'Không giới hạn';
                                                @endphp
                                                <tr>
                                                    <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #ff0000;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                        class="article">
                                                        Providing international public digital certificate
                                                        {{ $item->product->name ?? 'SSL' }} for website domain.<br /> -
                                                        Package: 01
                                                        {{ $item->product->name ?? 'SSL Certificate' }}<br /> - Domain
                                                        in use:
                                                        {{ $domain ? '*.' . $domain : 'N/A' }}<br /> - Verification
                                                        level: Domain verification<br /><br />
                                                        Included:<br /> - Direct certificate management account access
                                                        (https://gcc.globalsign.com)<br /> -
                                                        Unlimited server installations<br /> - Unlimited keypairs for
                                                        server use<br /> - Support and
                                                        troubleshooting within 24 hours<br /> - Valid products/services
                                                        with genuine origin, receiving
                                                        technical support and after-sales warranty service according to
                                                        supplier standards.
                                                    </td>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;">
                                                        <small>{{ $server }}</small></td>
                                                    <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                        align="center">{{ $item->quantity }}</td>
                                                    <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                        align="right">
                                                        {{ number_format($item->subtotal, 0, ',', '.') }}/đ/year</td>
                                                </tr>

                                                <tr>
                                                    <td height="1" colspan="4"
                                                        style="border-bottom:1px solid #e4e4e4"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Order Details -->
    <!-- Total -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
        bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                        class="fullTable" bgcolor="#ffffff">
                        <tbody>
                            <tr>
                                <td>

                                    <!-- Table Total -->
                                    <table width="480" border="0" cellpadding="0" cellspacing="0"
                                        align="center" class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <td
                                                    style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Subtotal
                                                </td>
                                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;"
                                                    width="80">
                                                    {{ number_format($total, 0, ',', '.') }} đ
                                                </td>
                                            </tr>
                                            @if (isset($discount) && $discount > 0)
                                                <tr>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        Giảm giá
                                                    </td>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        {{ number_format($discount, 0, ',', '.') }} đ
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        <strong>Total after discount</strong>
                                                    </td>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        <strong>{{ number_format($total - $discount, 0, ',', '.') }}
                                                            đ</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        <small>TAX</small></td>
                                                    <td
                                                        style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; ">
                                                        <small>{{ number_format($discount, 0, ',', '.') }}</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <!-- /Table Total -->

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Total -->
    <!-- Information -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
        bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                        class="fullTable" bgcolor="#ffffff">
                        <tbody>
                            <tr>
                            <tr class="hiddenMobile">
                                <td height="60"></td>
                            </tr>
                            <tr class="visibleMobile">
                                <td height="40"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="480" border="0" cellpadding="0" cellspacing="0"
                                        align="center" class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width="220" border="0" cellpadding="0"
                                                        cellspacing="0" align="left" class="col">

                                                        <tbody>
                                                            <tr>
                                                                <td class="bg-gray bold">PROVIDER</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    {{ $config->company_name ?? 'Hostist company' }}<br />
                                                                    {{ $config->company_address ?? '5335 Gate Pkwy, 2nd Floor, Jacksonville, FL 32256' }}<br />
                                                                    Email:
                                                                    {{ $config->support_email ?? 'supporthostit@gmail.com' }}<br />
                                                                    URL: {{ $config->website ?? 'www.hostist.com' }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>


                                                    <table width="220" border="0" cellpadding="0"
                                                        cellspacing="0" align="right" class="col">
                                                        <tbody>
                                                            <tr>
                                                                <td class="bg-gray bold">CLIENT</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    {{ $user->name ?? 'Customer' }}<br />
                                                                    Address:
                                                                    {{ $user->address ?? 'Address not provided' }}<br />
                                                                    Phone: {{ $user->phone ?? 'N/A' }}<br />
                                                                    Email: {{ $user->email ?? '' }}<br />
                                                                    @if (isset($user->customer) && isset($user->customer->website))
                                                                        URL: {{ $user->customer->website }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="480" border="0" cellpadding="0" cellspacing="0"
                                        align="center" class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width="220" border="0" cellpadding="0"
                                                        cellspacing="0" align="left" class="col">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <p><b>Payment Information</b></p>
                                                                    <p><b>Amount:</b>
                                                                        {{ number_format($total, 0, ',', '.') }} đ</p>
                                                                    <p><b>Bank:</b> {{ $config->bank_name ?? 'ACB' }}
                                                                    </p>
                                                                    <p><b>Account Number:</b>
                                                                        {{ $config->company_bank_account_number ?? '218906666' }}
                                                                    </p>
                                                                    <p><b>Account Holder:</b>
                                                                        {{ $config->company_name ?? 'Hostist company' }}
                                                                    </p>
                                                                    <p><b>Reference:</b>
                                                                        {{ str_replace('QUOTE-', 'INV', $quoteNumber) }}
                                                                    </p>
                                                                    <p><b>Expiration Date:</b> {{ $expireDate }}</p>

                                                                    <div align="center" style="margin-top: 5px;">
                                                                        <p>QR Code:</p>
                                                                        @if (isset($qrCodePath) && file_exists($qrCodePath))
                                                                            <img src="{{ $qrCodePath }}"
                                                                                alt="QR Code"
                                                                                style="width: 80px; height: 80px; border: 1px solid #ddd; padding: 3px; background-color: white;" />
                                                                        @else
                                                                            <div
                                                                                style="width: 80px; height: 80px; border: 1px solid #ddd; padding: 3px; background-color: white; margin: 0 auto;">
                                                                                QR Code</div>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>


                                                    <table width="220" border="0" cellpadding="0"
                                                        cellspacing="0" align="right" class="col">
                                                        <tbody>
                                                            <tr>
                                                                <td><b>Standard Technical Specifications:</b></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <ul style="margin: 0; padding-left: 20px;">
                                                                        @if (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'ssl')
                                                                            <li>Certificate Type:
                                                                                {{ $cart->items[0]->product->name ?? 'SSL Certificate' }}
                                                                            </li>
                                                                            <li>Website domain verification</li>
                                                                            <li>Key length from 2048 bit</li>
                                                                            <li>Security standard from 128 bit to 256
                                                                                bit - RSA & DSA Algorithm Support</li>
                                                                            @if (strpos(strtolower($cart->items[0]->product->name ?? ''), 'wildcard') !== false)
                                                                                <li>Wildcard extension support</li>
                                                                            @endif
                                                                            <li>Secure Site Seal:
                                                                                {{ strpos(strtolower($cart->items[0]->product->name ?? ''), 'alpha') !== false ? 'Alpha Seal' : 'Secure Seal' }}
                                                                            </li>
                                                                            <li>Unlimited reissues and number of digital
                                                                                certificates issued</li>
                                                                            @if (strpos(strtolower($cart->items[0]->product->name ?? ''), 'wildcard') !== false)
                                                                                <li>Unlimited first-level subdomains
                                                                                    using digital certificate (*.*)</li>
                                                                            @endif
                                                                            <li>Compatible with 99.999% of browsers and
                                                                                operating systems</li>
                                                                            <li>Certificate warranty coverage of $10,000
                                                                                USD</li>
                                                                        @elseif (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'hosting')
                                                                            <li>Operating System: Linux</li>
                                                                            <li>Control Panel: cPanel</li>
                                                                            <li>PHP 5.6 - 8.2</li>
                                                                            <li>MySQL 5.7+</li>
                                                                            <li>Free Let's Encrypt SSL</li>
                                                                            <li>Daily Backup</li>
                                                                            <li>Anti-DDoS Protection</li>
                                                                            <li>99.9% Uptime Guarantee</li>
                                                                            <li>24/7 Technical Support</li>
                                                                        @elseif (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'domain')
                                                                            <li>Full DNS management</li>
                                                                            <li>Domain theft protection</li>
                                                                            <li>Email forwarding</li>
                                                                            <li>URL forwarding</li>
                                                                            <li>Custom nameservers</li>
                                                                            <li>Domain lock against unauthorized
                                                                                transfers</li>
                                                                            <li>Auto-renewal (optional)</li>
                                                                        @else
                                                                            <li>24/7 technical support</li>
                                                                            <li>Warranty according to manufacturer
                                                                                standards</li>
                                                                            <li>Latest version updates</li>
                                                                            <li>User documentation</li>
                                                                        @endif
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr class="hiddenMobile">
                                <td height="60"></td>
                            </tr>
                            <tr class="visibleMobile">
                                <td height="30"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Information -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
        bgcolor="#e1e1e1">

        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                    class="fullTable" bgcolor="#ffffff" style="border-radius: 0 0 10px 10px;">
                    <tr>
                        <td>
                            <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td
                                            style="font-size: 12px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: left;">
                                            Have a nice day.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class="spacer">
                        <td height="50"></td>
                    </tr>

                </table>
            </td>
        </tr>
        <tr>
            <td height="20"></td>
        </tr>
    </table>
</body>

</html>
