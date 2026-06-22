<?php
/* Unreal Fest Seoul 2026 — 아젠다/스케줄 DB 접근자 (공개 페이지 전용)
 * cb_unreal_2026_agenda 에서 읽어 기존 data/sessions.php 의 "세션 배열 형태"와
 * 동일한 구조로 매핑한다. 따라서 schedule.php/index.php/session.php 의 렌더 함수를
 * 거의 그대로 재사용할 수 있다.
 *
 * 호출 전제: 페이지 상단에서 common.php(sql_query) 와 data/lib.php(e/라벨맵) 가 이미 로드됨.
 * PHP 7.0 호환. arrow fn / match / str_contains 미사용.
 *
 * 반환 배열 키:
 *   id,title,time,day,date_label,track,level,
 *   speaker{name,role,company},desc,contents[],target,location,is_keynote,
 *   _slot_type (session|keynote|welcome|break|lunch|registration|raffle|etc), _sort
 */

if (!defined('UFS_AGENDA_DB')) {
    define('UFS_AGENDA_DB', 1);
}

// 공통(비-세션) 슬롯 여부 — 휴식/점심/등록확인/환영사/경품추첨 등 트랙을 가로지르는 행
function ufs_slot_is_common($slot_type) {
    $session_like = array('session', 'keynote');
    return !in_array($slot_type, $session_like, true);
}

// 공통 슬롯 표시 라벨(아이콘 텍스트). 제목(ag_title)이 있으면 그것을 우선.
function ufs_slot_common_label($row) {
    if (isset($row['title']) && $row['title'] !== '') return $row['title'];
    $m = array(
        'registration' => '등록 확인',
        'welcome'      => '환영사',
        'break'        => '휴식 시간',
        'lunch'        => '점심 시간',
        'raffle'       => '경품 추첨',
    );
    $t = isset($row['_slot_type']) ? $row['_slot_type'] : '';
    return isset($m[$t]) ? $m[$t] : '';
}

// 연사 배열 파싱: ag_speakers(JSON) 우선, 없으면 ag_sp_*(이름 '/' 구분 시 분리)
function ufs_db_speakers_arr($r) {
    $json = isset($r['ag_speakers']) ? trim((string)$r['ag_speakers']) : '';
    if ($json !== '') {
        $arr = json_decode($json, true);
        if (is_array($arr) && count($arr) > 0) {
            $out = array();
            foreach ($arr as $sp) {
                if (!is_array($sp)) continue;
                $nm = isset($sp['name']) ? trim($sp['name']) : '';
                if ($nm === '') continue;
                $out[] = array(
                    'name'    => $nm,
                    'role'    => isset($sp['role']) ? $sp['role'] : '',
                    'company' => isset($sp['company']) ? $sp['company'] : '',
                    'bio'     => isset($sp['bio']) ? $sp['bio'] : '',
                    'photo'   => isset($sp['photo']) ? $sp['photo'] : '',
                );
            }
            if ($out) return $out;
        }
    }
    $name  = isset($r['ag_sp_name']) ? $r['ag_sp_name'] : '';
    $role  = isset($r['ag_sp_role']) ? $r['ag_sp_role'] : '';
    $comp  = isset($r['ag_sp_company']) ? $r['ag_sp_company'] : '';
    $photo = isset($r['ag_sp_photo']) ? $r['ag_sp_photo'] : '';
    // 이름이 ' / ' 로 여러 명이면 분리(이름만, 사진/회사는 첫 명에)
    $parts = preg_split('#\s*/\s*#u', $name);
    if (is_array($parts) && count($parts) > 1) {
        $out = array();
        foreach ($parts as $i => $nm) {
            $nm = trim($nm);
            if ($nm === '') continue;
            $out[] = array('name'=>$nm, 'role'=>'', 'company'=>$comp, 'bio'=>'', 'photo'=>($i===0 ? $photo : ''));
        }
        if ($out) return $out;
    }
    return array(array('name'=>$name, 'role'=>$role, 'company'=>$comp, 'bio'=>'', 'photo'=>$photo));
}

// 제품군(ag_product) → 배열. 콤마 분리, '기타/Others' 류 제외, 최대 3개.
function ufs_db_product_arr($r) {
    $raw = isset($r['ag_product']) ? trim((string)$r['ag_product']) : '';
    if ($raw === '') return array();
    $out = array();
    foreach (explode(',', $raw) as $p) {
        $p = trim($p);
        if ($p === '' || $p === '기타/Others' || $p === '기타' || $p === 'Others') continue;
        $out[] = $p;
    }
    return array_slice($out, 0, 3);
}

// 주제(ag_topic) → 배열. 콤마 분리.
function ufs_db_topic_arr($r) {
    $raw = isset($r['ag_topic']) ? trim((string)$r['ag_topic']) : '';
    if ($raw === '') return array();
    $out = array();
    foreach (explode(',', $raw) as $p) { $p = trim($p); if ($p !== '') $out[] = $p; }
    return $out;
}

// DB 행 1개 → 세션 배열 형태로 매핑
function ufs_db_map_row($r) {
    $contents = array();
    if (isset($r['ag_contents']) && $r['ag_contents'] !== '' && $r['ag_contents'] !== null) {
        $lines = preg_split('/\r\n|\r|\n/', (string)$r['ag_contents']);
        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln !== '') $contents[] = $ln;
        }
    }
    $slot = isset($r['ag_slot_type']) ? $r['ag_slot_type'] : 'session';
    $day  = (int)$r['ag_day'];
    $speakers = ufs_db_speakers_arr($r);
    $first = $speakers[0];
    $sp_count = count($speakers);
    return array(
        'id'         => $r['ag_sid'],
        'title'      => $r['ag_title'],
        'time'       => $r['ag_time'],
        'day'        => $day,
        'date_label' => ufs_day_date($day),
        'track'      => $r['ag_track'],
        'colspan'    => (isset($r['ag_colspan']) && (int)$r['ag_colspan'] > 1) ? (int)$r['ag_colspan'] : 1,
        'level'      => ($r['ag_level'] !== '' ? $r['ag_level'] : '전체 참가자'),
        'product'    => ufs_db_product_arr($r), // 제품군(SH리뷰) — 콤마분리 배열
        'topic'      => ufs_db_topic_arr($r),   // 주제(필터용) — 콤마분리 배열

        'speaker'    => array(
            'name'    => $first['name'],
            'role'    => $first['role'],
            'company' => $first['company'],
            'photo'   => $first['photo'],
        ),
        'speakers'       => $speakers,
        '_speakers_label'=> ($sp_count > 1 ? ($first['name'] . ' 외 ' . ($sp_count - 1) . '명') : $first['name']),
        'desc'       => (isset($r['ag_desc']) && $r['ag_desc'] !== null) ? $r['ag_desc'] : '',
        'contents'   => $contents,
        'target'     => $r['ag_target'],
        'location'   => $r['ag_location'],
        'is_keynote' => ($slot === 'keynote'),
        '_slot_type' => $slot,
        '_sort'      => (int)$r['ag_sort'],
        '_hidden'    => (isset($r['ag_is_active']) && $r['ag_is_active'] === 'N'), // 가림(곧 공개 예정)
    );
}

// 내부: WHERE 절 + 정렬로 조회 후 매핑 배열 반환
// $include_hidden=true 면 가림(ag_is_active='N') 항목도 포함('_hidden' 플래그로 구분)
function ufs_db_rows($extra_where, $include_hidden = false) {
    $where = $include_hidden ? '1=1' : "ag_is_active='Y'";
    if ($extra_where !== '') $where .= ' AND ' . $extra_where;
    $sql = "SELECT * FROM cb_unreal_2026_agenda WHERE $where ORDER BY ag_day ASC, ag_sort ASC, ag_no ASC";
    $res = sql_query($sql);
    $out = array();
    if ($res) {
        while ($r = $res->fetch_assoc()) { $out[] = ufs_db_map_row($r); }
    }
    return $out;
}

/* ───────── 공개 접근자 (기존 ufs_sessions_* 대응) ───────── */

// Day별 전체 슬롯(키노트 + 세션 + 공통행) — 타임테이블(schedule_s)용
// 가림 항목도 포함하여 "곧 공개 예정"으로 표시(_hidden 플래그)
function ufs_db_day_all($day) {
    return ufs_db_rows('ag_day=' . (int)$day, true);
}

// Day별 세션만(키노트/공통 제외) — index_s 캐러셀용
function ufs_db_day_sessions($day) {
    return ufs_db_rows("ag_day=" . (int)$day . " AND ag_slot_type='session'");
}

// 키노트만 — index_s 키노트 블록용
function ufs_db_keynotes() {
    return ufs_db_rows("ag_slot_type='keynote'");
}

// 단일 세션 — session_s 용
function ufs_db_session($sid) {
    $sid_e = sql_real_escape_string((string)$sid);
    $rows = ufs_db_rows("ag_sid='$sid_e'");
    return count($rows) > 0 ? $rows[0] : null;
}

// 관련 세션(같은 트랙 OR 키노트, 본인 제외, 세션/키노트만) — session_s 용
function ufs_db_related($session, $limit) {
    if ($limit === null) $limit = 2;
    $out = array();
    foreach (ufs_db_rows("ag_slot_type IN ('session','keynote')") as $s) {
        if ($s['id'] === $session['id']) continue;
        if ($s['track'] === $session['track'] || $s['track'] === '키노트' || $s['is_keynote']) {
            $out[] = $s;
            if (count($out) >= $limit) break;
        }
    }
    return $out;
}
