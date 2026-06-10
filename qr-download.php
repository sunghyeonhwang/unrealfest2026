<?php
/* Unreal Fest Seoul 2026 — QR 이미지 강제 다운로드 (qr-download.php)
 * qrdata/{no}.jpg 를 크게 확대(nearest, 선명)하여 Content-Disposition: attachment 로 내려준다.
 * 파일명: UnrealFest2026_QR_{이름}_{전화뒷4자리}.jpg (한글은 RFC5987 filename* 인코딩)
 * PHP 7.0 호환. no 는 숫자만 허용(임의 파일 접근 방지).
 */
include_once "../common.php";

$no = isset($_GET['no']) ? preg_replace('/[^0-9]/', '', $_GET['no']) : '';
if ($no === '') { http_response_code(400); exit('Bad Request'); }
$file = __DIR__ . '/qrdata/' . $no . '.jpg';
if (!is_file($file)) { http_response_code(404); exit('Not Found'); }

// 이름 / 전화 뒷4자리 (파일명용)
$row = function_exists('sql_fetch') ? sql_fetch("select apply_user_name, apply_user_phone from cb_unreal_2026_event2_apply where apply_no='".intval($no)."'") : null;
$name = $row ? preg_replace('/[\\s\\/\\\\:*?"<>|]+/', '', $row['apply_user_name']) : '';
$digits = $row ? preg_replace('/[^0-9]/', '', $row['apply_user_phone']) : '';
$last4 = (strlen($digits) >= 4) ? substr($digits, -4) : $digits;
$fname = 'UnrealFest2026_QR' . ($name !== '' ? '_' . $name : '') . ($last4 !== '' ? '_' . $last4 : '') . '.jpg';
$ascii = 'UnrealFest2026_QR_' . ($last4 !== '' ? $last4 : $no) . '.jpg';

// QR 확대 (nearest-neighbor → 모듈 선명 유지). 목표 폭 약 900px.
$out = null;
if (function_exists('imagecreatefromjpeg')) {
    $src = @imagecreatefromjpeg($file);
    if ($src) {
        $w = imagesx($src); $h = imagesy($src);
        $scale = (int)floor(900 / max(1, $w));
        if ($scale < 1) { $scale = 1; }
        if ($scale > 1) {
            $dst = imagecreatetruecolor($w * $scale, $h * $scale);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $w * $scale, $h * $scale, $w, $h);
            ob_start(); imagejpeg($dst, null, 100); $out = ob_get_clean();
            imagedestroy($dst);
        }
        imagedestroy($src);
    }
}

header('Content-Type: image/jpeg');
header("Content-Disposition: attachment; filename=\"$ascii\"; filename*=UTF-8''" . rawurlencode($fname));
header('Cache-Control: private, no-store');
if ($out !== null) { header('Content-Length: ' . strlen($out)); echo $out; }
else { header('Content-Length: ' . filesize($file)); readfile($file); }
exit;
