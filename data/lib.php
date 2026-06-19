<?php
// Unreal Fest Seoul 2026 — 데이터/표시 헬퍼 (canonical = React 라이브 기준)
// PHP 7.0 호환: arrow fn(fn=>), match, ?->, nullable 힌트, str_contains 미사용.
// 트랙/레벨은 "전체 키"로 저장하고, 페이지별 라벨/배지 클래스는 여기 맵으로 변환한다.
// Tailwind v4 CLI가 이 파일(../data/*.php)도 @source 스캔 → 아래 클래스 리터럴이 생성된다.

if (!defined('UFS_DATA_DIR')) {
    define('UFS_DATA_DIR', __DIR__);
}
if (!defined('UFS_ROOT_DIR')) {
    define('UFS_ROOT_DIR', dirname(__DIR__)); // unrealfest2026/ (사이트 루트)
}

/* ───────── 출력 이스케이프 ───────── */
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function e_nl($v) {
    return nl2br(htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'));
}

/* ───────── 에셋 캐시버스트 (filemtime ?v=) ─────────
 * $path 는 사이트 루트 기준 상대경로(예: 'assets/style.css'). CF 캐시 자동 무효화. */
function asset_v($path) {
    $rel = ltrim($path, '/');
    $fs = UFS_ROOT_DIR . '/' . $rel;
    if (is_file($fs)) {
        return $rel . '?v=' . filemtime($fs);
    }
    return $rel;
}

/* ───────── 행사 상수 ───────── */
function ufs_earlybird_deadline() { return '2026-07-13T23:59:59+09:00'; } // 얼리버드 카운트다운 타깃
function ufs_event_dates_label() { return '2026. 8. 20 (목) - 8. 21 (금)'; }

/* ───────── GNB / Footer 네비 ───────── */
function ufs_nav_links() {
    // 홈 앵커 스크롤 대상. 타 페이지에서는 index.php#id 로 폴백.
    return array(
        array('name'=>'소개', 'id'=>'overview'),
        array('name'=>'아젠다', 'id'=>'agenda'),
        array('name'=>'티켓', 'id'=>'register'),
        array('name'=>'행사장 안내', 'id'=>'venue'),
        array('name'=>'이벤트', 'id'=>'event-benefits'),
        array('name'=>'FAQ', 'id'=>'faq'),
        array('name'=>'스폰서', 'id'=>'sponsors'),
    );
}
// {br} 토큰을 PC(lg+)에서만 줄바꿈으로 렌더 (모바일은 줄바꿈 없음). 텍스트는 이스케이프.
function ufs_render_br($text) {
    return str_replace('{br}', '<br class="hidden lg:inline">', htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8'));
}
// FAQ 답변 렌더: 이스케이프 후 등록확인/전화/이메일을 링크화. (\n 줄바꿈은 whitespace-pre-line 으로 유지)
function ufs_faq_html($a) {
    $h = htmlspecialchars((string)$a, ENT_QUOTES, 'UTF-8');
    $lk = 'text-[#00C1D5] hover:underline';
    $h = str_replace('info@epiclounge.co.kr', '<a href="mailto:info@epiclounge.co.kr" class="'.$lk.'">info@epiclounge.co.kr</a>', $h);
    $h = str_replace('02-326-3701', '<a href="tel:02-326-3701" class="'.$lk.'">02-326-3701</a>', $h);
    $h = str_replace('이니시스 고객센터', '<a href="https://www.inicis.com/blog/archives/category/cs" target="_blank" rel="noopener noreferrer" class="'.$lk.'">이니시스 고객센터</a>', $h);
    // '등록 확인' (작은따옴표는 ENT_QUOTES 로 &#039; 변환됨) → 등록 확인 페이지(myticket)
    $h = str_replace('&#039;등록 확인&#039;', '<a href="myticket.php" class="'.$lk.'">&#039;등록 확인&#039;</a>', $h);
    return $h;
}
// 키노트 연사 이미지 (id 매핑; index.php 키노트 카드와 동일 에셋). 없으면 ''.
function ufs_keynote_img($id) {
    $m = array(
        'd1-k1' => 'https://unrealsummit16.cafe24.com/2026/ufs26/picture/epicgames_ceo.png',
        'd1-k2' => 'https://unrealsummit16.cafe24.com/2026/ufs26/picture/epicgames_cto.png',
    );
    return isset($m[$id]) ? $m[$id] : '';
}
function ufs_footer_epic_links() {
    return array(
        '새소식' => 'https://epiclounge.co.kr/contents/v4/news_list.php',
        '이벤트' => 'https://epiclounge.co.kr/contents/v4/event_list.php',
        '리소스' => 'https://epiclounge.co.kr/contents/v4/replay_list.php',
    );
}
function ufs_footer_legal_links() {
    return array('이용약관', '개인정보처리방침', '쿠키 정책');
}

/* ───────── 트랙 메타 (전체키 → 표시/색상) ─────────
 * React Agenda.tsx / Sessions.tsx / Schedule.tsx 의 맵을 컨텍스트별로 보존. */
function ufs_tracks() {
    // 필터/스케줄 컬럼 순서 (키노트 제외)
    return array('게임 - 프로그래밍', '게임 - 아트', '미디어 & 엔터테인먼트', '제조 및 시뮬레이션');
}

// 홈 Agenda 카드 배지 클래스
function ufs_track_badge_home($track) {
    $m = array(
        '키노트' => 'bg-[#00C1D5] text-black',
        '게임 - 프로그래밍' => 'bg-[#307fe2]/50 text-white',
        '게임 - 아트' => 'bg-[#FF8F1C]/50 text-white',
        '미디어 & 엔터테인먼트' => 'bg-[#FA4616]/50 text-white',
        '제조 및 시뮬레이션' => 'bg-[#DD0AB2]/50 text-white',
    );
    return isset($m[$track]) ? $m[$track] : 'bg-[#00C1D5] text-black';
}
// 홈 Agenda 라벨(짧은형)
function ufs_track_label_home($track) {
    $m = array(
        '키노트' => '키노트',
        '게임 - 프로그래밍' => '프로그래밍',
        '게임 - 아트' => '아트',
        '미디어 & 엔터테인먼트' => '미디어&엔터테인먼트',
        '제조 및 시뮬레이션' => '제조&시뮬레이션',
    );
    return isset($m[$track]) ? $m[$track] : $track;
}
// 홈 Agenda 아바타 배경색
function ufs_track_avatar_home($track) {
    $m = array(
        '게임 - 프로그래밍' => 'bg-[#5a9be6]',
        '게임 - 아트' => 'bg-[#fecb8b]',
        '미디어 & 엔터테인먼트' => 'bg-[#ff8674]',
        '제조 및 시뮬레이션' => 'bg-[#dd9cdf]',
    );
    return isset($m[$track]) ? $m[$track] : 'bg-[#00C1D5]';
}

// 세션목록(sessions.php) 배지 클래스 (rgba 보더형)
function ufs_track_badge_list($track) {
    $m = array(
        '게임 - 프로그래밍' => 'bg-[rgba(48,127,226,0.1)] text-[#5a9be6] border border-[rgba(48,127,226,0.25)]',
        '게임 - 아트' => 'bg-[rgba(255,143,28,0.1)] text-[#fecb8b] border border-[rgba(255,143,28,0.25)]',
        '미디어 & 엔터테인먼트' => 'bg-[rgba(250,70,22,0.1)] text-[#ff8674] border border-[rgba(250,70,22,0.25)]',
        '제조 및 시뮬레이션' => 'bg-[rgba(221,10,178,0.1)] text-[#dd9cdf] border border-[rgba(221,10,178,0.25)]',
    );
    return isset($m[$track]) ? $m[$track] : 'bg-[rgba(0,193,213,0.1)] text-[#00C1D5] border border-[rgba(0,193,213,0.25)]';
}
// 세션목록/스케줄 라벨(하이픈형)
function ufs_track_label_list($track) {
    $m = array(
        '키노트' => '키노트',
        '게임 - 프로그래밍' => '게임-프로그래밍',
        '게임 - 아트' => '게임-아트',
        '미디어 & 엔터테인먼트' => '미디어&엔터테인먼트',
        '제조 및 시뮬레이션' => '제조&시뮬레이션',
    );
    return isset($m[$track]) ? $m[$track] : $track;
}
// 요일별 트랙 라벨 — 4번 트랙(제조 및 시뮬레이션)은 Day1 '공통' / Day2 '제조&시뮬레이션'
function ufs_track_label_day($track, $day) {
    if ($track === '제조 및 시뮬레이션') return ((int)$day === 1) ? '공통' : '제조&시뮬레이션';
    return ufs_track_label_list($track);
}

/* ───────── 레벨(난이도) 라벨 (컨텍스트별) ───────── */
function ufs_level_label_short($level) { // 홈/세션목록/스케줄: 초급/중급/고급/전체
    $m = array('전체 참가자'=>'전체', '초보자용'=>'초급', '중급자용'=>'중급', '전문가용'=>'고급');
    return isset($m[$level]) ? $m[$level] : $level;
}
function ufs_level_label_sessions($level) { // 세션목록: 전체 참가자→All Levels
    $m = array('전체 참가자'=>'All Levels', '초보자용'=>'초급', '중급자용'=>'중급', '전문가용'=>'고급');
    return isset($m[$level]) ? $m[$level] : $level;
}
function ufs_level_label_detail($level) { // 세션상세: 전체/초보자/중급/전문가
    $m = array('전체 참가자'=>'전체', '초보자용'=>'초보자', '중급자용'=>'중급', '전문가용'=>'전문가');
    return isset($m[$level]) ? $m[$level] : $level;
}
// 세션상세 키워드 트랙 배지 (키노트 포함)
function ufs_track_badge_detail($track) {
    $m = array(
        '키노트' => 'bg-[#00C1D5] text-white',
        '게임 - 프로그래밍' => 'bg-[rgba(48,127,226,0.1)] text-[#5a9be6] border border-[rgba(48,127,226,0.25)]',
        '게임 - 아트' => 'bg-[rgba(255,143,28,0.1)] text-[#fecb8b] border border-[rgba(255,143,28,0.25)]',
        '미디어 & 엔터테인먼트' => 'bg-[rgba(250,70,22,0.1)] text-[#ff8674] border border-[rgba(250,70,22,0.25)]',
        '제조 및 시뮬레이션' => 'bg-[rgba(221,10,178,0.1)] text-[#dd9cdf] border border-[rgba(221,10,178,0.25)]',
    );
    return isset($m[$track]) ? $m[$track] : 'bg-[#00C1D5] text-white';
}

// 난이도 필터(sessions 사이드바)
function ufs_difficulty_filters() {
    return array(
        array('key'=>'all', 'label'=>'전체'),
        array('key'=>'초보자용', 'label'=>'초보자용'),
        array('key'=>'중급자용', 'label'=>'중급자용'),
        array('key'=>'전문가용', 'label'=>'전문가용'),
    );
}
// 트랙 필터(sessions 사이드바)
function ufs_track_filters() {
    return array(
        array('key'=>'all', 'label'=>'전체 트랙'),
        array('key'=>'게임 - 프로그래밍', 'label'=>'게임-프로그래밍'),
        array('key'=>'게임 - 아트', 'label'=>'게임-아트'),
        array('key'=>'미디어 & 엔터테인먼트', 'label'=>'미디어&엔터테인먼트'),
        array('key'=>'제조 및 시뮬레이션', 'label'=>'제조&시뮬레이션'),
    );
}
// 기술분야 카테고리(sessions: '전체' 포함 12 / schedule: 11)
function ufs_all_categories() {
    return array('전체', 'AI', 'Unreal Engine', 'Game', 'Art', 'Entertainment',
        'Security', 'Infra', 'XR / AR', 'Digital Twin', 'Automotive', 'MetaHuman');
}
function ufs_topics() {
    return array('AI', 'Unreal Engine', 'Game', 'Art', 'Entertainment',
        'Security', 'Infra', 'XR / AR', 'Digital Twin', 'Automotive', 'MetaHuman');
}

/* ───────── 세션 기술분야(태그) 파생 — React getSessionCategories 포팅 ─────────
 * title+desc+contents 텍스트에서 키워드 매칭. 최대 3개. 순서/규칙 React와 동일. */
function ufs_session_categories($s) {
    $text = $s['title'] . ' ' . $s['desc'] . ' ' . implode(' ', $s['contents']);
    $t = mb_strtolower($text, 'UTF-8'); // 한글은 영향 없음, 영문 소문자화
    $tags = array();
    $hit = function($needles) use ($t) {
        foreach ($needles as $n) { if (strpos($t, $n) !== false) return true; }
        return false;
    };
    if ($hit(array('언리얼', 'unreal'))) $tags[] = 'Unreal Engine';
    if ($hit(array('ai', '인공지능', '머신러닝'))) $tags[] = 'AI';
    if ($hit(array('게임', 'game'))) $tags[] = 'Game';
    if ($hit(array('아트', '라이팅', '캐릭터', '머티리얼', '이펙트'))) $tags[] = 'Art';
    if ($hit(array('애니메이션', '시네마틱', '영상', '콘서트', '모션'))) $tags[] = 'Entertainment';
    if ($hit(array('xr', 'vr', 'ar '))) $tags[] = 'XR / AR';
    if ($hit(array('디지털 트윈', 'digital twin'))) $tags[] = 'Digital Twin';
    if ($hit(array('자율주행', 'automotive', '자동차'))) $tags[] = 'Automotive';
    if ($hit(array('메타휴먼', 'metahuman'))) $tags[] = 'MetaHuman';
    if ($hit(array('보안', 'security'))) $tags[] = 'Security';
    if ($hit(array('인프라', 'infra', '서버', '네트워크'))) $tags[] = 'Infra';
    if (count($tags) === 0) $tags[] = 'Unreal Engine';
    return array_slice($tags, 0, 3);
}

// 세션 토픽 전체(스케줄 필터용 — slice 없음, Sessions 규칙과 동일).
function ufs_session_topics($s) {
    $t = mb_strtolower($s['title'].' '.$s['desc'].' '.implode(' ', $s['contents']), 'UTF-8');
    $tags = array();
    $hit = function($needles) use ($t) {
        foreach ($needles as $n) { if (strpos($t, $n) !== false) return true; }
        return false;
    };
    if ($hit(array('언리얼', 'unreal'))) $tags[] = 'Unreal Engine';
    if ($hit(array('ai', '인공지능', '머신러닝'))) $tags[] = 'AI';
    if ($hit(array('게임', 'game'))) $tags[] = 'Game';
    if ($hit(array('아트', '라이팅', '캐릭터', '머티리얼', '이펙트'))) $tags[] = 'Art';
    if ($hit(array('애니메이션', '시네마틱', '영상', '콘서트', '모션'))) $tags[] = 'Entertainment';
    if ($hit(array('xr', 'vr', 'ar '))) $tags[] = 'XR / AR';
    if ($hit(array('디지털 트윈', 'digital twin'))) $tags[] = 'Digital Twin';
    if ($hit(array('자율주행', 'automotive', '자동차'))) $tags[] = 'Automotive';
    if ($hit(array('메타휴먼', 'metahuman'))) $tags[] = 'MetaHuman';
    if ($hit(array('보안', 'security'))) $tags[] = 'Security';
    if ($hit(array('인프라', 'infra', '서버', '네트워크'))) $tags[] = 'Infra';
    if (count($tags) === 0) $tags[] = 'Unreal Engine';
    return $tags;
}

/* ───────── 세션 접근자 ───────── */
function ufs_sessions() {
    $sessions = array();
    require UFS_DATA_DIR . '/sessions.php';
    return $sessions;
}
function ufs_keynotes() {
    $out = array();
    foreach (ufs_sessions() as $s) { if (!empty($s['is_keynote'])) $out[] = $s; }
    return $out;
}
function ufs_session($id) {
    foreach (ufs_sessions() as $s) { if ($s['id'] === $id) return $s; }
    return null;
}
function ufs_sessions_by_day($day) {
    $out = array();
    foreach (ufs_sessions() as $s) {
        if (!empty($s['is_keynote'])) continue;
        if ((int)$s['day'] === (int)$day) $out[] = $s;
    }
    return $out;
}
// Day별 전체(키노트 포함) — 스케줄 타임테이블용
function ufs_sessions_by_day_all($day) {
    $out = array();
    foreach (ufs_sessions() as $s) {
        if ((int)$s['day'] === (int)$day) $out[] = $s;
    }
    return $out;
}
// 세션목록용: 키노트 제외 + time 오름차순(React filteredSessions 정렬과 동일, stable)
function ufs_sessions_for_list() {
    $out = array();
    foreach (ufs_sessions() as $s) { if (empty($s['is_keynote'])) $out[] = $s; }
    // 안정 정렬: time 문자열 비교
    $idx = array();
    foreach ($out as $i => $s) { $idx[$i] = $s; }
    usort($out, 'ufs_cmp_time_stable');
    return $out;
}
function ufs_cmp_time_stable($a, $b) {
    $c = strcmp($a['time'], $b['time']);
    return $c; // usort는 불안정하지만 동일 time 내 순서차는 표시상 무해(원본 배열도 시간순 그룹)
}
// 관련 세션(React SessionDetail: 같은 트랙 OR 키노트, 배열순, 2개).
function ufs_related_sessions($session, $limit) {
    if ($limit === null) $limit = 2;
    $out = array();
    foreach (ufs_sessions() as $s) {
        if ($s['id'] === $session['id']) continue;
        if ($s['track'] === $session['track'] || $s['track'] === '키노트') {
            $out[] = $s;
            if (count($out) >= $limit) break;
        }
    }
    return $out;
}
// 세션상세 키워드 태그(목록과 다른 규칙: Entertainment/Security/Infra 없음, Game 후순위).
function ufs_session_keywords($s) {
    $t = mb_strtolower($s['title'].' '.$s['desc'].' '.implode(' ', $s['contents']), 'UTF-8');
    $tags = array();
    $hit = function($needles) use ($t) {
        foreach ($needles as $n) { if (strpos($t, $n) !== false) return true; }
        return false;
    };
    if ($hit(array('언리얼','unreal'))) $tags[] = 'Unreal Engine';
    if ($hit(array('ai','인공지능'))) $tags[] = 'AI';
    if ($hit(array('아트','라이팅','캐릭터','머티리얼','이펙트'))) $tags[] = 'Art';
    if ($hit(array('xr','vr','ar '))) $tags[] = 'XR / AR';
    if ($hit(array('디지털 트윈'))) $tags[] = 'Digital Twin';
    if ($hit(array('자율주행','자동차'))) $tags[] = 'Automotive';
    if ($hit(array('메타휴먼'))) $tags[] = 'MetaHuman';
    if ($hit(array('게임','game'))) $tags[] = 'Game';
    if (count($tags) === 0) $tags[] = 'Unreal Engine';
    return array_slice($tags, 0, 3);
}

/* ───────── 날짜 라벨 ───────── */
function ufs_day_date($day) { return ((int)$day === 1) ? '2026. 8. 20 (목)' : '2026. 8. 21 (금)'; }
function ufs_day_iso($day) { return ((int)$day === 1) ? '2026-08-20' : '2026-08-21'; }
function ufs_day_short($day) { return ((int)$day === 1) ? '8월 20일' : '8월 21일'; }

/* ───────── FAQ / 스폰서 / 콘텐츠 접근자 ───────── */
function ufs_faqs() {
    $faqs = array();
    require UFS_DATA_DIR . '/faq.php';
    return $faqs;
}
function ufs_sponsors_home() {
    $sponsors_home = array();
    require UFS_DATA_DIR . '/sponsors.php';
    return $sponsors_home;
}
function ufs_sponsors_detail() {
    $sponsors_detail = array();
    require UFS_DATA_DIR . '/sponsors.php';
    return $sponsors_detail;
}
function ufs_overview() {
    $overview = array();
    require UFS_DATA_DIR . '/content.php';
    return $overview;
}
function ufs_venue() {
    $venue = array();
    require UFS_DATA_DIR . '/content.php';
    return $venue;
}
function ufs_events() {
    $events = array();
    require UFS_DATA_DIR . '/content.php';
    return $events;
}
