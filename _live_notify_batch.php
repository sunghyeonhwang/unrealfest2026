<?php
/* Unreal Fest Seoul 2026 — 라이브 안내 배치 발송 엔드포인트 (_live_notify_batch.php)
 *
 * Cloudflare Worker(ufs2026-live-notify)가 행사일 매분 호출한다. 실제 발송 여부는
 * 관리자 설정(cb_unreal_2026_config)의 '발송 창'과 활성화 토글을 보고 PHP 가 판단하므로,
 * 스케줄러는 조건 없이 매분 호출해도 안전하다(창 밖이면 아무것도 하지 않음).
 *
 * 발송 슬롯(4개)
 *   d1am  Day1 오전 시청 안내   템플릿 328  (그날 미접속자만 — 진입 분산)
 *   d1pm  Day1 오후 세션 시작   템플릿 331  (온라인 등록자 전체 — 복귀 유도)
 *   d2am  Day2 오전 시청 안내   템플릿 337
 *   d2pm  Day2 오후 세션 시작   템플릿 340
 *
 * 설정 키
 *   live_notify_enabled            0|1
 *   live_notify_{slot}_start/end   'Y-m-d H:i'
 *   live_notify_batch              1회 발송 인원. 빈값/0 이면 자동(남은 대상 ÷ 남은 분)
 *   live_notify_channel            alimtalk|sms
 *   live_notify_at_plusid          발신프로필 @아이디
 *   live_notify_at_tpl_{slot}      템플릿 번호
 *
 * 호출: ?k=KEY                      → dry-run(발송 안 함)
 *       ?k=KEY&go=1                 → 실제 발송(설정 창 안에서만)
 *       ?k=KEY&go=1&force=1&slot=d1pm → 창 무시 강제 1배치(테스트)
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

$now     = date('Y-m-d H:i');
$enabled = (ln_cfg('live_notify_enabled', '0') === '1');
$channel = (ln_cfg('live_notify_channel', 'alimtalk') === 'sms') ? 'sms' : 'alimtalk';
$cfg_bat = (int)ln_cfg('live_notify_batch', '0');       // 0 = 자동
$force   = (($_GET['force'] ?? '') === '1');
$go      = (($_GET['go'] ?? '') === '1');
$SLOTS   = ufs_ln_slots();

// 지금이 어느 슬롯의 발송 창인지 판정
$active = '';
foreach ($SLOTS as $k => $sl) {
    $s = ln_cfg('live_notify_' . $k . '_start');
    $e = ln_cfg('live_notify_' . $k . '_end');
    if ($s !== '' && $e !== '' && $now >= $s && $now <= $e) { $active = $k; break; }
}
if ($force) { $active = isset($SLOTS[$_GET['slot'] ?? '']) ? $_GET['slot'] : 'd1am'; }

echo "== 라이브 안내 배치 ==\n";
echo "  서버시각 : $now\n";
echo "  활성화   : " . ($enabled ? 'ON' : 'OFF') . "\n";
echo "  발송 창  : " . ($active !== '' ? ($active . ($force ? ' (강제)' : '')) : '(창 밖 — 발송 안 함)') . "\n";
echo "  채널     : $channel | 1회 인원: " . ($cfg_bat > 0 ? $cfg_bat . '명(고정)' : '자동(남은 대상 ÷ 남은 분)') . "\n\n";
foreach ($SLOTS as $k => $sl) {
    $s = ln_cfg('live_notify_' . $k . '_start');
    $e = ln_cfg('live_notify_' . $k . '_end');
    $is_mail = ($sl['ch'] === 'email');
    printf("  %-6s %-22s %s ~ %s | %-4s %-4s | 남은 대상 %4d명 | %s\n", $k, $sl['label'],
        ($s !== '' ? substr($s, 5) : '  (미설정)'), ($e !== '' ? substr($e, 11) : '  ---'),
        ($is_mail ? '메일' : '카톡'), ($sl['mode'] === 'bulk' ? '일괄' : '분산'),
        ufs_ln_remaining($k),
        $is_mail ? ('링크 ' . (ufs_ln_cfg('live_notify_nl_url_' . $k, '') !== '' ? 'OK' : '미설정 ← 발송 안 됨'))
                 : ('템플릿 ' . ln_cfg('live_notify_at_tpl_' . $k, $sl['tpl_def'])));
}
echo "\n";

/* 이번 호출에 보낼 인원 */
function ln_batch_size($slot, $cfg_bat) {
    if ($cfg_bat > 0) return $cfg_bat;
    return ufs_ln_auto_batch($slot, ln_cfg('live_notify_' . $slot . '_end'), ln_cfg('live_notify_' . $slot . '_start'));
}

/* 일괄 슬롯은 남은 대상을 한 번에 소진하고, 분산 슬롯만 자동 계산으로 나눠 보낸다 */
function ln_run($slot, $cfg_bat, $dry, $channel) {
    $sl = ufs_ln_slot($slot);
    if ($sl['mode'] === 'bulk') return array(ufs_ln_run_bulk($slot, $dry, $channel), 0);
    $n = ln_batch_size($slot, $cfg_bat);
    return array(ufs_ln_run_batch($slot, max(1, $n), $dry, $channel), $n);
}

if (!$go) {
    $s0 = ($active !== '') ? $active : 'd1am';
    list($r, $n) = ln_run($s0, $cfg_bat, true, $channel);
    $mode = (ufs_ln_slot($s0)['mode'] === 'bulk') ? '일괄(남은 전량)' : ('분산 · 자동 계산 ' . $n . '명');
    echo "[DRY-RUN] $s0 · 이번 회차 {$r['picked']}명 [$mode] — 실제 발송 안 함\n";
    foreach (array_slice($r['rows'], 0, 8) as $x) { echo "    #{$x[0]} {$x[1]} ****{$x[2]}\n"; }
    if ($r['picked'] > 8) echo "    … 외 " . ($r['picked'] - 8) . "명\n";
    echo "\n실제 발송은 &go=1 (설정 창 안에서만). 테스트는 &go=1&force=1&slot=d1pm\n";
    exit;
}

if (!$enabled && !$force) { echo "발송 비활성화 상태(live_notify_enabled=0) — 아무것도 하지 않음\n"; exit; }
if ($active === '')       { echo "발송 창 밖 — 아무것도 하지 않음\n"; exit; }

if (ufs_ln_remaining($active) <= 0) { echo "[$active] 남은 대상 없음 — 발송 완료 상태\n"; exit; }
list($r, $n) = ln_run($active, $cfg_bat, false, $channel);
printf("[발송] %s | 이번 %d명%s → 성공 %d · 실패 %d | 남은 %d명 %s\n",
    $r['day'], $r['picked'], ($n > 0 ? ('(계산 ' . $n . ')') : ('(일괄 ' . $r['calls'] . '회 호출)')),
    $r['sent'], $r['fail'], $r['remaining'],
    ($r['detail'] !== '' ? ('· ' . $r['detail']) : ''));
