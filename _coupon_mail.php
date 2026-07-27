<?php
/* Unreal Fest Seoul 2026 — 쿠폰 발급 안내 이메일 템플릿 (_coupon_mail.php)
 * ufs_coupon_mail($row, $lang) -> array('subject','html','text'). $row = cb_unreal_2026_coupon 행.
 * 초대장(_invite_mail)과 성격 다름: 개인 정식 등록(ticket-all.php, 본인인증·카드) + 쿠폰 할인.
 * 링크 = ticket-all.php?coupon=코드 (쿠폰 자동 프리필). 브랜디드 셸은 초대장과 동일. PHP 7.0.
 */
if (!function_exists('ufs_coupon_mail')) {
function ufs_coupon_mail($row, $lang = 'ko') {
    $lang = ($lang === 'en') ? 'en' : 'ko';
    $code = isset($row['cp_code']) ? $row['cp_code'] : '';
    $pct  = isset($row['cp_percent']) ? (int)$row['cp_percent'] : 0;
    $name = isset($row['cp_recipient_name']) ? trim($row['cp_recipient_name']) : '';
    $expire = (isset($row['cp_expire']) && $row['cp_expire'] && $row['cp_expire'] !== '0000-00-00') ? $row['cp_expire'] : '';
    $e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $page = ($lang === 'en') ? 'ticket-coupon-en.php' : 'ticket-coupon.php';   // 쿠폰 등록 전용 페이지(KO=본인인증, EN=무료 전용)
    $link = 'https://epiclounge.co.kr/unrealfest2026/'.$page.'?coupon='.rawurlencode($code);

    // 쿠폰 코드 강조 박스
    $codebox = '<div style="margin:8px 0 4px;padding:14px 18px;background:#f4f7f8;border:1px dashed #00C1D5;text-align:center;'
             . 'font-family:\'Inter\',monospace;font-size:20px;font-weight:800;letter-spacing:2px;color:#000001;">'.$e($code).'</div>';

    if ($lang === 'en') {
        $subject   = '[Unreal Fest Seoul 2026] Registration';
        $preheader = 'A discount coupon for Unreal Fest Seoul 2026 registration has been issued.';
        $title     = 'Unreal Fest Seoul 2026 — Registration';
        $disc_line = ($pct >= 100)
            ? 'This coupon covers <strong class="inter-bold700" style="font-weight:700;">100% (free registration)</strong>.'
            : 'A <strong class="inter-bold700" style="font-weight:700;">'.$pct.'% discount</strong> applies at checkout.';
        $body = ($name !== '' ? $e($name).', ' : '').'hello.<br><br>'
              . 'A discount coupon for <strong class="inter-bold700" style="font-weight:700;">Unreal Fest Seoul 2026</strong> registration has been issued to you. '
              . $disc_line . '<br><br>'
              . 'Use the button below — the coupon is applied automatically on the registration page (or enter it manually):'
              . $codebox
              . ($expire !== '' ? '<br><span style="color:#595959;font-size:14px;">Valid until '.$e($expire).'.</span>' : '')
              . '<br><span style="color:#595959;font-size:14px;">※ Card payment · identity verification required.</span>';
        $cta = 'Register now';
        $textbody = "Unreal Fest Seoul 2026 registration coupon: ".$code." (".$pct."% off)\nRegister: ".$link;
    } else {
        $subject   = '[언리얼 페스트 서울 2026] 등록 안내';
        $preheader = '언리얼 페스트 서울 2026 참가 등록에 사용할 수 있는 할인 쿠폰이 발급되었습니다.';
        $title     = '언리얼 페스트 서울 2026 등록 안내';
        $disc_line = ($pct >= 100)
            ? '본 쿠폰은 <strong class="inter-bold700" style="font-weight:700;">100%(무료 등록)</strong> 쿠폰입니다.'
            : '결제 시 <strong class="inter-bold700" style="font-weight:700;">'.$pct.'% 할인</strong>이 적용됩니다.';
        $body = ($name !== '' ? $e($name).'님, ' : '').'안녕하세요.<br><br>'
              . '<strong class="inter-bold700" style="font-weight:700;">언리얼 페스트 서울 2026</strong> 참가 등록에 사용하실 수 있는 할인 쿠폰이 발급되었습니다. '
              . $disc_line . '<br><br>'
              . '아래 버튼으로 등록 페이지에 접속하시면 쿠폰이 자동 적용됩니다. (직접 입력도 가능)'
              . $codebox
              . ($expire !== '' ? '<br><span style="color:#595959;font-size:14px;">사용 기한: '.$e($expire).'까지</span>' : '')
              . '<br><span style="color:#595959;font-size:14px;">※ 카드 결제 · 본인인증이 필요한 정식 등록입니다.</span>';
        $cta = '지금 등록하기';
        $textbody = "언리얼 페스트 서울 2026 등록 할인 쿠폰: ".$code." (".$pct."% 할인)\n등록: ".$link;
    }

    $tpl = <<<'HTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" style="width: 100%;"><head>
<meta name="viewport" content="width=device-width">
<!--[if !mso]><!-->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>{{TITLE}}</title>
<style type="text/css">
body { width: 100% !important; background-color: #FFFFFF; color: #000001; }
body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; padding: 0; margin: 0; }
table td { border-collapse: collapse !important; }
img { border: 0; height: auto; outline: none; text-decoration: none; max-width: 100%; }
#outlook a { padding: 0; }
.container { background-color: #FFFFFF; color: #000001; }
.inter-bold700 { font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-weight: 700; }
a { color: #157EAF; }
@media screen and (max-width: 700px) { .container { width: 100% !important; } .med-full { width: 100% !important; max-width: 100% !important; } .m-title-size { font-size: 22px !important; line-height: 32px !important; } .l-pad-20 { padding-left: 20px !important; padding-right: 20px !important; } .l-pad-24 { padding-left: 24px !important; padding-right: 24px !important; } }
@media (prefers-color-scheme: dark) { body, .container { background-color: #FFFFFF !important; color: #000001 !important; } .gray-65 { color: #595959 !important; } }
</style>
</head>
<body style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #000001; margin: 0; padding: 0; width: 100% !important;" bgcolor="#FFFFFF">
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
<img alt="언리얼 페스트 서울 2026" border="0" src="https://unrealsummit16.cafe24.com/2026/ufs26/ufs26_mail_epicgames/main_key_1920x1080.jpg" style="border-width: 0; display: block; height: auto; max-width: 100%; outline: none; text-decoration: none;" width="700" class="med-full m-full"></a>
</td></tr>
<tr><td align="left" valign="top" style="margin: 0; padding: 24px 20px 62px;" class="l-pad-20">
<table cellpadding="0" cellspacing="0" border="0" width="100%" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="left" dir="ltr" valign="top" style="color: #000001; font-family: 'Inter Tight','Noto Sans KR',Arial,sans-serif; font-size: 24px; font-weight: 900; line-height: 32px; margin: 0; padding: 0 0 16px; word-break: keep-all;" class="m-title-size">{{TITLE}}</td></tr>
<tr><td align="left" dir="ltr" valign="top" style="color: #000001; font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-size: 17px; font-weight: 400; line-height: 1.5; margin: 0; padding: 0 0 32px; word-break: keep-all;">
<p dir="ltr" style="text-align: left; margin: 0; padding: 0; font-family: inherit; font-size: inherit; line-height: inherit;">{{BODY}}</p>
</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0;">
<table align="center" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="center" valign="middle" bgcolor="#00C1D5" style="border-radius: 0; color: #000001; font-family: 'Inter','Noto Sans KR',Arial,sans-serif; font-size: 14px; font-weight: 500; line-height: 20px; margin: 0; padding: 0;" class="o-btn">
<a dir="ltr" href="{{LINK}}" target="_blank" style="color: #000001; display: block; margin: 0; padding: 14px 28px; text-decoration: none; font-weight: 700;">{{CTA_LABEL}}</a>
</td></tr></tbody></table>
</td></tr>
</tbody></table>
</td></tr>
<tr><td height="60" style="font-size: 0; line-height: 0; height: 60px; margin: 0; padding: 0;">&nbsp;</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0 48px 80px;" class="l-pad-24">
<table cellpadding="0" cellspacing="0" border="0" width="100%" role="presentation" style="margin: 0; padding: 0;"><tbody>
<tr><td align="center" valign="top" style="border-top-color: #999999; border-top-style: solid; border-top-width: 1px; margin: 0; padding: 40px 0 24px;">
<a href="https://epiclounge.co.kr/" target="_blank" style="color: #157EAF; margin: 0; padding: 0;">
<img src="https://unrealsummit16.cafe24.com/2026/start_unreal/logo_black.png" alt="Epic Lounge" width="220" border="0" style="border-width: 0; display: block; height: auto; max-width: 100%; outline: none; text-decoration: none;"></a>
</td></tr>
<tr><td align="center" valign="top" style="margin: 0; padding: 0 0 24px;">
<table align="center" cellpadding="0" cellspacing="0" border="0" role="presentation"><tbody><tr>
<td width="28" align="center" valign="middle"><a href="https://www.facebook.com/unrealenginekr" target="_blank"><img alt="Facebook" src="https://unrealsummit16.cafe24.com/2026/start_unreal/fb_btn_1.png" width="20" style="display:block;"></a></td>
<td width="12"></td>
<td width="28" align="center" valign="middle"><a href="https://www.youtube.com/@unrealenginekr" target="_blank"><img alt="YouTube" src="https://unrealsummit16.cafe24.com/2026/start_unreal/ut_btn_1.png" width="20" style="display:block;"></a></td>
<td width="12"></td>
<td width="28" align="center" valign="middle"><a href="https://cafe.naver.com/unrealenginekr" target="_blank"><img alt="Naver Cafe" src="https://unrealsummit16.cafe24.com/2026/start_unreal/cf_btn_1.png" width="20" style="display:block;"></a></td>
</tr></tbody></table>
</td></tr>
<tr><td align="center" valign="top" class="gray-65" style="color: #595959; font-family: 'Inter',Arial,sans-serif; font-size: 12px; font-weight: 400; line-height: 18px; margin: 0; padding: 0 0 8px;">
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">에픽 라운지 (Epic Lounge)&nbsp;|&nbsp;사업자 등록번호 859-88-00263</p>
</td></tr>
<tr><td align="center" valign="top" class="gray-65" style="color: #595959; font-family: 'Inter',Arial,sans-serif; font-size: 12px; font-weight: 400; line-height: 18px; margin: 0; padding: 0 0 24px;">
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">본 메일은 언리얼 페스트 서울 2026 등록 안내를 위해 발송되었습니다.</p>
</td></tr>
<tr><td align="center" valign="top" class="gray-65" style="color: #595959; font-family: 'Inter',Arial,sans-serif; font-size: 12px; font-weight: 400; line-height: 18px; margin: 0; padding: 0;">
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">
<a style="color: #26BBFF; margin: 0; padding: 2px 4px; text-decoration: underline;" href="https://epiclounge.co.kr/v3/contents/v4/ode.php/" target="_blank">서비스 이용약관</a>
&nbsp;|&nbsp;
<a style="color: #26bbff; margin: 0; padding: 2px 4px; text-decoration: underline;" href="https://epiclounge.co.kr/v3/contents/v4/personal.php" target="_blank">개인정보 취급방침</a>
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
}
}
