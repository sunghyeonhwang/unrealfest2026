<?php
/* Unreal Fest Seoul 2026 — QR 이미지 저장 버튼 (myticket.php / ticket-complete.php 공용)
 * 호출 전 정의 필요: e(), $qr_rel(상대경로 예 "qrdata/12.jpg")
 * 강제 다운로드는 qr-download.php?no= 가 Content-Disposition: attachment 로 처리.
 */
$qr_no = preg_replace('/[^0-9]/', '', basename($qr_rel));
?>
<div class="flex justify-center mt-5">
  <a href="qr-download.php?no=<?= e($qr_no) ?>" class="inline-flex items-center justify-center gap-2 border border-[#27272a] text-[#a1a1aa] px-6 py-3 text-sm font-bold hover:text-white hover:border-white/30 transition-colors">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
    QR 이미지 저장
  </a>
</div>
