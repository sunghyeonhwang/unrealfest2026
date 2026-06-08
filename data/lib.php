<?php
// Unreal Fest Seoul 2026 — 데이터 접근 헬퍼
// Design Ref: design doc §4 Data Access / §10.4 PHP 7.0 호환
// PHP 7.0 호환: 화살표함수/?-> /match/nullable힌트/str_contains 미사용.

if (!defined('UFS_DATA_DIR')) {
    define('UFS_DATA_DIR', __DIR__);
}

// XSS 이스케이프 래퍼
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// 멀티라인 출력
function e_nl($v) {
    return nl2br(htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'));
}

// 트랙 메타 (이름 → slug/색상클래스/장소). Tailwind JIT 위해 클래스는 리터럴.
function ufs_track_meta($track) {
    switch ($track) {
        case '게임 - 프로그래밍':
            return array('slug' => 'prog', 'room' => '그랜드 볼룸 A',
                'text' => 'text-track-prog', 'bg' => 'bg-track-prog', 'border' => 'border-track-prog',
                'badge' => 'bg-track-prog/15 text-track-prog', 'dot' => 'bg-track-prog');
        case '게임 - 아트':
            return array('slug' => 'art', 'room' => '그랜드 볼룸 C',
                'text' => 'text-track-art', 'bg' => 'bg-track-art', 'border' => 'border-track-art',
                'badge' => 'bg-track-art/15 text-track-art', 'dot' => 'bg-track-art');
        case '미디어 & 엔터테인먼트':
            return array('slug' => 'media', 'room' => '그랜드 볼룸 B',
                'text' => 'text-track-media', 'bg' => 'bg-track-media', 'border' => 'border-track-media',
                'badge' => 'bg-track-media/15 text-track-media', 'dot' => 'bg-track-media');
        case '산업 & 시뮬레이션':
            return array('slug' => 'industry', 'room' => '컨퍼런스 룸 1',
                'text' => 'text-track-industry', 'bg' => 'bg-track-industry', 'border' => 'border-track-industry',
                'badge' => 'bg-track-industry/15 text-track-industry', 'dot' => 'bg-track-industry');
        default: // 키노트(전체)
            return array('slug' => 'keynote', 'room' => '그랜드 볼룸 (전체)',
                'text' => 'text-brand', 'bg' => 'bg-brand', 'border' => 'border-brand',
                'badge' => 'bg-brand/15 text-brand', 'dot' => 'bg-brand');
    }
}

// 트랙 목록 (필터/스케줄 컬럼 순서)
function ufs_tracks() {
    return array('게임 - 프로그래밍', '게임 - 아트', '미디어 & 엔터테인먼트', '산업 & 시뮬레이션');
}

// 난이도 목록
function ufs_difficulties() {
    return array('초보자용', '중급자용', '전문가용');
}

// 전체 세션
function ufs_sessions() {
    $sessions = array();
    require UFS_DATA_DIR . '/sessions.php';
    return $sessions;
}

// 키노트만
function ufs_keynotes() {
    $out = array();
    $list = ufs_sessions();
    foreach ($list as $s) {
        if (!empty($s['is_keynote'])) {
            $out[] = $s;
        }
    }
    return $out;
}

// 단건 (없으면 null)
function ufs_session($id) {
    $list = ufs_sessions();
    foreach ($list as $s) {
        if ($s['id'] === $id) {
            return $s;
        }
    }
    return null;
}

// Day별 세션
function ufs_sessions_by_day($day) {
    $out = array();
    $list = ufs_sessions();
    foreach ($list as $s) {
        if ((int)$s['day'] === (int)$day) {
            $out[] = $s;
        }
    }
    return $out;
}

// 관련 세션 (같은 트랙 우선, 자기 자신 제외)
function ufs_related_sessions($session, $limit) {
    if ($limit === null) {
        $limit = 3;
    }
    $out = array();
    $list = ufs_sessions();
    foreach ($list as $s) {
        if ($s['id'] === $session['id']) {
            continue;
        }
        if ($s['track'] === $session['track']) {
            $out[] = $s;
        }
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

// Day → 날짜 라벨
function ufs_day_date($day) {
    return ((int)$day === 1) ? '2026. 8. 20 (목)' : '2026. 8. 21 (금)';
}
function ufs_day_iso($day) {
    return ((int)$day === 1) ? '2026-08-20' : '2026-08-21';
}

// 스폰서 (tier별)
function ufs_sponsors() {
    $sponsors = array();
    require UFS_DATA_DIR . '/sponsors.php';
    return $sponsors;
}

// FAQ
function ufs_faqs() {
    $faqs = array();
    require UFS_DATA_DIR . '/faq.php';
    return $faqs;
}
function ufs_faq_tabs() {
    return array('등록안내', '참석 및 시청', '기타 사항');
}
