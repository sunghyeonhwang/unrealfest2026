<?php
/* Unreal Fest Seoul 2026 — 라이브 안내 배치 발송 엔드포인트 (_live_notify_batch.php)
 *
 * 외부 스케줄러(Cloudflare Worker 크론 등)가 1분마다 호출한다. 실제 발송 여부는
 * 관리자 설정(cb_unreal_2026_config)이 정한 '발송 창(window)' 안인지 보고 PHP 가 판단하므로,
 * 스케줄러는 조건 없이 매분 호출해도 안전하다.
 *
 * 설정 키(관리자 2026_live_notify.php 에서 설정)
 *   live_notify_enabled      0|1
 *   live_notify_d1_start/end 'Y-m-d H:i'  (예: 2026-08-20 09:30 ~ 10:20)
 *   live_notify_d2_start/end 'Y-m-d H:i'
 *   live_notify_batch        1회 호출당 발송 인원(기본 60)
 *   live_notify_channel      alimtalk|sms  (기본 alimtalk, 실패 시 sms 자동 대체)
 *
 * 호출: ?k=KEY            → dry-run(대상 수만 보고, 발송 안 함)
 *       ?k=KEY&go=1       → 실제 발송(설정 창 안일 때만)
 *       ?k=KEY&go=1&force=1&day=1 → 창 무시하고 강제 1배치(테스트용)
 */
if (($_GET['k'] ?? '') !== 'ufslivenoti2026x') { http_response_code(403); exit('no'); }
include_once "../common.php";
require_once __DIR__ . '/_live_notify.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

function ln_cfg($k, $def = '') {
    $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='" . sql_real_escape_string($k) . "'");
    return ($r && $r['cfg_val'] !== '') ? $r['cfg_val'] : $def;
}

$now      = date('Y-m-d H:i');
$enabled  = (ln_cfg('live_notify_enabled', '0') === '1');
$batch    = max(1, min(300, (int)ln_cfg('live_notify_batch', '60')));
$channel  = (ln_cfg('live_notify_channel', 'alimtalk') === 'sms') ? 'sms' : 'alimtalk';
$force    = (($_GET['force'] ?? '') === '1');
$go       = (($_GET['go'] ?? '') === '1');

// 지금이 어느 Day 의 발송 창인지 판정
$active_day = '';
foreach (array('1', '2') as $d) {
    $s = ln_cfg('live_notify_d' . $d . '_start');
    $e = ln_cfg('live_notify_d' . $d . '_end');
    if ($s !== '' && $e !== '' && $now >= $s && $now <= $e) { $active_day = $d; break; }
}
if ($force) { $active_day = (($_GET['day'] ?? '1') === '2') ? '2' : '1'; }

echo "== 라이브 안내 배치 ==\n";
echo "  서버시각   : $now\n";
echo "  활성화     : " . ($enabled ? 'ON' : 'OFF') . "\n";
echo "  발송 창    : " . ($active_day !== '' ? ('Day' . $active_day . ($force ? ' (강제)' : '')) : '(창 밖 — 발송 안 함)') . "\n";
echo "  채널/배치  : $channel / {$batch}명\n";
foreach (array('1', '2') as $d) {
    printf("  Day%s 설정  : %s ~ %s | 남은 대상 %d명\n", $d,
        ln_cfg('live_notify_d' . $d . '_start', '(미설정)'), ln_cfg('live_notify_d' . $d . '_end', '(미설정)'),
        ufs_ln_remaining($d));
}
echo "\n";

if (!$go) {
    // dry-run: 다음 배치 대상만 미리 보기
    $d = $active_day !== '' ? $active_day : '1';
    $r = ufs_ln_run_batch($d, $batch, true, $channel);
    echo "[DRY-RUN] Day{$d} 다음 배치 대상 {$r['picked']}명 (실제 발송 안 함)\n";
    foreach (array_slice($r['rows'], 0, 10) as $x) { echo "    #{$x[0]} {$x[1]} ****{$x[2]}\n"; }
    if ($r['picked'] > 10) echo "    … 외 " . ($r['picked'] - 10) . "명\n";
    echo "\n실제 발송은 &go=1 (설정 창 안에서만). 테스트는 &go=1&force=1&day=1\n";
    exit;
}

if (!$enabled && !$force) { echo "발송 비활성화 상태(live_notify_enabled=0) — 아무것도 하지 않음\n"; exit; }
if ($active_day === '')  { echo "발송 창 밖 — 아무것도 하지 않음\n"; exit; }

$r = ufs_ln_run_batch($active_day, $batch, false, $channel);
printf("[발송] Day%s | 대상 %d명 → 성공 %d · 실패 %d | 남은 대상 %d명\n",
    $r['day'], $r['picked'], $r['sent'], $r['fail'], $r['remaining']);
