<?php
// Unreal Fest Seoul 2026 — 타임테이블 (DB 버전). schedule.php 의 DB 연동 포크.
// 데이터 출처: cb_unreal_2026_agenda (관리자 CSV 업로드/아젠다 관리로 채움).
// Day1/Day2 탭 · 트랙뷰/그리드뷰 · Filter. 휴식/점심 등 공통행 렌더 분기 포함.
$ufs_page   = 'schedule';
$page_title = '타임테이블 — Unreal Fest Seoul 2026';
$page_desc  = 'Unreal Fest Seoul 2026 양일간의 세션 타임테이블. 트랙별/시간대별로 일정을 확인하세요.';
include_once __DIR__ . '/../common.php';        // DB (sql_query / sql_real_escape_string)
require_once __DIR__ . '/data/lib.php';
require_once __DIR__ . '/data/agenda_db.php';

// 시간 슬롯을 등장(=ag_sort 정렬) 순서대로, 중복 제거하여 수집
function ufs_s_sched_slots($sessions) {
    $slots = array();
    foreach ($sessions as $s) {
        if (!in_array($s['time'], $slots, true)) $slots[] = $s['time'];
    }
    return $slots;
}
function ufs_sched_colors($track) {
    $m = array(
        '키노트' => array('bg'=>'bg-[rgba(0,193,213,0.1)]','text'=>'text-[#00C1D5]','border'=>'border-[rgba(0,193,213,0.3)]','dot'=>'bg-[#00C1D5]'),
        '게임 - 프로그래밍' => array('bg'=>'bg-[rgba(48,127,226,0.1)]','text'=>'text-[#5a9be6]','border'=>'border-[rgba(48,127,226,0.25)]','dot'=>'bg-[#5a9be6]'),
        '게임 - 아트' => array('bg'=>'bg-[rgba(255,143,28,0.1)]','text'=>'text-[#fecb8b]','border'=>'border-[rgba(255,143,28,0.25)]','dot'=>'bg-[#fecb8b]'),
        '미디어 & 엔터테인먼트' => array('bg'=>'bg-[rgba(250,70,22,0.1)]','text'=>'text-[#ff8674]','border'=>'border-[rgba(250,70,22,0.25)]','dot'=>'bg-[#ff8674]'),
        '산업 & 시뮬레이션' => array('bg'=>'bg-[rgba(221,10,178,0.1)]','text'=>'text-[#dd9cdf]','border'=>'border-[rgba(221,10,178,0.25)]','dot'=>'bg-[#dd9cdf]'),
    );
    return isset($m[$track]) ? $m[$track] : $m['키노트'];
}
function ufs_user_svg($cls) {
    return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="'.$cls.'"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
}
// 연사 아바타 — 헤드샷(ag_sp_photo) 있으면 사진, 없으면 트랙색 원형 + 아이콘
function ufs_avatar($s, $wh, $dot, $svgcls) {
    $photo = isset($s['speaker']['photo']) ? $s['speaker']['photo'] : '';
    if ($photo !== '') {
        return '<img src="'.e($photo).'" alt="'.e($s['speaker']['name']).'" class="'.$wh.' rounded-full object-cover flex-shrink-0" onerror="this.style.display=\'none\'">';
    }
    return '<div class="'.$wh.' rounded-full flex items-center justify-center flex-shrink-0 '.$dot.'">'.ufs_user_svg($svgcls).'</div>';
}

// 트랙뷰 렌더
function ufs_render_track_view($daySessions) {
    foreach (ufs_s_sched_slots($daySessions) as $time) {
        $inSlot = array();
        foreach ($daySessions as $s) { if ($s['time'] === $time) $inSlot[] = $s; }
        if (!$inSlot) continue;
        // 공통(환영사) 단독 슬롯 → 시간/라벨 상하좌우 중앙정렬 행
        $only_common = true;
        foreach ($inSlot as $s) { if (empty($s['_slot_type']) || !ufs_slot_is_common($s['_slot_type'])) { $only_common = false; break; } }
        if ($only_common) {
            echo '<div class="flex border-b border-[#27272a] min-h-[64px]" data-slot-row>';
            echo '<div class="w-[120px] md:w-[160px] flex-shrink-0 flex items-center justify-center"><div class="text-base font-bold text-white tracking-tight text-center">'.e($time).'</div></div>';
            echo '<div class="flex-grow border-l border-[#27272a]">';
            foreach ($inSlot as $s) {
                echo '<div data-sched-common class="h-full flex items-center justify-center px-6 text-center text-sm font-semibold text-[#71717a] bg-[#0b0c10]">'.e(ufs_slot_common_label($s)).'</div>';
            }
            echo '</div></div>';
            continue;
        }
        echo '<div class="flex border-b border-[#27272a]" data-slot-row>';
        echo '<div class="w-[120px] md:w-[160px] flex-shrink-0 py-6"><div class="text-base font-bold text-white tracking-tight sticky top-[140px] text-center pt-2.5">'.e($time).'</div></div>';
        echo '<div class="flex-grow border-l border-[#27272a] divide-y divide-[#27272a]">';
        foreach ($inSlot as $s) {
            // 공통행(휴식/점심/등록확인/환영사/경품추첨) — 클릭 불가 풀폭 라벨
            if (!empty($s['_slot_type']) && ufs_slot_is_common($s['_slot_type'])) {
                echo '<div data-sched-common class="block px-6 py-4 text-center text-sm font-semibold text-[#71717a] bg-[#0b0c10]">'.e(ufs_slot_common_label($s)).'</div>';
                continue;
            }
            // 가림 세션 — "곧 공개 예정"(제목/연사 숨김)
            if (!empty($s['_hidden'])) {
                $c = ufs_sched_colors($s['track']);
                $isKeyH = ($s['track'] === '키노트' || !empty($s['is_keynote']));
                echo '<div data-sched-card data-track="'.e($s['track']).'" data-level="'.e($s['level']).'" data-topics="" class="block p-6 opacity-70">';
                echo '<div class="flex items-center gap-2 mb-2"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($isKeyH ? '키노트' : $s['track']).'">'.e($isKeyH ? '키노트' : ufs_track_label_list($s['track'])).'</span></div>';
                echo '<div class="flex items-center gap-2 text-[#71717a] font-bold"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>곧 공개 예정</div>';
                echo '</div>';
                continue;
            }
            $c = ufs_sched_colors($s['track']);
            $isKey = ($s['track'] === '키노트' || !empty($s['is_keynote']));
            // 키노트는 연사 이미지 보강(index.php 키노트와 동일 에셋) — DB 사진 없을 때
            if ($isKey && (!isset($s['speaker']['photo']) || $s['speaker']['photo'] === '')) {
                $ki = ufs_keynote_img($s['id']);
                if ($ki !== '') $s['speaker']['photo'] = $ki;
            }
            $topics = implode(' ', ufs_session_topics($s));
            echo '<a href="session.php?id='.e($s['id']).'" data-sched-card data-track="'.e($s['track']).'" data-level="'.e($s['level']).'" data-topics="'.e($topics).'" class="block p-6 hover:bg-[#0e0f14] transition-colors'.($isKey ? ' bg-[rgba(0,193,213,0.03)]' : '').'">';
            echo '<div class="flex items-center gap-2 mb-2"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($isKey ? '키노트' : $s['track']).'">'.e($isKey ? '키노트' : ufs_track_label_list($s['track'])).'</span><span class="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">'.e(ufs_level_label_short($s['level'])).'</span></div>';
            echo '<h3 class="font-bold text-[#fafafa] mb-2 tracking-tight leading-snug '.($isKey ? 'text-xl' : 'text-base').'">'.e($s['title']).'</h3>';
            if ($s['desc'] !== '') echo '<p class="text-sm text-[#a1a1aa] mb-3 line-clamp-2">'.e($s['desc']).'</p>';
            $sp_sub = ($isKey && $s['speaker']['role'] !== '') ? ($s['speaker']['role'].($s['speaker']['company'] !== '' ? ' · '.$s['speaker']['company'] : '')) : $s['speaker']['company'];
            echo '<div class="flex items-center gap-2">'.ufs_avatar($s, 'w-12 h-12', $c['dot'], 'w-6 h-6 text-black/60').'<span class="text-sm text-[#a1a1aa]">'.e($s['_speakers_label']).'</span><span class="text-xs text-[#71717a]">'.e($sp_sub).'</span></div>';
            echo '</a>';
        }
        echo '</div></div>';
    }
}

// 트랙별 룸 위치 (범례용)
function ufs_track_room($tr) {
    $m = array(
        '키노트' => 'Main Stage',
        '게임 - 프로그래밍' => 'Harmony Ballroom 1',
        '게임 - 아트' => 'Harmony Ballroom 2',
        '미디어 & 엔터테인먼트' => 'Harmony Ballroom 3',
        '산업 & 시뮬레이션' => 'Atlas',
    );
    return isset($m[$tr]) ? $m[$tr] : $tr;
}

// 그리드뷰(타임테이블) 렌더 — 시간 슬롯 순서대로 키노트/공통/세션 행을 통합 렌더
// 그리드 트랙 표시 라벨 — '산업 & 시뮬레이션'은 Day별로 다름 (Day1=공통, Day2=제조 및 시뮬레이션)
function ufs_grid_track_label($tr, $day) {
    if ($tr === '산업 & 시뮬레이션') return ((int)$day === 1) ? '공통' : '제조 및 시뮬레이션';
    return ufs_track_label_list($tr);
}
function ufs_render_grid_view($daySessions, $day) {
    $gridTracks = array('게임 - 프로그래밍', '게임 - 아트', '미디어 & 엔터테인먼트', '산업 & 시뮬레이션');
    echo '<div class="overflow-x-auto"><table class="w-full min-w-[900px] border-collapse"><thead><tr>';
    echo '<th class="w-[100px] p-3 text-center text-xs font-bold text-[#71717a] uppercase border-b border-[#27272a] sticky left-0 bg-[#09090b] z-10">시간</th>';
    foreach ($gridTracks as $tr) {
        $c = ufs_sched_colors($tr);
        echo '<th class="p-3 text-center text-xs font-bold border-b border-[#27272a]"><span class="'.$c['text'].'">'.e(ufs_grid_track_label($tr, $day)).'</span></th>';
    }
    echo '</tr></thead><tbody>';
    foreach (ufs_s_sched_slots($daySessions) as $time) {
        $inSlot = array();
        foreach ($daySessions as $s) { if ($s['time'] === $time) $inSlot[] = $s; }
        if (!$inSlot) continue;
        // 슬롯 유형별 분류
        $keys = array(); $commons = array(); $normals = array();
        foreach ($inSlot as $s) {
            if ($s['track'] === '키노트' || !empty($s['is_keynote'])) $keys[] = $s;
            elseif (!empty($s['_slot_type']) && ufs_slot_is_common($s['_slot_type'])) $commons[] = $s;
            else $normals[] = $s;
        }
        // 키노트 행(풀폭)
        foreach ($keys as $k) {
            if (!empty($k['_hidden'])) {
                echo '<tr class="border-b border-[#27272a]"><td class="p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.e($k['time']).'</td>';
                echo '<td colspan="4" class="p-2"><div class="block bg-[#0e0f14] p-6 rounded-[6px] text-center text-[#71717a] font-bold">곧 공개 예정</div></td></tr>';
                continue;
            }
            $img = $k['speaker']['photo'] !== '' ? $k['speaker']['photo'] : ufs_keynote_img($k['id']);
            echo '<tr class="border-b border-[#27272a]"><td class="p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.e($k['time']).'</td>';
            echo '<td colspan="4" class="p-2"><a href="session.php?id='.e($k['id']).'" data-grid-cell data-track="키노트" data-level="'.e($k['level']).'" data-topics="'.e(implode(' ', ufs_session_topics($k))).'" class="block bg-[#00C1D5] hover:bg-[#00b0c2] p-6 rounded-[6px] transition-all relative overflow-hidden">';
            echo '<div class="relative z-10 max-w-[70%]"><div class="flex items-center gap-2 mb-2"><span class="px-2 py-0.5 text-[11px] font-bold bg-black/20 text-white">키노트</span><span class="px-2 py-0.5 text-[11px] font-semibold bg-black/20 text-white">'.e(ufs_level_label_short($k['level'])).'</span></div>';
            echo '<h3 class="text-lg font-bold text-black mb-3 tracking-tight leading-snug">'.e($k['title']).'</h3>';
            echo '<div><div class="text-sm font-bold text-black">'.e($k['speaker']['name']).'</div><div class="text-xs text-black/60">'.e($k['speaker']['role']).($k['speaker']['role']!==''&&$k['speaker']['company']!=='' ? ' · ' : '').e($k['speaker']['company']).'</div></div></div>';
            if ($img) echo '<div class="absolute right-4 bottom-0 w-[25%] hidden md:flex items-end justify-center"><img src="'.e($img).'" alt="'.e($k['speaker']['name']).'" class="h-32 object-cover object-top" onerror="this.style.display=\'none\'"></div>';
            echo '</a></td></tr>';
        }
        // 공통 행(풀폭, 클릭 불가)
        foreach ($commons as $cm) {
            echo '<tr class="border-b border-[#27272a] bg-[#0b0c10]"><td class="p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#0b0c10] z-10">'.e($cm['time']).'</td>';
            echo '<td colspan="4" class="p-3 text-center text-sm font-semibold text-[#71717a]">'.e(ufs_slot_common_label($cm)).'</td></tr>';
        }
        // 일반 세션 행(4트랙 셀)
        if ($normals) {
            echo '<tr class="border-b border-[#27272a]"><td class="p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.e($time).'</td>';
            $ntrk = count($gridTracks);
            $gi = 0;
            while ($gi < $ntrk) {
                $tr = $gridTracks[$gi];
                $cells = array();
                foreach ($normals as $s) { if ($s['track'] === $tr) $cells[] = $s; }
                // 통합 세션: cells 중 colspan>1 이면 다음 칸까지 병합(남은 칸 수로 클램프)
                $span = 1;
                foreach ($cells as $cc) { if (!empty($cc['colspan']) && $cc['colspan'] > $span) $span = (int)$cc['colspan']; }
                if ($gi + $span > $ntrk) $span = $ntrk - $gi;
                echo '<td'.($span > 1 ? ' colspan="'.$span.'"' : '').' class="p-2 align-top h-px">';
                if ($cells) {
                    echo '<div class="flex flex-col gap-2 h-full">';
                    foreach ($cells as $cell) {
                        $c = ufs_sched_colors($cell['track']);
                        $minh = (count($cells) > 1) ? '' : ' min-h-[240px]';
                        // 가림 셀 — "곧 공개 예정"
                        if (!empty($cell['_hidden'])) {
                            echo '<div class="block bg-[#0e0f14] p-5 flex-grow'.$minh.' flex flex-col gap-2 opacity-70">';
                            echo '<div class="flex items-center gap-2 flex-wrap"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($cell['track']).'">'.e(ufs_grid_track_label($cell['track'], $day)).'</span></div>';
                            echo '<div class="flex-grow flex items-center justify-center gap-2 text-[#71717a] font-bold text-sm"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>곧 공개 예정</div>';
                            echo '</div>';
                            continue;
                        }
                        $gtopics = implode(' ', ufs_session_topics($cell));
                        echo '<a href="session.php?id='.e($cell['id']).'" data-grid-cell data-track="'.e($cell['track']).'" data-level="'.e($cell['level']).'" data-topics="'.e($gtopics).'" class="block bg-[#0e0f14] p-5 hover:bg-[#111115] transition-colors transition-opacity flex-grow'.$minh.' flex flex-col gap-2">';
                        echo '<div class="flex items-center gap-2 flex-wrap"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($cell['track']).'">'.e(ufs_grid_track_label($cell['track'], $day)).'</span><span class="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">'.e(ufs_level_label_short($cell['level'])).'</span></div>';
                        echo '<h4 class="text-[15px] font-bold text-[#fafafa] leading-snug tracking-tight line-clamp-3 flex-grow">'.e($cell['title']).'</h4>';
                        echo '<div class="flex items-center gap-2.5 mt-auto">'.ufs_avatar($cell, 'w-12 h-12', $c['dot'], 'w-6 h-6 text-black/60').'<div class="min-w-0"><div class="text-sm font-medium text-[#fafafa] truncate">'.e($cell['_speakers_label']).'</div><div class="text-xs text-[#71717a] truncate">'.e($cell['speaker']['company']).'</div></div></div>';
                        echo '</a>';
                    }
                    echo '</div>';
                } else {
                    // 빈 트랙 칸(DB 없음) → "곧 공개 예정"
                    echo '<div class="block bg-[#0e0f14] p-5 min-h-[240px] flex flex-col gap-2 opacity-70 h-full">';
                    echo '<div class="flex items-center gap-2 flex-wrap"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($tr).'">'.e(ufs_grid_track_label($tr, $day)).'</span></div>';
                    echo '<div class="flex-grow flex items-center justify-center gap-2 text-[#71717a] font-bold text-sm"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>곧 공개 예정</div>';
                    echo '</div>';
                }
                echo '</td>';
                $gi += $span;
            }
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
    // 범례
    echo '<div class="flex flex-wrap gap-4 mt-8">';
    foreach (array('키노트','게임 - 프로그래밍','게임 - 아트','미디어 & 엔터테인먼트','산업 & 시뮬레이션') as $tr) {
        $c = ufs_sched_colors($tr);
        echo '<div class="flex items-center gap-1.5 text-xs text-[#a1a1aa]"><span class="w-2.5 h-2.5 rounded-full '.$c['dot'].'"></span>'.e(ufs_track_room($tr)).'</div>';
    }
    echo '</div>';
}

$day1 = ufs_db_day_all(1);
$day2 = ufs_db_day_all(2);
// 환영사(welcome) 외 공통슬롯(등록확인/휴식/점심/경품추첨 등)은 일정표에서 숨김
$ufs_sched_keep = function ($s) {
    return empty($s['_slot_type']) || !ufs_slot_is_common($s['_slot_type']) || $s['_slot_type'] === 'welcome';
};
$day1 = array_values(array_filter($day1, $ufs_sched_keep));
$day2 = array_values(array_filter($day2, $ufs_sched_keep));
include __DIR__ . '/_head.php';
?>

<div class="bg-[#09090b] min-h-screen text-white" data-schedule>
  <!-- 헤딩 -->
  <section class="relative pt-24 pb-12 border-b border-[#27272a]" style="background-color:#0e0f14;">
    <div class="max-w-7xl mx-auto px-6 pt-12">
      <h1 class="text-5xl md:text-6xl mb-4 tracking-tight font-jamjil font-medium">아젠다</h1>
      <p class="text-[#90a1b9] max-w-2xl text-base leading-relaxed">최신 기술과 새로운 아이디어, 다양한 산업 분야의 세션을 만나보세요.</p>
    </div>
  </section>

  <!-- 컨트롤 바 -->
  <div class="sticky top-[73px] z-40 bg-[#111115] border-b border-[#27272a]">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
      <div class="flex items-center gap-4 flex-wrap">
        <div class="flex">
          <button type="button" data-sched-day="day1" class="px-5 py-2.5 text-sm font-bold transition-all bg-[#00C1D5] text-black">Day 1. 8월 20일(목)</button>
          <button type="button" data-sched-day="day2" class="px-5 py-2.5 text-sm font-bold transition-all text-[#71717a] hover:text-white">Day 2. 8월 21일(금)</button>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <!-- 뷰 전환 -->
        <div class="flex items-center gap-1 border border-[#27272a]">
          <button type="button" data-sched-view="track" class="px-3 py-2 transition-colors bg-white text-black" title="트랙뷰">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M3 6h.01"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M8 6h13"/></svg>
          </button>
          <button type="button" data-sched-view="grid" class="px-3 py-2 transition-colors text-[#71717a] hover:text-white" title="그리드뷰">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
          </button>
        </div>
        <!-- 필터 -->
        <div class="relative">
          <button type="button" data-filter-btn class="px-4 py-2 text-sm font-medium flex items-center gap-1.5 border transition-colors border-[#27272a] text-[#a1a1aa] hover:text-white hover:border-white/20">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>
            Filter
            <span class="w-1.5 h-1.5 rounded-full bg-[#00C1D5] hidden" data-filter-dot></span>
          </button>
          <!-- 드롭다운 -->
          <div class="absolute right-0 top-full mt-2 w-[420px] bg-[#111115]/80 backdrop-blur-xl border border-white/10 shadow-2xl z-50 hidden" data-filter-panel>
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-bold text-white">Filter</h2>
                <div class="flex items-center gap-3">
                  <button type="button" data-filter-reset class="text-xs text-[#a1a1aa] underline hover:text-white">Reset</button>
                  <button type="button" data-filter-close class="text-[#a1a1aa] hover:text-white">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                  </button>
                </div>
              </div>
              <div class="mb-6">
                <h3 class="text-sm font-bold text-white mb-3">트랙</h3>
                <div class="grid grid-cols-2 gap-2">
                  <?php
                  $tf = array(array('key'=>'all','label'=>'전체'));
                  foreach (array('키노트','게임 - 프로그래밍','게임 - 아트','미디어 & 엔터테인먼트','산업 & 시뮬레이션') as $tr) { $tf[] = array('key'=>$tr,'label'=>ufs_track_label_list($tr)); }
                  foreach ($tf as $t): ?>
                    <label class="flex items-center gap-2.5 cursor-pointer py-1">
                      <input type="checkbox" data-filter-track="<?= e($t['key']) ?>" class="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]"<?= $t['key']==='all' ? ' checked' : '' ?>>
                      <span class="text-sm text-[#a1a1aa]"><?= e($t['label']) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="mb-6">
                <h3 class="text-sm font-bold text-white mb-3">난이도</h3>
                <div class="grid grid-cols-2 gap-2">
                  <?php foreach (ufs_difficulty_filters() as $l): ?>
                    <label class="flex items-center gap-2.5 cursor-pointer py-1">
                      <input type="checkbox" data-filter-level="<?= e($l['key']) ?>" class="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]"<?= $l['key']==='all' ? ' checked' : '' ?>>
                      <span class="text-sm text-[#a1a1aa]"><?= e($l['label']) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="mb-6">
                <h3 class="text-sm font-bold text-white mb-3">토픽</h3>
                <div class="grid grid-cols-2 gap-2">
                  <?php foreach (ufs_topics() as $tp): ?>
                    <label class="flex items-center gap-2.5 cursor-pointer py-1">
                      <input type="checkbox" data-filter-topic="<?= e($tp) ?>" class="w-4 h-4 rounded text-[#00C1D5] focus:ring-[#00C1D5] bg-transparent border-[#27272a]">
                      <span class="text-sm text-[#a1a1aa]"><?= e($tp) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <button type="button" data-grid-reset class="w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-2.5 font-bold text-sm transition-all">초기화</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 콘텐츠 -->
  <div class="max-w-7xl mx-auto px-6 pb-24">
    <!-- Day1 -->
    <div data-day-content="day1">
      <div data-view-content="track"><?php ufs_render_track_view($day1); ?></div>
      <div data-view-content="grid" class="hidden"><?php ufs_render_grid_view($day1, 1); ?></div>
    </div>
    <!-- Day2 -->
    <div data-day-content="day2" class="hidden">
      <div data-view-content="track"><?php ufs_render_track_view($day2); ?></div>
      <div data-view-content="grid" class="hidden"><?php ufs_render_grid_view($day2, 2); ?></div>
    </div>
  </div>
</div>

<script>
(function(){
  // 그리드뷰 필터: 매치=opacity 1, 비매치=opacity .5 (app.js 트랙뷰 필터와 별개)
  function checked(attr){
    var ck = document.querySelectorAll('[' + attr + ']'), list = [], all = false;
    for (var i = 0; i < ck.length; i++) {
      if (ck[i].checked) { var v = ck[i].getAttribute(attr); if (v === 'all') all = true; else list.push(v); }
    }
    return { list: list, all: all };
  }
  function applyGrid(){
    var tr = checked('data-filter-track'), lv = checked('data-filter-level');
    var tpCk = document.querySelectorAll('[data-filter-topic]'), topics = [];
    for (var i = 0; i < tpCk.length; i++) { if (tpCk[i].checked) topics.push(tpCk[i].getAttribute('data-filter-topic')); }
    var cells = document.querySelectorAll('[data-grid-cell]');
    for (var j = 0; j < cells.length; j++) {
      var c = cells[j];
      var t = c.getAttribute('data-track'), l = c.getAttribute('data-level');
      var tp = (c.getAttribute('data-topics') || '').split(' ');
      var okT = tr.all || tr.list.length === 0 || tr.list.indexOf(t) >= 0;
      var okL = lv.all || lv.list.length === 0 || lv.list.indexOf(l) >= 0;
      var okP = topics.length === 0;
      if (!okP) { for (var k = 0; k < topics.length; k++) { if (tp.indexOf(topics[k]) >= 0) { okP = true; break; } } }
      c.style.opacity = (okT && okL && okP) ? '1' : '0.5';
    }
  }
  // 체크박스 변경 시 즉시 적용
  var fcbs = document.querySelectorAll('[data-filter-track],[data-filter-level],[data-filter-topic]');
  for (var i = 0; i < fcbs.length; i++) fcbs[i].addEventListener('change', applyGrid);
  // 초기화 버튼: app.js 상단 Reset 로직 실행 + 그리드 리셋
  var gr = document.querySelector('[data-grid-reset]');
  if (gr) gr.addEventListener('click', function(){ var tr = document.querySelector('[data-filter-reset]'); if (tr) tr.click(); setTimeout(applyGrid, 0); });
  applyGrid();
})();
</script>
<?php include __DIR__ . '/_foot.php'; ?>
