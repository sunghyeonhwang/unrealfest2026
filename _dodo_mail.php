<?php
/* Unreal Fest Seoul 2026 — 해외 등록 완료 안내 이메일 (_dodo_mail.php)
 * ufs_dodo_confirm_mail($row) -> array('subject','html','text'). $row = cb_unreal_2026_event2_apply 행(확정 후).
 * 영문. QR 이미지(공개 URL) + myticket 조회 링크. 브랜디드 셸은 _coupon_mail 과 동일. PHP 7.0.
 */
if (!function_exists('ufs_dodo_confirm_mail')) {
function ufs_dodo_confirm_mail($row, $amount_label = '') {
    $e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $apply_no = (int)$row['apply_no'];
    $name  = isset($row['apply_user_name']) ? trim($row['apply_user_name']) : '';
    $prod  = isset($row['apply_product_name']) ? $row['apply_product_name'] : '';
    $price = isset($row['apply_product_price']) ? (int)$row['apply_product_price'] : 0;
    // 금액 표기: override(예: PayPal "$89.00 USD") 있으면 사용, 없으면 KRW
    $amount_html = ($amount_label !== '') ? $e($amount_label) : ('&#8361;'.number_format($price).' (KRW)');
    $base  = 'https://epiclounge.co.kr/unrealfest2026/';
    $qr    = $base.'qrdata/'.$apply_no.'.jpg';
    $link  = $base.'myticket.php?lang=en';

    $subject   = '[Unreal Fest Seoul 2026] Registration Confirmed';
    $preheader = 'Your registration for Unreal Fest Seoul 2026 is confirmed. Your QR admission code is inside.';
    $title     = 'Registration Confirmed';

    $qrbox = '<div style="margin:14px 0;padding:18px;background:#f4f7f8;border:1px solid #e0e6e8;text-align:center;">'
           . '<img src="'.$e($qr).'" alt="Admission QR" width="180" style="display:block;margin:0 auto 8px;max-width:180px;height:auto;">'
           . '<span style="color:#595959;font-size:13px;">Present this QR code at the venue entrance.</span></div>';

    $body = ($name !== '' ? $e($name).', ' : '').'thank you for registering for '
          . '<strong class="inter-bold700" style="font-weight:700;">Unreal Fest Seoul 2026</strong> '
          . '(August 20–21, 2026 · COEX, Seoul).<br><br>'
          . 'Your payment has been received and your registration is <strong class="inter-bold700" style="font-weight:700;">confirmed</strong>.<br><br>'
          . 'Ticket: <strong class="inter-bold700" style="font-weight:700;">'.$e($prod).'</strong><br>'
          . 'Amount paid: '.$amount_html
          . $qrbox
          . 'You can also view your ticket anytime using the button below (look up by email and phone). '
          . 'The event T-shirt and goods are picked up on-site at the venue.';
    $cta = 'View my ticket';
    $textbody = "Unreal Fest Seoul 2026 — Registration confirmed.\nTicket: ".$prod."\nQR: ".$qr."\nView ticket: ".$link;

    $tpl = <<<'HTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" style="width: 100%;"><head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>{{TITLE}}</title>
<style type="text/css">
body { width: 100% !important; background-color: #FFFFFF; color: #000001; }
body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; padding: 0; margin: 0; }
table td { border-collapse: collapse !important; }
img { border: 0; height: auto; outline: none; text-decoration: none; max-width: 100%; }
.container { background-color: #FFFFFF; color: #000001; }
.inter-bold700 { font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-weight: 700; }
a { color: #157EAF; }
@media screen and (max-width: 700px) { .container { width: 100% !important; } .med-full { width: 100% !important; max-width: 100% !important; } .m-title-size { font-size: 22px !important; line-height: 32px !important; } .l-pad-20 { padding-left: 20px !important; padding-right: 20px !important; } .l-pad-24 { padding-left: 24px !important; padding-right: 24px !important; } }
</style>
</head>
<body style="color: #000001; margin: 0; padding: 0; width: 100% !important;" bgcolor="#FFFFFF">
<div style="display: none; max-height: 0px; overflow: hidden;">{{PREHEADER}}</div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 0; padding: 0; table-layout: fixed;" role="presentation"><tbody><tr>
<td align="center" valign="top" style="margin: 0; padding: 0;">
<table cellpadding="0" cellspacing="0" border="0" align="center" width="700" style="color: #000001; margin: 0; padding: 0; width: 700px;" class="container" role="presentation" bgcolor="#FFFFFF"><tbody>
<tr><td align="center" valign="top" style="margin: 0; padding: 0;">
<a href="https://www.unrealengine.com/" target="_blank" style="text-decoration: none;">
<img alt="Unreal Engine" border="0" src="https://images.email.unrealengine.com/images/2a8b51ad8aa80bebad0be4eed0f419f0/lightmode-opt/ue-header-dark-desktop.jpg" style="display: block; height: auto; max-width: 100%; outline: none; text-decoration: none;" width="700" class="med-full m-full"></a>
</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0;">
<a href="{{LINK}}" target="_blank" style="text-decoration: none;">
<img alt="Unreal Fest Seoul 2026" border="0" src="https://unrealsummit16.cafe24.com/2026/ufs26/ufs26_mail_epicgames/main_key_1920x1080.jpg" style="border-width: 0; display: block; height: auto; max-width: 100%; outline: none; text-decoration: none;" width="700" class="med-full m-full"></a>
</td></tr>
<tr><td align="left" valign="top" style="margin: 0; padding: 24px 20px 62px;" class="l-pad-20">
<table cellpadding="0" cellspacing="0" border="0" width="100%" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="left" dir="ltr" valign="top" style="color: #000001; font-family: 'Inter Tight','Noto Sans KR',Arial,sans-serif; font-size: 24px; font-weight: 900; line-height: 32px; margin: 0; padding: 0 0 16px; word-break: keep-all;" class="m-title-size">{{TITLE}}</td></tr>
<tr><td align="left" dir="ltr" valign="top" style="color: #000001; font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-size: 17px; font-weight: 400; line-height: 1.5; margin: 0; padding: 0 0 32px; word-break: keep-all;">
<p dir="ltr" style="text-align: left; margin: 0; padding: 0; font-family: inherit; font-size: inherit; line-height: inherit;">{{BODY}}</p>
</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0;">
<table align="center" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="center" valign="middle" bgcolor="#00C1D5" style="border-radius: 0; color: #000001; font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-size: 14px; font-weight: 500; line-height: 20px; margin: 0; padding: 0;">
<a dir="ltr" href="{{LINK}}" target="_blank" style="color: #000001; display: block; margin: 0; padding: 14px 28px; text-decoration: none; font-weight: 700;">{{CTA_LABEL}}</a>
</td></tr></tbody></table>
</td></tr>
</tbody></table>
</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0 48px 80px;" class="l-pad-24">
<table cellpadding="0" cellspacing="0" border="0" width="100%" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="center" valign="top" style="border-top-color: #999999; border-top-style: solid; border-top-width: 1px; margin: 0; padding: 40px 0 24px;">
<a href="https://epiclounge.co.kr/" target="_blank" style="color: #157EAF; margin: 0; padding: 0;">
<img src="https://unrealsummit16.cafe24.com/2026/start_unreal/logo_black.png" alt="Epic Lounge" width="220" border="0" style="border-width: 0; display: block; height: auto; max-width: 100%; outline: none; text-decoration: none;"></a>
</td></tr>
<tr><td align="center" valign="top" style="color: #595959; font-family: 'Inter',Arial,sans-serif; font-size: 12px; font-weight: 400; line-height: 18px; margin: 0; padding: 0 0 8px;">
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">GRIFF Inc. (Epic Lounge)&nbsp;|&nbsp;Business Reg. No. 859-88-00263</p>
</td></tr>
<tr><td align="center" valign="top" style="color: #595959; font-family: 'Inter',Arial,sans-serif; font-size: 12px; font-weight: 400; line-height: 18px; margin: 0; padding: 0;">
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">
<a style="color: #26bbff; padding: 2px 4px; text-decoration: underline;" href="https://epiclounge.co.kr/unrealfest2026/legal-en.php#terms" target="_blank">Terms</a>
&nbsp;|&nbsp;
<a style="color: #26bbff; padding: 2px 4px; text-decoration: underline;" href="https://epiclounge.co.kr/unrealfest2026/legal-en.php#refund" target="_blank">Refund Policy</a>
&nbsp;|&nbsp;
<a style="color: #26bbff; padding: 2px 4px; text-decoration: underline;" href="https://epiclounge.co.kr/unrealfest2026/legal-en.php#privacy" target="_blank">Privacy</a>
</p>
</td></tr>
</tbody></table>
</td></tr>
</tbody></table>
</td></tr></tbody></table>
</body></html>
HTML;

    $html = strtr($tpl, array(
        '{{TITLE}}'     => $title,
        '{{PREHEADER}}' => $e($preheader),
        '{{BODY}}'      => $body,
        '{{CTA_LABEL}}' => $e($cta),
        '{{LINK}}'      => $e($link),
    ));
    return array('subject'=>$subject, 'html'=>$html, 'text'=>$textbody);
}}
