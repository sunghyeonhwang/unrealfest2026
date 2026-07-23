<?php
/* Unreal Fest Seoul 2026 — 초청장 이메일 템플릿 (_invite_mail.php) [M6]
 * ufs_invite_mail($row, $lang) -> array('subject','html','text'). $row = cb_unreal_2026_speaker_code 행.
 * 베이스: UE/에픽라운지 공식 메일(헤더·히어로·푸터 유지) + 초청 문구/개인화/등록 CTA/KO·EN.
 * 이메일 클라이언트 호환(테이블+인라인, MSO 조건부, 다크모드 대응). PHP 7.0.
 */
if (!function_exists('ufs_invite_mail')) {
function ufs_invite_mail($row, $lang) {
    $lang = ($lang === 'en') ? 'en' : 'ko';
    $code = isset($row['sc_code']) ? $row['sc_code'] : '';
    $name = isset($row['sc_name']) ? trim($row['sc_name']) : '';
    $inviter = (isset($row['sc_inviter']) && trim($row['sc_inviter']) !== '') ? trim($row['sc_inviter']) : '에픽게임즈';
    $disc = isset($row['sc_discount']) ? (int)$row['sc_discount'] : 100;
    $e = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $link = 'https://epiclounge.co.kr/unrealfest2026/ticket-invite.php?code='.rawurlencode($code).'&lang='.$lang;

    if ($lang === 'en') {
        $subject   = '[Unreal Fest Seoul 2026] You are invited';
        $preheader = 'You are invited to Unreal Fest Seoul 2026 at the invitation of '.$inviter.'.';
        $title     = 'You are invited — Unreal Fest Seoul 2026';
        $disc_line = ($disc >= 100)
            ? 'This is a <strong class="inter-bold700" style="font-weight:700;">complimentary invitation</strong>. Please complete your free registration using the button below.'
            : 'An <strong class="inter-bold700" style="font-weight:700;">invitation discount ('.$disc.'%)</strong> applies. Please register using the button below.';
        $body = ($name !== '' ? $e($name).', ' : '').'hello.<br><br>'
              . 'You are invited to <strong class="inter-bold700" style="font-weight:700;">Unreal Fest Seoul 2026</strong> at the invitation of <strong class="inter-bold700" style="font-weight:700;">'.$e($inviter).'</strong>. '
              . 'Meet the latest Unreal Engine and Epic ecosystem technologies and real-world insights from experts across games, film &amp; TV, animation, automotive, simulation and more. '
              . $disc_line;
        $cta = 'Register now';
        $textbody = 'You are invited to Unreal Fest Seoul 2026 (invited by '.$inviter.").\nRegister: ".$link;
    } else {
        $subject   = '[언리얼 페스트 서울 2026] 초청합니다';
        $preheader = $inviter.'의 초청으로 언리얼 페스트 서울 2026에 초대합니다.';
        $title     = '언리얼 페스트 서울 2026 초청';
        $disc_line = ($disc >= 100)
            ? '본 초청은 <strong class="inter-bold700" style="font-weight:700;">무료 등록</strong>입니다. 아래 버튼으로 등록을 완료해 주세요.'
            : '<strong class="inter-bold700" style="font-weight:700;">초청 할인('.$disc.'%)</strong>이 적용됩니다. 아래 버튼으로 등록을 진행해 주세요.';
        $body = ($name !== '' ? $e($name).'님, ' : '').'안녕하세요.<br><br>'
              . '<strong class="inter-bold700" style="font-weight:700;">'.$e($inviter).'</strong>의 초청으로 <strong class="inter-bold700" style="font-weight:700;">언리얼 페스트 서울 2026</strong>에 초대합니다. '
              . '언리얼 엔진과 에픽 에코시스템의 최신 기술, 그리고 게임·영화 및 TV·애니메이션·자동차·시뮬레이션 등 다양한 산업 분야 전문가들의 실제 프로젝트 경험과 노하우를 현장에서 만나보세요. '
              . $disc_line;
        $cta = '지금 등록하기';
        $textbody = $inviter."의 초청으로 언리얼 페스트 서울 2026에 초대합니다.\n등록: ".$link;
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
<p dir="ltr" style="text-align: center; margin: 0; padding: 0; font: inherit;">본 메일은 언리얼 페스트 서울 2026 초청 안내를 위해 발송되었습니다.</p>
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
