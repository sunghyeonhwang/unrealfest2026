<?php
/* 공유 아젠다 그리드 렌더 — schedule.php 그리드뷰 함수 이식(랜딩/프리뷰 공용).
   schedule.php 원본은 무수정. function_exists 가드로 중복정의 방지. */
require_once __DIR__ . '/no_online.php';   // 온라인 중계 제외 배지/리본 = 단일 소스(schedule_preview 와 공용)
if (!function_exists('ufs_render_grid_view')) {
function ufs_track_room($tr) {
    $m = array('키노트'=>'Main Stage','게임: 아트'=>'Harmony Ballroom 1','게임: 프로그래밍'=>'Harmony Ballroom 2','미디어 & 엔터테인먼트'=>'Harmony Ballroom 3','제조 및 시뮬레이션'=>'Atlas');
    return isset($m[$tr]) ? $m[$tr] : $tr;
}
function ufs_s_sched_slots($sessions) {
    $slots = array();
    foreach ($sessions as $s) {
        if (!in_array($s['time'], $slots, true)) $slots[] = $s['time'];
    }
    return $slots;
}
// 시간 표기 — PC는 1줄(11:30~12:20), 모바일은 3줄(span을 CSS로 block 전환). HTML 반환.
function ufs_time_3line($t) {
    $p = preg_split('/\s*~\s*/u', trim((string)$t), 2);
    if (count($p) === 2 && $p[1] !== '') return '<span class="ufs-tt">'.e($p[0]).'</span><span class="ufs-tt">~</span><span class="ufs-tt">'.e($p[1]).'</span>';
    return e($t);
}
function ufs_sched_colors($track) {
    $m = array(
        '키노트' => array('bg'=>'bg-[rgba(0,193,213,0.1)]','text'=>'text-[#00C1D5]','border'=>'border-[rgba(0,193,213,0.3)]','dot'=>'bg-[#00C1D5]'),
        '게임: 프로그래밍' => array('bg'=>'bg-[rgba(48,127,226,0.1)]','text'=>'text-[#5a9be6]','border'=>'border-[rgba(48,127,226,0.25)]','dot'=>'bg-[#5a9be6]'),
        '게임: 아트' => array('bg'=>'bg-[rgba(255,143,28,0.1)]','text'=>'text-[#fecb8b]','border'=>'border-[rgba(255,143,28,0.25)]','dot'=>'bg-[#fecb8b]'),
        '미디어 & 엔터테인먼트' => array('bg'=>'bg-[rgba(250,70,22,0.1)]','text'=>'text-[#ff8674]','border'=>'border-[rgba(250,70,22,0.25)]','dot'=>'bg-[#ff8674]'),
        '제조 및 시뮬레이션' => array('bg'=>'bg-[rgba(221,10,178,0.1)]','text'=>'text-[#dd9cdf]','border'=>'border-[rgba(221,10,178,0.25)]','dot'=>'bg-[#dd9cdf]'),
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
function ufs_grid_track_label($tr, $day) {
    // 그리드뷰는 띄어쓰기 포함 정식 명칭으로 표기
    if ($tr === '제조 및 시뮬레이션') return ((int)$day === 1) ? '공통' : '제조 및 시뮬레이션';
    if ($tr === '미디어 & 엔터테인먼트') return '미디어 & 엔터테인먼트';
    if ($tr === '게임: 아트' || $tr === '게임: 프로그래밍') return $tr; // 콜론 뒤 띄어쓰기 유지
    return ufs_track_label_day($tr, $day);
}
function ufs_render_grid_view($daySessions, $day) {
    $gridTracks = array('게임: 아트', '게임: 프로그래밍', '미디어 & 엔터테인먼트', '제조 및 시뮬레이션');
    echo '<div class="overflow-x-auto"><table class="w-full min-w-[900px] border-collapse"><thead><tr>';
    echo '<th class="ufs-gtime w-[100px] p-3 text-center text-xs font-bold text-[#71717a] uppercase border-b border-[#27272a] sticky left-0 bg-[#09090b] z-10">시간</th>';
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
                echo '<tr class="border-b border-[#27272a]"><td class="ufs-gtime p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.ufs_time_3line($k['time']).'</td>';
                echo '<td colspan="4" class="p-2"><div class="block bg-[#0e0f14] p-6 rounded-[6px] text-center text-[#71717a] font-bold">곧 공개 예정</div></td></tr>';
                continue;
            }
            $img = $k['speaker']['photo'] !== '' ? $k['speaker']['photo'] : ufs_keynote_img($k['id']);
            echo '<tr class="border-b border-[#27272a]"><td class="ufs-gtime p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.ufs_time_3line($k['time']).'</td>';
            echo '<td colspan="4" class="p-2"><div data-grid-cell data-track="키노트" data-level="'.e($k['level']).'" data-topics="'.e(implode(' ', ufs_session_topics($k))).'" class="block bg-[#00C1D5] p-6 rounded-[6px] relative overflow-hidden">';
            echo '<div class="relative z-10 max-w-[70%]"><div class="flex items-center gap-2 mb-2"><span class="px-2 py-0.5 text-[11px] font-bold bg-black/20 text-white">키노트</span><span class="px-2 py-0.5 text-[11px] font-semibold bg-black/20 text-white">'.e(ufs_level_label_short($k['level'])).'</span></div>';
            echo '<h3 class="text-lg font-bold text-black mb-3 tracking-tight leading-snug">'.e($k['title']).'</h3>';
            echo '<div><div class="text-sm font-bold text-black">'.e($k['speaker']['name']).'</div><div class="text-xs text-black/60">'.e($k['speaker']['role']).($k['speaker']['role']!==''&&$k['speaker']['company']!=='' ? ' · ' : '').e($k['speaker']['company']).'</div></div></div>';
            if ($img) echo '<div class="absolute right-4 bottom-0 w-[25%] hidden md:flex items-end justify-center"><img src="'.e($img).'" alt="'.e($k['speaker']['name']).'" class="h-32 object-cover object-top" onerror="this.style.display=\'none\'"></div>';
            echo '</div></td></tr>';
        }
        // 공통 행(풀폭, 클릭 불가)
        foreach ($commons as $cm) {
            // 환영사: 키노트처럼 좌측정렬 + 연사(소속·이름·직함) 표시
            if (!empty($cm['_slot_type']) && $cm['_slot_type'] === 'welcome') {
                $wsp = $cm['speaker'];
                $wline = trim($wsp['company'].' '.$wsp['name'].' '.$wsp['role']);
                echo '<tr class="border-b border-[#27272a] bg-[#0b0c10]"><td class="ufs-gtime p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#0b0c10] z-10">'.ufs_time_3line($cm['time']).'</td>';
                echo '<td colspan="4" class="p-3 text-center text-base font-semibold text-white">'.e(ufs_slot_common_label($cm));
                if ($wline !== '') echo '<div class="text-xs font-normal text-[#71717a] mt-1">'.e($wline).'</div>';
                echo '</td></tr>';
                continue;
            }
            echo '<tr class="border-b border-[#27272a] bg-[#0b0c10]"><td class="ufs-gtime p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#0b0c10] z-10">'.ufs_time_3line($cm['time']).'</td>';
            echo '<td colspan="4" class="p-3 text-center text-sm font-semibold '.($cm['_slot_type']==='raffle'?'text-white':'text-[#71717a]').'">'.e(ufs_slot_common_label($cm)).'</td></tr>';
        }
        // 일반 세션 행(4트랙 셀)
        if ($normals) {
            echo '<tr class="border-b border-[#27272a]"><td class="ufs-gtime p-3 text-sm font-bold text-white align-middle text-center sticky left-0 bg-[#09090b] z-10">'.ufs_time_3line($time).'</td>';
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
                        $gtopics = implode('|', isset($cell['topic']) ? $cell['topic'] : array());
                        $gprods  = implode('|', isset($cell['product']) ? $cell['product'] : array());
                        echo '<a href="session.php?id='.e($cell['id']).'" data-grid-cell data-track="'.e($cell['track']).'" data-level="'.e($cell['level']).'" data-topics="'.e($gtopics).'" data-products="'.e($gprods).'" class="relative block bg-[#0e0f14] p-5 hover:bg-[#111115] transition-colors transition-opacity flex-grow'.$minh.' flex flex-col gap-2">'.ufs_no_online_ribbon($cell['id']);
                        $gxtb = '';
                        foreach (ufs_merged_extra_tracks($cell['track'], isset($cell['colspan']) ? $cell['colspan'] : 1) as $gxt) { $gxtb .= '<span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($gxt).'">'.e(ufs_grid_track_label($gxt, $day)).'</span>'; }
                        echo '<div class="flex items-center gap-2 flex-wrap"><span class="px-1.5 py-1 text-[10px] rounded-[4px] '.ufs_track_badge_home($cell['track']).'">'.e(ufs_grid_track_label($cell['track'], $day)).'</span>'.$gxtb.'<span class="px-2 py-0.5 text-[11px] font-semibold bg-[#27272a] text-[#f4f4f5]">'.e(ufs_level_label_short($cell['level'])).'</span>'.ufs_no_online_badge($cell['id']).'</div>';
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
    /* 범례 — 요청으로 주석처리(비노출)
    echo '<div class="flex flex-wrap gap-4 mt-8">';
    foreach (array('키노트','게임: 아트','게임: 프로그래밍','미디어 & 엔터테인먼트','제조 및 시뮬레이션') as $tr) {
        $c = ufs_sched_colors($tr);
        echo '<div class="flex items-center gap-1.5 text-xs text-[#a1a1aa]"><span class="w-2.5 h-2.5 rounded-full '.$c['dot'].'"></span>'.e(ufs_track_room($tr)).'</div>';
    }
    echo '</div>';
    */
}
}
