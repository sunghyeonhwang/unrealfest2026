<?php
/* Unreal Fest Seoul 2026 — PG(토스페이먼츠) 가맹 심사용 데모 메인 (paments/index.php)
 *
 * 루트 index.php를 그대로 렌더한 뒤 출력 버퍼에서 세 가지만 고친다.
 *   ① 상대 자산·링크 경로 → 루트(/v3/unrealfest2026/) 기준 절대 경로
 *   ② 등록하기 링크(양일권·1일권) → /paments/ 토스 결제 데모 페이지
 *   ③ 양일권 '등록 마감' 버튼(사무국 수동 마감) → 활성 버튼 (심사 데모 한정)
 *
 * 본 사이트(루트 index.php)에는 아무 영향이 없다. 심사 종료 후 paments/ 폴더째 삭제.
 * PHP 7.0 호환.
 */
ob_start();
include __DIR__ . '/../index.php';
$html = ob_get_clean();

$root = '/v3/unrealfest2026/';

/* ① 상대 경로 절대화 — http(s)·절대(/)·#앵커·mailto 등은 건드리지 않는다 */
$html = preg_replace('~\b(href|src|action)="(?!https?:|//|/|#|mailto:|tel:|data:|javascript:)(?:\./)?~', '$1="' . $root, $html);

/* ② 등록 링크 → 토스 결제 데모 */
$html = str_replace('href="' . $root . 'ticket-all.php"', 'href="' . $root . 'paments/ticket-all.php"', $html);
$html = str_replace('href="' . $root . 'ticket-day.php"', 'href="' . $root . 'paments/ticket-day.php"', $html);

/* ③ 양일권 수동 마감 버튼 활성화 (index.php $__closed_all=true 가 출력한 span 교체) */
$closedSpan = '<span class="btn-off w-full mt-[16px] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil" style="background:#27272a;color:#71717a;cursor:not-allowed">등록 마감</span>';
$openLink   = '<a href="' . $root . 'paments/ticket-all.php" class="btn-off mt-[16px] w-full bg-[#00C1D5] text-[#09090b] py-[13px] text-[18px] font-bold text-center flex items-center justify-center gap-2 font-jamjil">양일권 등록하기 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[18px] h-[18px]"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>';
$html = str_replace($closedSpan, $openLink, $html);

/* ④ 행사 일자: 8/20(목)–21(금) → 12/3(목)–4(금) (심사용 데모 표기 — 긴 문자열 먼저) */
$dates = array(
    '8.20 (목) ~ 8.21 (금)' => '12.3 (목) ~ 12.4 (금)',
    '8.20 (목)'             => '12.3 (목)',
    '8.21 (금)'             => '12.4 (금)',
    '8월 20일'              => '12월 3일',
    '8월 21일'              => '12월 4일',
    '2026-08-20'            => '2026-12-03',
    '2026-08-21'            => '2026-12-04',
);
$html = str_replace(array_keys($dates), array_values($dates), $html);

/* ⑤ PG 안내: 이니시스 고객센터 → 토스페이먼츠 고객센터 (문구·링크) */
$html = str_replace('이니시스 고객센터', '토스페이먼츠 고객센터', $html);
$html = str_replace('https://www.inicis.com/blog/archives/category/cs', 'https://www.tosspayments.com', $html);

/* ⑥ 히어로 로고 → 심사 데모용 새 로고 */
$html = str_replace('https://unrealsummit16.cafe24.com/2026/ufs26/hero_new_main_logo2.svg',
                    $root . 'paments/hero_new_main_logo3.svg', $html);

echo $html;
