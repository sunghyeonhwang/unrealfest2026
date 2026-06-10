<?php
/* Unreal Fest Seoul 2026 — 오프라인 등록 진입 (ticket.php)
 * 2026-06-10: 티켓 페이지가 ticket-all.php(양일권) / ticket-day.php(일일권)로 분리됨.
 * 기존 ?type= 링크/북마크 하위호환을 위해 301 리다이렉트만 수행.
 *   ?type=all  → ticket-all.php
 *   ?type=day1 → ticket-day.php
 *   ?type=day2 → ticket-day.php?d=2
 * 원본 단일 페이지는 ticket.php.bak-monolith-20260610 로 보존.
 */
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
if ($type === 'day2')      { $to = 'ticket-day.php?d=2'; }
else if ($type === 'day1') { $to = 'ticket-day.php'; }
else                       { $to = 'ticket-all.php'; }
header('Location: ' . $to, true, 301);
exit;
