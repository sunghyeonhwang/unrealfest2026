<?php
/* Unreal Fest Seoul 2026 — 세션 다시보기 (replay/index.php)
 *
 * 등록자 전용 다시보기. 기획: replay/REPLAY_PLANNING.md
 *  - 체크인 = 등록 이메일 + 연락처(뒤 8자리). 온라인·오프라인 등록자 모두 이용.
 *  - 온라인 등록자(ONLINE 상품) = QA 제외 영상(rv_vimeo), 오프라인 등록자 = QA 포함 영상(rv_vimeo_qa).
 *    오프라인용 QA 영상이 아직 없으면 "Q&A 포함 영상 준비 중" 안내.
 *  - 영상 = Vimeo(도메인 제한 임베드), 자료 = Cloudflare PDF(pdf.php 경유).
 *  - 세션 정보는 cb_unreal_2026_agenda 재사용, 다시보기 전용 값만 cb_unreal_2026_replay_vod.
 *  - 관리자(그누보드 is_admin 급)는 게이트 우회(운영 점검용) — 일반 세션에 영향 없음.
 * PHP 7.0 호환. noindex + private no-store(개인화 페이지 캐시 금지).
 */
include_once "../../common.php";
require_once __DIR__ . '/_common_replay.php';

header('Cache-Control: private, no-store');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow');

$is_adm = rv_is_adm();

/* ── 로그아웃 ── */
if (isset($_GET['logout'])) {
    unset($_SESSION['ufs_replay_ok'], $_SESSION['ufs_replay_name'], $_SESSION['ufs_replay_no'], $_SESSION['ufs_replay_email'], $_SESSION['ufs_replay_at'], $_SESSION['ufs_replay_type']);
    header('Location: ./'); exit;
}

/* ── 내부 확인용 프리뷰 — ?pv=<키> 로 체크인 없이 열람 (기본 오프라인 화면, ?as=online 로 온라인 화면) ── */
$PV_KEY = '95d22b516b1965719d9b';
$is_pv  = (isset($_GET['pv']) && hash_equals($PV_KEY, (string)$_GET['pv']));

$verified = (rv_auth_ok() || $is_adm || $is_pv);
$viewer   = !empty($_SESSION['ufs_replay_name']) ? $_SESSION['ufs_replay_name'] : ($is_adm ? '관리자' : ($is_pv ? '내부 확인' : ''));
$apply_no = !empty($_SESSION['ufs_replay_no']) ? (int)$_SESSION['ufs_replay_no'] : 0;

/* ── 등록 유형 — online(QA 제외 영상) / offline(QA 포함 영상). 기존 세션(유형 미저장)은 오프라인 취급 ── */
$user_type = !empty($_SESSION['ufs_replay_type']) ? $_SESSION['ufs_replay_type'] : 'offline';
if ($is_pv && empty($_SESSION['ufs_replay_ok'])) {
    // 프리뷰: 기본은 오프라인 화면(QA 포함), ?as=online 로 온라인 화면 확인
    $user_type = (isset($_GET['as']) && $_GET['as'] === 'online') ? 'online' : 'offline';
} else if ($is_adm && empty($_SESSION['ufs_replay_ok'])) {
    // 관리자 점검: 기본은 온라인 화면, ?as=offline 로 오프라인 화면 확인
    $user_type = (isset($_GET['as']) && $_GET['as'] === 'offline') ? 'offline' : 'online';
}
$is_online_user = ($user_type === 'online');
if ($is_pv && empty($_SESSION['ufs_replay_ok'])) {
    $viewer = $is_online_user ? '온라인 참석자용' : '오프라인 참석자용';
}

/* ── 이용 로그 비컨 (상세 열람 view / 재생 시작 play) ── */
if (isset($_GET['log'])) {
    header('Content-Type: text/plain; charset=utf-8');
    $t = $_GET['log'];
    if ($verified && ($t === 'view' || $t === 'play')) {
        rv_log($t, $apply_no, (int)(isset($_GET['id']) ? $_GET['id'] : 0));
        echo 'ok';
    } else { http_response_code(403); echo 'no'; }
    exit;
}

/* ── 서비스 상태 (관리자는 항상 통과 — 운영 점검) ── */
$state = rv_service_state();      // on | off | before | ended
$svc_open = ($state === 'on' || $is_adm);

/* ── CSRF 토큰 ── */
if (empty($_SESSION['ufs_replay_csrf'])) {
    $_SESSION['ufs_replay_csrf'] = function_exists('random_bytes') ? bin2hex(random_bytes(16)) : md5(uniqid(mt_rand(), true));
}
$csrf = $_SESSION['ufs_replay_csrf'];

/* ── 체크인 처리 ── */
$gate_err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rv_email']) && $svc_open && !$verified) {
    $em  = strtolower(trim($_POST['rv_email']));
    $phd = preg_replace('/[^0-9]/', '', isset($_POST['rv_phone']) ? $_POST['rv_phone'] : '');
    $tok = isset($_POST['rv_csrf']) ? $_POST['rv_csrf'] : '';

    if (!hash_equals($csrf, $tok)) {
        $gate_err = '잘못된 요청입니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.';
    } else if ($em === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $gate_err = '올바른 이메일을 입력해 주세요.';
    } else if (strlen($phd) < 8) {
        $gate_err = '연락처를 정확히 입력해 주세요.';
    } else {
        // 속도 제한 — 같은 IP 10분 내 실패 8회 이상이면 잠시 차단(기획 7.1/16)
        rv_log_table();
        $ipq = sql_real_escape_string(rv_ip());
        $fc  = @sql_fetch("SELECT COUNT(*) c FROM cb_unreal_2026_replay_log
                           WHERE rl_type='gate_fail' AND rl_ip='$ipq' AND rl_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
        if ($fc && (int)$fc['c'] >= 8) {
            $gate_err = '시도 횟수가 많아 잠시 제한되었습니다. 10분 후 다시 시도해 주세요.';
        } else {
            // 등록 자격: 정상 등록(임시X, 취소X). 온라인/오프라인은 상품 코드로 구분해 유형 저장
            $row = sql_fetch("SELECT apply_no, apply_user_name, apply_user_email, apply_product_code
                FROM cb_unreal_2026_event2_apply
                WHERE apply_user_email='" . sql_real_escape_string($em) . "'
                  AND apply_user_phone LIKE '%" . sql_real_escape_string(substr($phd, -8)) . "%'
                  AND apply_temp_yn='N' AND apply_pay_status<>0
                ORDER BY apply_no DESC LIMIT 1");
            if ($row) {
                if (function_exists('session_regenerate_id')) @session_regenerate_id(true);   // 세션 고정 방지(기획 12.2)
                $_SESSION['ufs_replay_ok']    = 1;
                $_SESSION['ufs_replay_name']  = $row['apply_user_name'];
                $_SESSION['ufs_replay_no']    = (int)$row['apply_no'];
                $_SESSION['ufs_replay_email'] = strtolower($row['apply_user_email']);
                $_SESSION['ufs_replay_type']  = ($row['apply_product_code'] === 'ONLINE') ? 'online' : 'offline';
                $_SESSION['ufs_replay_at']    = time();
                $_SESSION['ufs_replay_csrf']  = function_exists('random_bytes') ? bin2hex(random_bytes(16)) : md5(uniqid(mt_rand(), true));
                rv_log('gate_ok', (int)$row['apply_no'], 0, rv_mask_email($em));
                header('Location: ./'); exit;
            }
            rv_log('gate_fail', 0, 0, rv_mask_email($em));
            // 등록 여부 추측이 어렵도록 통합 문구(기획 7.1)
            $gate_err = '다시보기 이용 대상 정보를 확인할 수 없습니다. 등록 시 입력한 이메일과 연락처를 확인해 주세요.';
        }
    }
}

/* ── 트랙별 컬러 — 메인 사이트 schedule.php 의 ufs_sched_colors 와 동일 팔레트 ── */
function rv_track_color($track) {
    $m = array(
        '키노트'            => '#00C1D5',
        '게임: 프로그래밍'   => '#5a9be6',
        '게임: 아트'         => '#fecb8b',
        '미디어 & 엔터테인먼트' => '#ff8674',
        '제조 및 시뮬레이션'  => '#dd9cdf',
        '공통'              => '#dd9cdf',
    );
    return isset($m[$track]) ? $m[$track] : '#00C1D5';
}

/* ── 트랙 표기 — 메인(ufs_track_label_day)과 동일: Day1 의 '제조 및 시뮬레이션'은 '공통' ── */
function rv_track_label($track, $day) {
    if ($track === '제조 및 시뮬레이션') return ((int)$day === 1) ? '공통' : '제조 및 시뮬레이션';
    return $track;
}

/* ── 난이도 라벨 — 세션 상세와 동일 표기 ── */
function rv_level_label($lv) {
    $m = array('초보자용' => '초급', '중급자용' => '중급', '전문가용' => '고급', '전체 참가자' => '전체');
    $lv = trim((string)$lv);
    return isset($m[$lv]) ? $m[$lv] : ($lv !== '' ? $lv : '전체');
}

/* ── 등록 유형별 영상 선택 — 온라인: rv_vimeo(QA 제외) / 오프라인: rv_vimeo_qa(QA 포함).
 *    오프라인용 QA 영상이 아직 없고 원본 영상은 있으면 pending(준비 중) 상태 ── */
function rv_pick_video($s, $is_online_user) {
    $pub = ($s['rv_video_public'] === 'Y');
    if ($is_online_user) {
        $id = trim((string)$s['rv_vimeo']); $hash = trim((string)$s['rv_vimeo_hash']);
        return array('id' => $id, 'hash' => $hash, 'has' => ($pub && $id !== ''), 'pending' => false);
    }
    $id = trim((string)(isset($s['rv_vimeo_qa']) ? $s['rv_vimeo_qa'] : ''));
    $hash = trim((string)(isset($s['rv_vimeo_qa_hash']) ? $s['rv_vimeo_qa_hash'] : ''));
    $has = ($pub && $id !== '');
    $pending = ($pub && $id === '' && trim((string)$s['rv_vimeo']) !== '');
    return array('id' => $id, 'hash' => $hash, 'has' => $has, 'pending' => $pending);
}

/* ── 콤마 구분 컬럼 → 배열 ── */
function rv_csv_arr($v) {
    $out = array();
    foreach (explode(',', (string)$v) as $p) { $p = trim($p); if ($p !== '') $out[] = $p; }
    return $out;
}

/* ── 줄 단위 분리 ── */
function rv_lines_arr($v) {
    $out = array();
    foreach (preg_split('/\r\n|\r|\n/', (string)$v) as $ln) { $ln = trim($ln); if ($ln !== '') $out[] = $ln; }
    return $out;
}

/* ── 연사 배열 — ag_speakers(JSON) 우선, 없으면 ag_sp_* (data/agenda_db.php 와 동일 규칙) ── */
function rv_speakers_arr($r) {
    $json = isset($r['ag_speakers']) ? trim((string)$r['ag_speakers']) : '';
    if ($json !== '') {
        $arr = json_decode($json, true);
        if (is_array($arr)) {
            $out = array();
            foreach ($arr as $sp) {
                if (!is_array($sp)) continue;
                $nm = isset($sp['name']) ? trim($sp['name']) : '';
                if ($nm === '') continue;
                $out[] = array('n' => $nm,
                    'r' => isset($sp['role']) ? trim($sp['role']) : '',
                    'c' => isset($sp['company']) ? trim($sp['company']) : '',
                    'p' => isset($sp['photo']) ? trim($sp['photo']) : '',
                    'b' => isset($sp['bio']) ? trim($sp['bio']) : '');
            }
            if ($out) return $out;
        }
    }
    $name = isset($r['ag_sp_name']) ? trim($r['ag_sp_name']) : '';
    if ($name === '') return array();
    $comp  = isset($r['ag_sp_company']) ? trim($r['ag_sp_company']) : '';
    $photo = isset($r['ag_sp_photo']) ? trim($r['ag_sp_photo']) : '';
    $parts = preg_split('#\s*/\s*#u', $name);
    if (is_array($parts) && count($parts) > 1) {
        $out = array();
        foreach ($parts as $i => $nm) {
            $nm = trim($nm); if ($nm === '') continue;
            $out[] = array('n' => $nm, 'r' => '', 'c' => $comp, 'p' => ($i === 0 ? $photo : ''), 'b' => '');
        }
        if ($out) return $out;
    }
    return array(array('n' => $name, 'r' => isset($r['ag_sp_role']) ? trim($r['ag_sp_role']) : '', 'c' => $comp, 'p' => $photo, 'b' => ''));
}

/* ── 제목 표기 — <게임명> 같은 꺾쇠 묶음이 중간에 줄바꿈되지 않도록 감싼다 ── */
function rv_title_html($t) {
    $e = rv_e($t);
    return preg_replace('/&lt;([^&]{1,80}?)&gt;/u', '<span class="nb">&lt;$1&gt;</span>', $e);
}

/* ── 자막 자동 표시 — 영상 ID → 자막 언어 (Vimeo 플레이어 texttrack 파라미터) ── */
$RV_TEXTTRACK = array(
    '1227252168' => 'ko',   // <이환> 온라인용 — 해외 연사, 한국어 자막 자동 켜기
    // 오프라인용 QA 영상 — 외국어 발화 구간 한국어 자막 (2026-09-17 업로드)
    '1227577677' => 'ko',   // Chris_day1_QA
    '1227578714' => 'ko',   // Shao_day1_QA
    '1227577675' => 'ko',   // Iso_Sega_QA
    '1227578865' => 'ko',   // shinoyama_EGJ_QA
    '1227577678' => 'ko',   // Ari_day1_QA
    '1227577676' => 'ko',   // Magnus_pt1_QA
    '1227578555' => 'ko',   // Magnus_pt2_QA
    '1227592684' => 'ko',   // Liu_Feng_이환_QA
    '1227592682' => 'ko',   // Dmitriy_EGK_QA
    '1227592681' => 'ko',   // Ari_day2_QA
    '1227592683' => 'ko',   // Chris_day2_QA
);

/* ── 공개 세션 로드 (인증 후에만 — 인증 전 HTML 에 영상·PDF 정보 미포함) ── */
$DAYS = array('1' => 'Day 1 · 8월 20일(목)', '2' => 'Day 2 · 8월 21일(금)');
$sessions = array(); $tracks = array(); $JS = array();
if ($verified && $svc_open) {
    $rs = @sql_query("SELECT a.*,
               v.rv_vimeo, v.rv_vimeo_hash, v.rv_vimeo_qa, v.rv_vimeo_qa_hash, v.rv_thumb, v.rv_video_public,
               v.rv_pdf_url, v.rv_pdf_name, v.rv_pdf_size, v.rv_pdf_public
        FROM cb_unreal_2026_replay_vod v
        JOIN cb_unreal_2026_agenda a ON a.ag_no = v.rv_agno
        WHERE v.rv_show='Y' AND a.ag_is_active='Y'
        ORDER BY a.ag_day ASC, a.ag_sort ASC, a.ag_no ASC");
    if ($rs) { while ($x = sql_fetch_array($rs)) $sessions[] = $x; }

    foreach ($sessions as $s) {
        $tlabel = rv_track_label($s['ag_track'], (int)$s['ag_day']);
        if ($tlabel !== '' && !in_array($tlabel, $tracks, true)) $tracks[] = $tlabel;
        $pick = rv_pick_video($s, $is_online_user);
        $has_video = $pick['has'];  $qa_pending = $pick['pending'];
        $has_pdf   = ($s['rv_pdf_public'] === 'Y' && $s['rv_pdf_url'] !== '');
        if (!$has_video && !$has_pdf && !$qa_pending) continue;   // 미공개 세션 — 데이터 미포함
        // 키워드: 분야 → 주제 → 제품 순, 중복 제거 (세션 상세와 동일)
        $kw = array();
        foreach (array('ag_field', 'ag_topic', 'ag_product') as $kk) {
            if (!isset($s[$kk])) continue;
            foreach (rv_csv_arr($s[$kk]) as $kv) {
                if ($kv === '기타/Others' || $kv === '기타' || $kv === 'Others') continue;
                if (!in_array($kv, $kw, true)) $kw[] = $kv;
            }
        }
        $JS[(int)$s['ag_no']] = array(
            'title'   => $s['ag_title'],
            'day'     => (int)$s['ag_day'],
            'time'    => $s['ag_time'],
            'track'   => $tlabel,
            'level'   => rv_level_label(isset($s['ag_level']) ? $s['ag_level'] : ''),
            'desc'    => $s['ag_desc'],
            'kw'      => $kw,
            'sps'     => rv_speakers_arr($s),
            'tc'      => rv_track_color($tlabel),
            'embed'   => $has_video
                ? rv_vimeo_embed($pick['id'], $pick['hash'])
                  . (isset($RV_TEXTTRACK[$pick['id']]) ? '&texttrack=' . $RV_TEXTTRACK[$pick['id']] : '')
                : '',
            'qa'      => $qa_pending ? 1 : 0,
            'pdf'     => $has_pdf ? 1 : 0,
            'pdf_name'=> $has_pdf ? ($s['rv_pdf_name'] !== '' ? $s['rv_pdf_name'] : '강연자료 PDF') : '',
            'pdf_size'=> $has_pdf ? $s['rv_pdf_size'] : '',
        );
    }
}
$notice = rv_cfg('replay_notice', '');
$period_end = rv_cfg('replay_end', '');
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>언리얼 페스트 서울 2026 다시보기 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--mint:#00FFC8;--text:#eaeaef;--muted:#8b8b96}
*{box-sizing:border-box}html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;-webkit-font-smoothing:antialiased;word-break:keep-all;
 background-image:radial-gradient(900px 500px at 78% -8%, rgba(0,193,213,.10), transparent 60%)}
a{color:inherit;text-decoration:none}
button{font-family:inherit}
.rv-top{position:sticky;top:0;z-index:20;padding:13px clamp(14px,4vw,40px);background:rgba(8,8,10,.72);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
.rv-topin{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:14px}
.rv-toplogo{height:24px;width:auto;display:block}
.rv-user{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--muted);white-space:nowrap}
.rv-user b{color:var(--text);font-weight:700}
.rv-out{padding:6px 13px;border:1px solid var(--line2);font-size:12px;color:var(--muted);background:transparent;cursor:pointer}
.rv-out:hover{border-color:var(--teal);color:var(--teal)}
.rv-wrap{max-width:1280px;margin:0 auto;padding:clamp(18px,3vw,36px) clamp(14px,4vw,40px) 64px}
.rv-h1{font-size:clamp(24px,4vw,34px);font-weight:900;letter-spacing:-.01em;margin:0 0 6px}
.rv-sub{color:var(--muted);font-size:14px;margin:0 0 10px;line-height:1.7}
.rv-legal{color:#6b6b76;font-size:12px;line-height:1.7;margin:0 0 24px}
.rv-notice{border:1px solid rgba(0,193,213,.35);background:rgba(0,193,213,.07);color:#bfeef4;font-size:13px;padding:11px 14px;margin:0 0 20px;line-height:1.6}
/* filters */
.rv-filters{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:0 0 22px}
.rv-tabs{display:flex}
.rv-tab{padding:9px 18px;font-size:13px;font-weight:800;background:transparent;border:1px solid var(--line2);color:var(--muted);cursor:pointer;margin-left:-1px}
.rv-tab:first-child{margin-left:0}
.rv-tab.on{background:var(--teal);border-color:var(--teal);color:#00232a}
.rv-sel{padding:9px 12px;background:var(--panel);border:1px solid var(--line2);color:var(--text);font-size:13px}
/* 트랙별 필터 — 메인 사이트 트랙 컬러 적용 */
.rv-ttabs{display:flex;flex-wrap:wrap}
.rv-ttab{padding:9px 14px;font-size:13px;font-weight:800;background:transparent;border:1px solid var(--line2);color:var(--muted);cursor:pointer;margin-left:-1px;display:flex;align-items:center;gap:7px}
.rv-ttab:first-child{margin-left:0}
.rv-ttab .tdot{width:8px;height:8px;border-radius:50%;background:var(--tc,var(--muted));flex:none}
.rv-ttab:hover{color:var(--tc,var(--teal))}
.rv-ttab.on{border-color:var(--tc,var(--teal));color:var(--tc,var(--teal));background:rgba(255,255,255,.05);position:relative;z-index:1}
.rv-search{flex:1;min-width:180px;max-width:320px;padding:9px 13px;background:var(--panel);border:1px solid var(--line2);color:var(--text);font-size:13px}
.rv-search:focus,.rv-sel:focus{outline:none;border-color:var(--teal)}
/* grid */
.rv-day{font-size:15px;font-weight:800;color:#fff;margin:26px 0 12px;padding-left:11px;border-left:3px solid var(--teal)}
.rv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.rv-card{border:1px solid var(--line);background:var(--panel);cursor:pointer;transition:.15s;overflow:hidden;display:flex;flex-direction:column;text-align:left;padding:0;color:inherit}
.rv-card:hover,.rv-card:focus-visible{border-color:var(--teal);transform:translateY(-2px);outline:none}
/* 미공개 세션 — 회색 처리 + 클릭 불가 */
.rv-card.off{opacity:.45;filter:grayscale(1);cursor:default;pointer-events:none}
.rv-card.off:hover{border-color:var(--line);transform:none}
.rv-card.off .rv-trk{color:var(--muted) !important}
.rv-thumb{position:relative;width:100%;padding-top:56.25%;background:#0a0d12 center/cover no-repeat}
.rv-thumb .ph{position:absolute;inset:0;background:linear-gradient(135deg,#0c1218 0%,#0a2026 60%,#083038 100%)}
.rv-thumb .play{position:absolute;inset:0;display:grid;place-items:center}
.rv-thumb .play svg{width:52px;height:52px;color:#fff;filter:drop-shadow(0 2px 8px rgba(0,0,0,.6));opacity:.92}
.rv-meta{padding:13px 15px}
.rv-trk{font-size:11px;font-weight:800;color:var(--teal);letter-spacing:.02em}
.rv-title{font-size:14.5px;font-weight:700;color:#fff;margin:5px 0 6px;line-height:1.4}
.rv-sp{font-size:12px;color:var(--muted)}
.rv-badges{display:flex;gap:6px;flex-wrap:wrap;margin-top:9px}
.rv-bdg{display:inline-block;font-size:10.5px;font-weight:800;padding:2px 8px;letter-spacing:.02em}
.rv-bdg.v{background:rgba(0,193,213,.14);color:var(--teal);border:1px solid rgba(0,193,213,.4)}
.rv-bdg.p{background:rgba(0,255,200,.10);color:var(--mint);border:1px solid rgba(0,255,200,.35)}
.rv-bdg.w{background:rgba(139,139,150,.12);color:var(--muted);border:1px solid var(--line2)}
.rv-dt{font-size:11px;color:#6b6b76;margin-top:3px}
.rv-empty{border:1px dashed var(--line2);padding:48px 24px;text-align:center;color:var(--muted);font-size:14px;line-height:1.8}
/* gate & state */
.rv-gate{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:24px}
.rv-gcard{width:100%;max-width:440px;background:var(--panel);border:1px solid var(--line);padding:38px 34px}
.rv-glogo{width:200px;max-width:64%;height:auto;display:block;margin-bottom:22px}
.rv-gcard h1{font-size:23px;font-weight:900;color:#fff;margin:0 0 8px}
.rv-gcard p{font-size:13.5px;color:var(--muted);margin:0 0 20px;line-height:1.7}
.rv-gcard input{width:100%;padding:14px 16px;background:#08080b;border:1px solid var(--line2);color:#fff;font-size:15px;margin-bottom:12px}
.rv-gcard input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.rv-gcard button{width:100%;padding:15px;background:var(--teal);color:#00232a;border:0;font-size:15px;font-weight:900;cursor:pointer}
.rv-err{color:#ff8a8a;font-size:13px;margin-bottom:12px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);padding:9px 12px;line-height:1.6}
/* modal */
.rv-mask{position:fixed;inset:0;z-index:60;display:none;align-items:flex-start;justify-content:center;padding:20px;background:rgba(4,4,6,.85);overflow-y:auto}
.rv-mask.on{display:flex}
.rv-modal{width:100%;max-width:920px;background:#0b0b0f;border:1px solid var(--line2);margin:2vh auto}
.rv-mhd{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:13px 18px;background:#0c0c10;border-bottom:1px solid var(--line)}
.rv-mhd .t{font-size:14.5px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rv-mx{background:transparent;border:0;color:var(--muted);font-size:26px;cursor:pointer;line-height:1;padding:0 2px}
.rv-mx:hover{color:#fff}
.rv-frame{position:relative;width:100%;padding-top:56.25%;background:#000}
.rv-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.rv-frame .noem{position:absolute;inset:0;display:grid;place-items:center;color:var(--muted);font-size:14px;background:linear-gradient(135deg,#0c1218,#0a2026);text-align:center;padding:20px;line-height:1.8}
.rv-mbody{padding:20px 22px 26px}
.rv-mtitle{font-size:19px;font-weight:800;color:#fff;margin:0 0 4px;line-height:1.45;word-break:keep-all;overflow-wrap:break-word}
.nb{white-space:nowrap}
.rv-title,.rv-mtitle{word-break:keep-all;overflow-wrap:break-word}
.rv-mdesc{font-size:13.5px;color:#c9c9d2;line-height:1.8;margin:0;white-space:pre-line}
/* 모달 상세 섹션 — 세션 상세 페이지와 동일 구성 */
.rv-chips{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 12px}
.rv-chip{font-size:11px;font-weight:800;padding:3px 9px;border:1px solid var(--line2);border-radius:4px;color:#d4d4d8;background:rgba(255,255,255,.05)}
.rv-msec{margin:20px 0 0}
.rv-msec h3{font-size:14px;font-weight:800;color:#fff;margin:0 0 9px}
.rv-mlist{list-style:none;margin:0;padding:0}
.rv-mlist li{position:relative;padding-left:14px;font-size:13px;color:#a1a1aa;line-height:1.7;margin-bottom:5px;word-break:keep-all}
.rv-mlist li:before{content:'';position:absolute;left:0;top:.62em;width:4px;height:4px;border-radius:50%;background:var(--teal)}
.rv-mlist li.l2{margin-left:18px}
.rv-mlist li.l2:before{background:transparent;border:1px solid var(--teal)}
.rv-kw span{display:inline-block;font-size:11.5px;color:#a1a1aa;border:1px solid #27272a;border-radius:4px;padding:3px 9px;margin:0 6px 6px 0}
.rv-spcard{display:flex;gap:13px;align-items:flex-start;border:1px solid var(--line);background:var(--panel);padding:13px 15px;margin:0 0 10px;font-size:13px}
.rv-spcard img{width:52px;height:52px;border-radius:50%;object-fit:cover;flex:none;background:#15151b}
.rv-spcard .nm{font-weight:800;color:#fff}
.rv-spcard .co{color:var(--muted);font-size:12px;margin-top:2px}
.rv-spcard details{margin-top:7px}
.rv-spcard summary{font-size:12px;font-weight:800;color:var(--teal);cursor:pointer;list-style:none}
.rv-spcard summary::-webkit-details-marker{display:none}
.rv-spcard .bio{font-size:12.5px;color:#a1a1aa;line-height:1.75;margin:8px 0 0;word-break:keep-all}
.rv-pdfbox{border:1px solid var(--line);background:var(--panel);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 16px}
.rv-pdfbox .pn{font-size:13px;font-weight:700;color:#fff}
.rv-pdfbox .ps{font-size:11.5px;color:var(--muted);margin-top:3px}
.rv-pdfbtn{display:inline-block;padding:10px 20px;background:transparent;border:1px solid var(--mint);color:var(--mint);font-size:13px;font-weight:800;cursor:pointer}
.rv-pdfbtn:hover{background:rgba(0,255,200,.08)}
.rv-nopdf{font-size:12.5px;color:var(--muted);border:1px dashed var(--line2);padding:11px 14px;margin:0 0 16px}
.rv-copyright{font-size:11.5px;color:#6b6b76;line-height:1.7;border-top:1px solid var(--line);padding-top:13px}
@media(max-width:640px){.rv-filters{gap:8px}.rv-search{max-width:none}}
/* footer — 메인(_pf_footer.php) 구성 재현 */
.rvf{background:#000;border-top:1px solid rgba(255,255,255,.1);padding:64px clamp(16px,4vw,40px) 40px;font-size:14px}
.rvf-in{max-width:1280px;margin:0 auto}
.rvf-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:48px;margin-bottom:56px}
.rvf-logo{height:32px;width:auto;display:block;margin-bottom:28px}
.rvf-sns{display:flex;gap:14px;margin-bottom:28px}
.rvf-sns a{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;color:#94a3b8;transition:.15s}
.rvf-sns a:hover{color:#fff;border-color:var(--teal)}
.rvf-sns svg{width:16px;height:16px}
.rvf-biz{font-size:12px;line-height:1.8;color:#64748b}
.rvf-biz p{margin:0 0 4px}
.rvf-biz a{text-decoration:underline}
.rvf-biz a:hover{color:#fff}
.rvf h4{color:#fff;font-weight:700;margin:0 0 22px;letter-spacing:.1em;font-size:12px;text-transform:uppercase}
.rvf-links{list-style:none;margin:0;padding:0}
.rvf-links li{margin-bottom:14px}
.rvf-links a{color:#94a3b8;font-size:13.5px;transition:.15s}
.rvf-links a:hover{color:var(--teal)}
.rvf-bottom{border-top:1px solid rgba(255,255,255,.1);padding-top:28px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;color:#64748b;font-size:12.5px;line-height:1.7}
.rvf-bottom .lg{display:flex;gap:22px}
.rvf-bottom a:hover{color:#fff}
@media(max-width:820px){.rvf-grid{grid-template-columns:1fr;gap:36px}.rvf-bottom{flex-direction:column;align-items:flex-start}}
</style>
</head>
<body>
<header class="rv-top">
  <div class="rv-topin">
    <a href="../index.php" aria-label="Unreal Fest Seoul 2026"><img class="rv-toplogo" src="../white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
    <?php if ($verified && $svc_open): ?><div class="rv-user"><span><b><?= rv_e($viewer) ?></b><?= ($is_pv && empty($_SESSION['ufs_replay_ok'])) ? '' : '님' ?></span><a class="rv-out" href="./?logout=1">로그아웃</a></div><?php endif; ?>
  </div>
</header>

<?php if (!$svc_open): ?>
  <!-- 서비스 미운영 상태 -->
  <div class="rv-gate">
    <div class="rv-gcard" style="text-align:center">
      <img class="rv-glogo" src="../white_logo.svg" alt="Unreal Fest Seoul 2026" style="margin-left:auto;margin-right:auto">
      <h1>언리얼 페스트 서울 2026 다시보기</h1>
      <?php if ($state === 'ended'): ?>
        <p>언리얼 페스트 서울 2026 다시보기 제공 기간이 종료되었습니다.</p>
      <?php else: ?>
        <p>다시보기가 준비 중입니다.<br>영상과 강연자료가 준비되는 대로 순차적으로 공개됩니다.</p>
      <?php endif; ?>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">문의: 사무국 02-326-3701 · info@epiclounge.co.kr</p>
    </div>
  </div>

<?php elseif (!$verified): ?>
  <!-- 체크인 -->
  <div class="rv-gate">
    <div class="rv-gcard">
      <img class="rv-glogo" src="../white_logo.svg" alt="Unreal Fest Seoul 2026">
      <h1>언리얼 페스트 서울 2026 다시보기</h1>
      <p>언리얼 페스트 서울 2026 <b style="color:#cfd0d6">등록자</b>를 위한 다시보기 페이지입니다. 등록 시 입력한 이메일과 연락처로 체크인해 주세요.</p>
      <?php if ($gate_err): ?><div class="rv-err"><?= rv_e($gate_err) ?></div><?php endif; ?>
      <form method="post" action="./">
        <input type="hidden" name="rv_csrf" value="<?= rv_e($csrf) ?>">
        <input type="email" name="rv_email" autocapitalize="off" autocomplete="email" placeholder="등록 이메일" required autofocus>
        <input type="tel" name="rv_phone" inputmode="numeric" autocomplete="tel" placeholder="등록 연락처" required>
        <button type="submit">다시보기 입장 →</button>
      </form>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">등록 확인이 안 되면 사무국으로 문의해 주세요.<br>02-326-3701 · info@epiclounge.co.kr</p>
    </div>
  </div>

<?php else: ?>
  <!-- 목록 -->
  <div class="rv-wrap">
    <h1 class="rv-h1">언리얼 페스트 서울 2026 다시보기</h1>
    <p class="rv-sub">언리얼 페스트 서울 2026 세션 영상과 강연자료를 다시 확인하실 수 있습니다.<?php if ($period_end !== ''): ?> 다시보기는 <b style="color:#cfd0d6"><?= rv_e(substr($period_end, 0, 10)) ?></b>까지 제공됩니다.<?php endif; ?></p>
    <?php if ($notice !== ''): ?><div class="rv-notice"><?= nl2br(rv_e($notice)) ?></div><?php endif; ?>
    <?php
      // 오프라인 등록자 안내 — QA 포함 영상이 하나도 등록되지 않은 동안만 표시
      if (!$is_online_user) {
          $qa_ready = 0;
          foreach ($sessions as $qs) { if (trim((string)(isset($qs['rv_vimeo_qa']) ? $qs['rv_vimeo_qa'] : '')) !== '') { $qa_ready = 1; break; } }
          if (!$qa_ready): ?>
    <div class="rv-notice">오프라인 참석자용 <b style="color:#cfd0d6">Q&amp;A 포함 영상</b>은 현재 준비 중입니다. 준비되는 대로 순차 공개되며, 강연자료 PDF는 지금 바로 이용하실 수 있습니다.</div>
    <?php endif; } ?>

    <?php if (!$sessions): ?>
      <div class="rv-empty">다시보기가 준비 중입니다.<br>영상과 강연자료가 준비되는 대로 순차적으로 공개됩니다.</div>
    <?php else: ?>

    <div class="rv-filters">
      <div class="rv-tabs" role="tablist">
        <button type="button" class="rv-tab on" data-day="all">전체</button>
        <button type="button" class="rv-tab" data-day="1">Day 1</button>
        <button type="button" class="rv-tab" data-day="2">Day 2</button>
      </div>
      <div class="rv-ttabs" role="tablist" aria-label="트랙 필터">
        <button type="button" class="rv-ttab on" data-trk="">모든 트랙</button>
        <?php foreach (array('공통', '게임: 아트', '게임: 프로그래밍', '미디어 & 엔터테인먼트', '제조 및 시뮬레이션') as $t): ?>
        <button type="button" class="rv-ttab" data-trk="<?= rv_e($t) ?>" style="--tc:<?= rv_e(rv_track_color($t)) ?>"><span class="tdot"></span><?= rv_e($t) ?></button>
        <?php endforeach; ?>
      </div>
      <input type="search" class="rv-search" id="rvSearch" placeholder="세션명 · 발표자 검색" aria-label="세션 검색">
    </div>

    <?php foreach (array('1','2') as $d): ?>
      <?php $dsess = array(); foreach ($sessions as $s) { if ((string)(int)$s['ag_day'] === $d) $dsess[] = $s; } if (!$dsess) continue; ?>
      <div class="rv-day" data-dayhead="<?= $d ?>"><?= rv_e($DAYS[$d]) ?></div>
      <div class="rv-grid" data-daygrid="<?= $d ?>">
        <?php foreach ($dsess as $s):
          $agno = (int)$s['ag_no'];
          $pick = rv_pick_video($s, $is_online_user);
          $has_video = $pick['has'];  $qa_pending = $pick['pending'];
          $has_pdf   = ($s['rv_pdf_public'] === 'Y' && $s['rv_pdf_url'] !== '');
          $closed    = (!$has_video && !$has_pdf && !$qa_pending);   // 미공개 세션 — 목록에서 제외
          if ($closed) continue;
          $tlabel = rv_track_label($s['ag_track'], (int)$s['ag_day']);
          $thumb = trim($s['rv_thumb']);
          // 발표자 — 복수 연사(ag_speakers)면 전원 표기 (예: 심성민 · 남기호 · 엑스온스튜디오)
          $csps = rv_speakers_arr($s);
          $csp_names = array(); foreach ($csps as $csp) $csp_names[] = $csp['n'];
          $csp_co = ($csps && $csps[0]['c'] !== '') ? $csps[0]['c'] : trim($s['ag_sp_company']);
          $hay = mb_strtolower($s['ag_title'] . ' ' . implode(' ', $csp_names) . ' ' . $csp_co, 'UTF-8');
        ?>
        <button type="button" class="rv-card<?= $closed ? ' off' : '' ?>"<?= $closed ? ' disabled tabindex="-1"' : '' ?> data-id="<?= $agno ?>" data-day="<?= (int)$s['ag_day'] ?>"
                data-track="<?= rv_e($tlabel) ?>" data-hay="<?= rv_e($hay) ?>">
          <div class="rv-thumb"<?= $thumb !== '' ? ' style="background-image:url(\'' . rv_e($thumb) . '\')"' : '' ?>>
            <?php if ($thumb === ''): ?><div class="ph"></div><?php endif; ?>
            <?php if ($has_video): ?>
            <div class="play"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="rgba(0,0,0,.45)"/><path d="M10 8.5l6 3.5-6 3.5z" fill="#fff"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="rv-meta">
            <?php if ($tlabel !== ''): ?><div class="rv-trk" style="color:<?= rv_e(rv_track_color($tlabel)) ?>"><?= rv_e($tlabel) ?></div><?php endif; ?>
            <?php // 내부 프리뷰(오프라인 화면)에서만 별도 QA(Q&A 포함) 영상 세션에 제목 뒤 * 표시 — 실사용자에겐 미표시
              $qa_mark = ($is_pv && !$is_online_user && trim((string)$s['rv_vimeo_qa']) !== '' && $s['rv_vimeo_qa'] !== $s['rv_vimeo']); ?>
            <div class="rv-title"><?= rv_title_html($s['ag_title']) ?><?= $qa_mark ? ' <b style="color:#00FFC8">*</b>' : '' ?></div>
            <?php if ($csp_names): ?><div class="rv-sp"><?= rv_e(implode(' · ', $csp_names)) ?><?= $csp_co !== '' ? ' · ' . rv_e($csp_co) : '' ?></div><?php endif; ?>
            <div class="rv-badges">
              <?php if ($has_pdf): ?><span class="rv-bdg p">강연자료</span><?php endif; ?>
              <?php if ($closed): ?><span class="rv-bdg w">미공개</span>
              <?php elseif ($qa_pending): ?><span class="rv-bdg w">Q&amp;A 포함 영상 준비 중</span>
              <?php elseif (!$has_video): ?><span class="rv-bdg w">영상 준비 중</span><?php endif; ?>
            </div>
          </div>
        </button>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <div class="rv-empty" id="rvNoResult" style="display:none">검색 결과가 없습니다.</div>
    <?php endif; ?>
  </div>

  <!-- 상세 모달 -->
  <div class="rv-mask" id="rvMask" role="dialog" aria-modal="true">
    <div class="rv-modal">
      <div class="rv-mhd"><span class="t" id="rvMTop"></span><button type="button" class="rv-mx" id="rvClose" aria-label="닫기">&times;</button></div>
      <div class="rv-frame" id="rvFrame"></div>
      <div class="rv-mbody">
        <div class="rv-chips" id="rvMChips"></div>
        <h2 class="rv-mtitle" id="rvMTitle"></h2>
        <div class="rv-pdfbox" id="rvMPdf" style="display:none;margin-top:14px">
          <div><div class="pn" id="rvMPdfNm"></div><div class="ps" id="rvMPdfSz"></div></div>
          <a class="rv-pdfbtn" id="rvMPdfBtn" href="#" target="_blank" rel="noopener">강연자료 PDF 보기 ↗</a>
        </div>
        <div class="rv-nopdf" id="rvMNoPdf" style="display:none;margin-top:14px">발표자료가 제공되지 않는 세션입니다.</div>
        <div class="rv-msec" id="rvMDescW" style="display:none"><h3>세션 소개</h3><p class="rv-mdesc" id="rvMDesc"></p></div>
        <div class="rv-msec" id="rvMKwW" style="display:none"><h3>키워드</h3><div class="rv-kw" id="rvMKw"></div></div>
        <div class="rv-msec" id="rvMSpsW" style="display:none"><h3>연사</h3><div id="rvMSps"></div></div>
      </div>
    </div>
  </div>

  <script>
  var RV = <?= json_encode($JS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  var rvPlayed = {};
  function $id(x){ return document.getElementById(x); }
  function rvBeacon(type, id){
    try { var i = new Image(); i.src = './?log=' + type + '&id=' + id + '&_=' + Date.now(); } catch(e){}
  }
  /* 제목 세팅 — <게임명> 꺾쇠 묶음은 nowrap 스팬으로 감싸 줄바꿈 방지 (textContent 만 사용) */
  function rvSetTitle(el, t){
    while (el.firstChild) el.removeChild(el.firstChild);
    (t || '').split(/(<[^<>]{1,80}>)/).forEach(function(p){
      if (!p) return;
      if (p.charAt(0) === '<' && p.charAt(p.length - 1) === '>') {
        var sp = document.createElement('span'); sp.className = 'nb'; sp.textContent = p; el.appendChild(sp);
      } else {
        el.appendChild(document.createTextNode(p));
      }
    });
  }
  function rvOpen(id){
    var d = RV[id]; if (!d) return;
    $id('rvMTop').textContent = d.title;
    // 뱃지: 트랙(컬러) + 난이도
    var chips = $id('rvMChips');
    while (chips.firstChild) chips.removeChild(chips.firstChild);
    if (d.track) {
      var c1 = document.createElement('span'); c1.className = 'rv-chip';
      c1.textContent = d.track;
      if (d.tc) { c1.style.color = d.tc; c1.style.borderColor = d.tc; c1.style.background = 'transparent'; }
      chips.appendChild(c1);
    }
    if (d.level) { var c2 = document.createElement('span'); c2.className = 'rv-chip'; c2.textContent = d.level; chips.appendChild(c2); }
    rvSetTitle($id('rvMTitle'), d.title);
    // 세션 소개
    $id('rvMDescW').style.display = d.desc ? '' : 'none';
    $id('rvMDesc').textContent = d.desc || '';
    // 키워드
    var kw = $id('rvMKw');
    while (kw.firstChild) kw.removeChild(kw.firstChild);
    (d.kw || []).forEach(function(k){ var s = document.createElement('span'); s.textContent = k; kw.appendChild(s); });
    $id('rvMKwW').style.display = (d.kw && d.kw.length) ? '' : 'none';
    // 연사 (복수 지원 + 사진 + 약력)
    var sps = $id('rvMSps');
    while (sps.firstChild) sps.removeChild(sps.firstChild);
    (d.sps || []).forEach(function(sp){
      if (!sp || !sp.n) return;
      var card = document.createElement('div'); card.className = 'rv-spcard';
      if (sp.p) { var im = document.createElement('img'); im.src = sp.p; im.alt = sp.n; im.loading = 'lazy'; im.onerror = function(){ this.style.display = 'none'; }; card.appendChild(im); }
      var box = document.createElement('div');
      var nm = document.createElement('div'); nm.className = 'nm'; nm.textContent = sp.n; box.appendChild(nm);
      var co = [sp.r, sp.c].filter(Boolean).join(' · ');
      if (co) { var cd = document.createElement('div'); cd.className = 'co'; cd.textContent = co; box.appendChild(cd); }
      if (sp.b) {
        var dt = document.createElement('details');
        var sm = document.createElement('summary'); sm.textContent = '약력 보기'; dt.appendChild(sm);
        var bp = document.createElement('p'); bp.className = 'bio'; bp.textContent = sp.b; dt.appendChild(bp);
        box.appendChild(dt);
      }
      card.appendChild(box); sps.appendChild(card);
    });
    $id('rvMSpsW').style.display = (d.sps && d.sps.length) ? '' : 'none';
    var fr = $id('rvFrame');
    while (fr.firstChild) fr.removeChild(fr.firstChild);
    if (d.embed) {
      var ifr = document.createElement('iframe');
      ifr.src = d.embed;
      ifr.title = '다시보기';
      ifr.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
      ifr.setAttribute('allowfullscreen', '');
      ifr.setAttribute('referrerpolicy', 'strict-origin');
      fr.appendChild(ifr);
      if (!rvPlayed[id]) { rvPlayed[id] = 1; rvBeacon('play', id); }
    } else {
      var no = document.createElement('div');
      no.className = 'noem';
      no.textContent = d.qa ? 'Q&A가 포함된 다시보기 영상을 준비 중입니다. 준비되는 대로 공개됩니다.'
                            : '해당 세션의 다시보기 영상은 준비 중입니다.';
      fr.appendChild(no);
    }
    if (d.pdf) {
      $id('rvMPdf').style.display = 'flex';
      $id('rvMNoPdf').style.display = 'none';
      $id('rvMPdfNm').textContent = d.pdf_name;
      $id('rvMPdfSz').textContent = d.pdf_size || '';
      $id('rvMPdfBtn').href = 'pdf.php?id=' + id;
    } else {
      $id('rvMPdf').style.display = 'none';
      $id('rvMNoPdf').style.display = 'block';
    }
    $id('rvMask').classList.add('on');
    document.body.style.overflow = 'hidden';
    rvBeacon('view', id);
    $id('rvClose').focus();
  }
  function rvCloseModal(){
    var fr = $id('rvFrame');
    while (fr.firstChild) fr.removeChild(fr.firstChild);   // iframe 제거 = 재생 정지
    $id('rvMask').classList.remove('on');
    document.body.style.overflow = '';
  }
  document.addEventListener('click', function(ev){
    var card = ev.target.closest ? ev.target.closest('.rv-card') : null;
    if (card) { if (card.classList.contains('off')) return; rvOpen(parseInt(card.getAttribute('data-id'), 10)); return; }
    if (ev.target === $id('rvMask') || ev.target === $id('rvClose')) rvCloseModal();
  });
  document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape') rvCloseModal(); });

  /* 필터 — Day 탭 + 트랙 + 검색 */
  var rvDay = 'all', rvTrk = '', rvQ = '';
  function rvFilter(){
    var shown = 0;
    document.querySelectorAll('.rv-card').forEach(function(c){
      var ok = (rvDay === 'all' || c.getAttribute('data-day') === rvDay)
            && (rvTrk === '' || rvTrk.split('|').indexOf(c.getAttribute('data-track')) !== -1)
            && (rvQ === '' || c.getAttribute('data-hay').indexOf(rvQ) !== -1);
      c.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    ['1','2'].forEach(function(d){
      var g = document.querySelector('[data-daygrid="' + d + '"]');
      var h = document.querySelector('[data-dayhead="' + d + '"]');
      if (!g || !h) return;
      var any = Array.prototype.some.call(g.querySelectorAll('.rv-card'), function(c){ return c.style.display !== 'none'; });
      g.style.display = any ? '' : 'none';
      h.style.display = any ? '' : 'none';
    });
    var nr = $id('rvNoResult'); if (nr) nr.style.display = shown ? 'none' : 'block';
  }
  document.querySelectorAll('.rv-tab').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('.rv-tab').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on'); rvDay = b.getAttribute('data-day'); rvFilter();
    });
  });
  document.querySelectorAll('.rv-ttab').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('.rv-ttab').forEach(function(x){ x.classList.remove('on'); });
      b.classList.add('on'); rvTrk = b.getAttribute('data-trk'); rvFilter();
    });
  });
  var srch = $id('rvSearch');
  if (srch) srch.addEventListener('input', function(){ rvQ = srch.value.trim().toLowerCase(); rvFilter(); });
  </script>
<?php endif; ?>

<!-- 푸터 — 메인(_pf_footer.php) 구성 재현 -->
<footer class="rvf">
  <div class="rvf-in">
    <div class="rvf-grid">
      <div>
        <img class="rvf-logo" src="../white_logo.svg" alt="Unreal Fest 2026">
        <div class="rvf-sns">
          <a href="https://www.youtube.com/@unrealenginekr" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
          </a>
          <a href="https://cafe.naver.com/unrealenginekr" target="_blank" rel="noopener noreferrer" aria-label="네이버 카페">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727v12.845z"/></svg>
          </a>
          <a href="https://pf.kakao.com/_xfdmNb" target="_blank" rel="noopener noreferrer" aria-label="카카오톡 채널">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C6.477 3 2 6.463 2 10.691c0 2.818 1.867 5.29 4.682 6.678-.177.63-.64 2.284-.733 2.64-.117.443.162.436.34.317.14-.093 2.23-1.516 3.132-2.132.52.078 1.053.12 1.579.12 5.523 0 10-3.463 10-7.623C22 6.463 17.523 3 12 3z"/></svg>
          </a>
        </div>
        <div class="rvf-biz">
          <p>법인명(상호): 주식회사 그리프 &nbsp;·&nbsp; 대표이사: 황성현 &nbsp;·&nbsp; 사업자등록번호: 859-88-00263 <a href="https://www.ftc.go.kr/bizCommPop.do?wrkr_no=8598800263" target="_blank" rel="noopener noreferrer">확인</a></p>
          <p>통신판매업: 2018-서울송파-0571 &nbsp;·&nbsp; 개인정보보호책임자: 오승훈</p>
          <p>주소: 서울 성동구 광나루로8길 31, SK V1 CENTER2, 1102-1103호</p>
          <p>고객센터: 02-326-3701 &nbsp;·&nbsp; 대표 이메일: <a href="mailto:info@epiclounge.co.kr">info@epiclounge.co.kr</a></p>
        </div>
      </div>
      <div>
        <h4>Unreal Fest Seoul</h4>
        <ul class="rvf-links">
          <li><a href="../index.php#overview">소개</a></li>
          <li><a href="../index.php#agenda">아젠다</a></li>
          <li><a href="../index.php#venue">행사장 안내</a></li>
          <li><a href="../index.php#faq">FAQ</a></li>
        </ul>
      </div>
      <div>
        <h4>Epic Lounge</h4>
        <ul class="rvf-links">
          <li><a href="https://epiclounge.co.kr" target="_blank" rel="noopener noreferrer">새소식</a></li>
          <li><a href="https://epiclounge.co.kr" target="_blank" rel="noopener noreferrer">이벤트</a></li>
          <li><a href="https://epiclounge.co.kr" target="_blank" rel="noopener noreferrer">리소스</a></li>
        </ul>
      </div>
    </div>
    <div class="rvf-bottom">
      <p style="margin:0">© 2026 Epic Games, Inc. All Rights Reserved. Unreal 및 그 로고의 저작권은 Epic Games, Inc. 에 있으며, 이는 미국 및 그 외 국가에 모두 해당됩니다.</p>
      <div class="lg">
        <a href="https://epiclounge.co.kr/v3/contents/v4/ode.php" target="_blank" rel="noopener noreferrer">이용약관</a>
        <a href="https://epiclounge.co.kr/v3/contents/v4/personal.php" target="_blank" rel="noopener noreferrer">개인정보처리방침</a>
        <a href="https://epiclounge.co.kr/v3/contents/v4/cookie.php" target="_blank" rel="noopener noreferrer">쿠키 정책</a>
      </div>
    </div>
  </div>
</footer>
</body></html>
