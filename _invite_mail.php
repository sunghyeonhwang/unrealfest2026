<?php
/* Unreal Fest Seoul 2026 — 초청장 이메일 템플릿 (_invite_mail.php) [M6]
 * ufs_invite_mail($row, $lang) -> array('subject','html','text'). $row = cb_unreal_2026_speaker_code 행.
 * 이메일 클라이언트 호환: 테이블 레이아웃 + 인라인 스타일 + 라이트 테마. PHP 7.0.
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
        $subject  = '[Unreal Fest Seoul 2026] You are invited';
        $greeting = ($name !== '' ? $e($name).', ' : '').'hello,';
        $lead     = 'You are invited to <strong>Unreal Fest Seoul 2026</strong> at the invitation of <strong>'.$e($inviter).'</strong>.';
        $sub      = ($disc >= 100) ? 'Complete your free registration using the button below.' : 'Register with your invitation discount using the button below.';
        $btn      = 'Register now';
        $note     = 'Aug 20 (Thu) – 21 (Fri), 2026. If the button does not work, open this link:';
        $foot     = 'Unreal Fest Seoul 2026 · Host: Epic Games · Organizer: GRIFF';
        $textbody = 'You are invited to Unreal Fest Seoul 2026 (invited by '.$inviter.").\nRegister: ".$link;
    } else {
        $subject  = '[언리얼 페스트 서울 2026] 초청합니다';
        $greeting = ($name !== '' ? $e($name).'님, ' : '').'안녕하세요.';
        $lead     = '<strong>'.$e($inviter).'</strong>의 초청으로 <strong>언리얼 페스트 서울 2026</strong>에 초대합니다.';
        $sub      = ($disc >= 100) ? '아래 버튼으로 무료 등록을 완료해 주세요.' : '아래 버튼으로 초청 할인 등록을 진행해 주세요.';
        $btn      = '지금 등록하기';
        $note     = '2026년 8월 20일(목)~21일(금). 버튼이 동작하지 않으면 아래 링크를 열어 주세요:';
        $foot     = '언리얼 페스트 서울 2026 · 주최 Epic Games · 주관 (주)그리프';
        $textbody = $inviter."의 초청으로 언리얼 페스트 서울 2026에 초대합니다.\n등록: ".$link;
    }

    $html =
      '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>'
      .'<body style="margin:0;padding:0;background:#f4f5f7;">'
      .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 0;"><tr><td align="center">'
      .'<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;font-family:Arial,Helvetica,\'Apple SD Gothic Neo\',sans-serif;">'
      .'<tr><td style="background:#0b0c10;padding:22px 32px;"><span style="color:#ffffff;font-size:18px;font-weight:bold;letter-spacing:1px;">UNREAL FEST </span><span style="color:#00C1D5;font-size:18px;font-weight:bold;">SEOUL 2026</span></td></tr>'
      .'<tr><td style="padding:32px;">'
      .'<p style="margin:0 0 16px;font-size:15px;color:#111111;">'.$greeting.'</p>'
      .'<p style="margin:0 0 8px;font-size:15px;color:#333333;line-height:1.6;">'.$lead.'</p>'
      .'<p style="margin:0 0 24px;font-size:14px;color:#555555;line-height:1.6;">'.$sub.'</p>'
      .'<table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="border-radius:6px;background:#00C1D5;"><a href="'.$e($link).'" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:bold;color:#0b0c10;text-decoration:none;">'.$btn.' &rarr;</a></td></tr></table>'
      .'<p style="margin:24px 0 6px;font-size:12px;color:#888888;line-height:1.6;">'.$note.'</p>'
      .'<p style="margin:0;font-size:12px;word-break:break-all;"><a href="'.$e($link).'" style="color:#00838f;">'.$e($link).'</a></p>'
      .'</td></tr>'
      .'<tr><td style="padding:20px 32px;background:#fafafa;border-top:1px solid #eeeeee;font-size:11px;color:#999999;">'.$e($foot).'</td></tr>'
      .'</table></td></tr></table></body></html>';

    return array('subject'=>$subject, 'html'=>$html, 'text'=>$textbody);
}
}
