<?php
/* Unreal Fest Seoul 2026 — 에셋 버전 헬퍼 (cache-busting)
 * 모든 PHP 페이지에서 CSS/JS/이미지 참조 시 asset_v() 사용.
 * filemtime 기반 → 파일 변경(재업로드) 시 ?v= 자동 갱신 → Cloudflare/브라우저 캐시 무효화.
 * PHP 7.0 호환.
 */
if (!function_exists('asset_v')) {
    function asset_v($rel) {
        $full = __DIR__ . '/' . ltrim($rel, '/');
        $v = @filemtime($full);
        return $v ? ($rel . '?v=' . $v) : $rel;
    }
}
