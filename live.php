<?php
/* Unreal Fest Seoul 2026 — 온라인 라이브 시청 (live.php)
 * 이메일 등록확인 게이트(등록 완료자) → Day1/Day2 · 트랙 4채널 YouTube 라이브 시청. 설정=cb_unreal_2026_config(라이브 설정 admin).
 * 관리자(mb_level>=10)는 게이트 우회. noindex. PHP 7.0 호환.
 */
include_once "../common.php";
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

// [2026-08-13] config 전체를 요청당 1회만 조회 후 정적 캐시 → 매 로드 개별 SELECT 5~7회를 1회로 감축(동접 대비).
function lv_get($k){
    static $C = null;
    if ($C === null) {
        $C = array();
        $r = @sql_query("SELECT cfg_key, cfg_val FROM cb_unreal_2026_config");
        if ($r) { while ($x = $r->fetch_assoc()) { $C[$x['cfg_key']] = $x['cfg_val']; } }
    }
    return isset($C[$k]) ? $C[$k] : '';
}
// 라이브 접속 로그 upsert(이메일 기준). 실패해도 로그인 흐름에 영향 없도록 @ 처리.
function ufs_live_log($row, $day, $trk){
    @sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_event2_apply_live (la_no INT NOT NULL AUTO_INCREMENT, apply_no INT NOT NULL DEFAULT 0, apply_user_email VARCHAR(190) NOT NULL DEFAULT '', apply_user_name VARCHAR(100) NOT NULL DEFAULT '', apply_user_phone VARCHAR(30) NOT NULL DEFAULT '', la_day CHAR(1) NOT NULL DEFAULT '', la_trk CHAR(1) NOT NULL DEFAULT '', la_free CHAR(1) NOT NULL DEFAULT '', first_at DATETIME DEFAULT NULL, last_at DATETIME DEFAULT NULL, hits INT NOT NULL DEFAULT 0, la_ip VARCHAR(45) NOT NULL DEFAULT '', d1_at DATETIME DEFAULT NULL, d2_at DATETIME DEFAULT NULL, win_yn CHAR(1) NOT NULL DEFAULT 'N', win_at DATETIME DEFAULT NULL, PRIMARY KEY (la_no), UNIQUE KEY uq_email (apply_user_email)) DEFAULT CHARSET=utf8");
    $ano  = (int)(isset($row['apply_no'])?$row['apply_no']:0);
    $em   = sql_real_escape_string($row['apply_user_email']);
    $nm   = sql_real_escape_string($row['apply_user_name']);
    $ph   = sql_real_escape_string($row['apply_user_phone']);
    $free = sql_real_escape_string(isset($row['free_yn'])?$row['free_yn']:'');
    $d    = sql_real_escape_string($day);
    $t    = sql_real_escape_string($trk);
    // Cloudflare 뒤 → 실제 방문자 IP 우선(CF-Connecting-IP → X-Forwarded-For 첫 값 → REMOTE_ADDR)
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) { $__ip = $_SERVER['HTTP_CF_CONNECTING_IP']; }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { $__xf = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']); $__ip = trim($__xf[0]); }
    else { $__ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:''; }
    $ip   = sql_real_escape_string(substr($__ip,0,45));
    $now  = date('Y-m-d H:i:s');
    // Day별 최초접속 스탬프(양일 접속자 추첨용). day가 1/2일 때만 해당 컬럼 세팅.
    $dcol = ($d==='2') ? 'd2_at' : (($d==='1') ? 'd1_at' : '');
    $ex = @sql_fetch("SELECT la_no FROM cb_unreal_2026_event2_apply_live WHERE apply_user_email='$em'");
    if ($ex) {
        $setd = ($dcol!=='') ? ", $dcol = IF($dcol IS NULL, '$now', $dcol)" : '';
        @sql_query("UPDATE cb_unreal_2026_event2_apply_live SET apply_no=$ano, apply_user_name='$nm', apply_user_phone='$ph', la_free='$free', la_day='$d', la_trk='$t', la_ip='$ip', last_at='$now', hits=hits+1$setd WHERE la_no=".(int)$ex['la_no']);
    } else {
        $d1 = ($d==='1') ? "'$now'" : 'NULL';
        $d2 = ($d==='2') ? "'$now'" : 'NULL';
        @sql_query("INSERT INTO cb_unreal_2026_event2_apply_live (apply_no, apply_user_email, apply_user_name, apply_user_phone, la_free, la_day, la_trk, la_ip, first_at, last_at, hits, d1_at, d2_at) VALUES ($ano, '$em', '$nm', '$ph', '$free', '$d', '$t', '$ip', '$now', '$now', 1, $d1, $d2)");
    }
}
// 라이브 활성 = 수동 토글 ON 또는 예약 기간(live_start~end) 내 (서버 시각 기준)
$__la_manual = (lv_get('live_active') === '1');
$__ls = lv_get('live_start'); $__le = lv_get('live_end'); $__lnow = date('Y-m-d H:i');
$live_active = $__la_manual || ($__ls !== '' && $__le !== '' && $__lnow >= $__ls && $__lnow <= $__le);
$live_notice = lv_get('live_notice');
$live_ended = (lv_get('live_ended') === '1');   // 관리자 토글: 온라인 라이브 종료(영상·채팅 내리고 종료 안내 표시)

// 관리자 여부(공개측 동일 세션)
$is_adm = (isset($member['mb_id']) && $member['mb_id']!=='' && (
    ((int)(isset($member['mb_level'])?$member['mb_level']:0) >= 10) ||
    (isset($config['cf_admin']) && $member['mb_id']===$config['cf_admin'])
));

// ── 라이브 종료(관리자 토글) = 전원 로그아웃 + 재입장 차단. 관리자는 예외(모니터링 유지) ──
// 비관리자 라이브세션 즉시 해제 → 다음 요청/새로고침 시 게이트 대신 종료화면. 게이트 로그인도 차단(아래).
if ($live_ended && !$is_adm) {
    unset($_SESSION['ufs_live_ok'], $_SESSION['ufs_live_name'], $_SESSION['ufs_live_phone'], $_SESSION['ufs_live_email']);
}

// ── 라이선스 문의 접수(AJAX, JSON 반환) ──
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['inq_action']) && $_POST['inq_action']==='submit') {
    header('Content-Type: application/json; charset=utf-8');
    // 신원은 체크인(세션) 정보를 우선 사용 — 클라이언트 위변조 방지. 세션 없으면(관리자 등) POST 사용.
    if (!empty($_SESSION['ufs_live_ok']) && !empty($_SESSION['ufs_live_email'])) {
        $iqn = trim($_SESSION['ufs_live_name']);
        $iqe = trim($_SESSION['ufs_live_email']);
    } else {
        $iqn = trim(isset($_POST['inq_name'])?$_POST['inq_name']:'');
        $iqe = trim(isset($_POST['inq_email'])?$_POST['inq_email']:'');
    }
    $iqc = trim(isset($_POST['inq_content'])?$_POST['inq_content']:'');
    $iqagree = (isset($_POST['inq_agree']) && $_POST['inq_agree']==='1');
    if ($iqn==='' || $iqe==='' || $iqc==='' || !filter_var($iqe, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('ok'=>0,'msg'=>'이름·이메일·내용을 올바르게 입력해 주세요.')); exit;
    }
    if (!$iqagree) {
        echo json_encode(array('ok'=>0,'msg'=>'개인정보 수집·이용에 동의해 주세요.')); exit;
    }
    if (function_exists('mb_strlen') && mb_strlen($iqc,'UTF-8') > 5000) $iqc = mb_substr($iqc,0,5000,'UTF-8');
    if (function_exists('mb_strlen') && mb_strlen($iqn,'UTF-8') > 100) $iqn = mb_substr($iqn,0,100,'UTF-8');
    @sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_live_inquiry (iq_no INT NOT NULL AUTO_INCREMENT, iq_name VARCHAR(100) NOT NULL DEFAULT '', iq_email VARCHAR(190) NOT NULL DEFAULT '', iq_content TEXT, iq_status CHAR(1) NOT NULL DEFAULT 'N', iq_ip VARCHAR(45) NOT NULL DEFAULT '', created_at DATETIME DEFAULT NULL, PRIMARY KEY (iq_no)) DEFAULT CHARSET=utf8");
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) { $iqip = $_SERVER['HTTP_CF_CONNECTING_IP']; }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { $__xf=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']); $iqip=trim($__xf[0]); }
    else { $iqip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:''; }
    $ok = @sql_query("INSERT INTO cb_unreal_2026_live_inquiry (iq_name, iq_email, iq_content, iq_ip, created_at) VALUES ('".sql_real_escape_string($iqn)."', '".sql_real_escape_string($iqe)."', '".sql_real_escape_string($iqc)."', '".sql_real_escape_string(substr($iqip,0,45))."', '".date('Y-m-d H:i:s')."')");
    echo json_encode(array('ok'=>$ok?1:0, 'msg'=>$ok?'':'접수 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.')); exit;
}

// ── 이메일 게이트 ──
if (isset($_GET['logout'])) { unset($_SESSION['ufs_live_ok'], $_SESSION['ufs_live_name']); header('Location: live.php'); exit; }
$gate_err = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['live_email']) && !($live_ended && !$is_adm)) {
    $em = trim($_POST['live_email']);
    $phd = preg_replace('/[^0-9]/', '', isset($_POST['live_phone']) ? $_POST['live_phone'] : '');
    if ($em === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) { $gate_err = '올바른 이메일을 입력해 주세요.'; }
    else if (strlen($phd) < 8) { $gate_err = '전화번호를 정확히 입력해 주세요.'; }
    else {
        // 이메일 + 전화번호(뒷 8자리·앞자리0 보정) 둘 다 일치하는 등록 확인
        $row = sql_fetch("SELECT apply_no, apply_user_name, apply_user_phone, apply_user_email, free_yn FROM cb_unreal_2026_event2_apply WHERE apply_user_email='".sql_real_escape_string($em)."' AND apply_user_phone LIKE '%".sql_real_escape_string(substr($phd,-8))."%' AND apply_pay_status<>0 AND apply_temp_yn='N' ORDER BY apply_no DESC LIMIT 1");
        if ($row) {
            $_SESSION['ufs_live_ok']=1; $_SESSION['ufs_live_name']=$row['apply_user_name']; $_SESSION['ufs_live_phone']=$row['apply_user_phone']; $_SESSION['ufs_live_email']=$row['apply_user_email'];
            // 게이트에서 선택한 채널로 바로 입장(단일 채널만 로드 → 트래픽 절감). Day는 접속일 기준.
            $__dd = (date('Y-m-d')==='2026-08-21') ? '2' : '1';
            $__st = (isset($_POST['live_trk']) && in_array($_POST['live_trk'], array('1','2','3','4'), true)) ? $_POST['live_trk'] : '';
            // 접속 로그 기록(관리자 2026_live_list.php에서 조회). 이메일 기준 upsert — 최초/최종접속·횟수·선택채널·유형.
            ufs_live_log($row, $__dd, $__st);
            header('Location: live.php?d='.$__dd.($__st!=='' ? '&t='.$__st : '')); exit;
        }
        $gate_err = '등록 정보를 찾을 수 없습니다. 등록에 사용하신 이메일과 전화번호를 확인해 주세요.';
    }
}
$verified = (!empty($_SESSION['ufs_live_ok']) || $is_adm);
$viewer = !empty($_SESSION['ufs_live_name']) ? $_SESSION['ufs_live_name'] : ($is_adm ? '관리자' : '');

// ── 채널 정의 ──
$DAYS = array('1'=>'8월 20일', '2'=>'8월 21일');
$DAYSUB = array('1'=>'Day 1 · 목', '2'=>'Day 2 · 금');
$TRK  = array(
  '1'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'공통'),
  '2'=>array('1'=>'게임: 프로그래밍','2'=>'게임: 아트','3'=>'미디어 & 엔터','4'=>'제조 및 시뮬레이션'),
);
$TRKCOL = array('1'=>'#307FE2','2'=>'#FF8F1C','3'=>'#FA4616','4'=>'#DD0AB2');
// 기본 Day = 오늘(8/20→1, 8/21→2), 그 외 1
$today = date('Y-m-d');
$defDay = ($today==='2026-08-21') ? '2' : '1';
$day = (isset($_GET['d']) && isset($DAYS[$_GET['d']])) ? $_GET['d'] : $defDay;
$trk_explicit = (isset($_GET['t']) && isset($TRK[$day][$_GET['t']]));
$trk = $trk_explicit ? $_GET['t'] : '1';
$ytid = lv_get('live_yt_d'.$day.'t'.$trk);
// 기본 진입(트랙 미지정)인데 해당 채널이 비어 있고 라이브 활성이면 → 스트림 있는 첫 채널로 자동 이동
if (!$trk_explicit && $live_active && $ytid === '') {
    $__order = array();
    foreach (array_keys($TRK[$day]) as $tk) $__order[] = array($day, $tk);
    $__od2 = ($day==='1') ? '2' : '1';
    foreach (array_keys($TRK[$__od2]) as $tk) $__order[] = array($__od2, $tk);
    foreach ($__order as $o) { $yy = lv_get('live_yt_d'.$o[0].'t'.$o[1]); if ($yy !== '') { $day=$o[0]; $trk=$o[1]; $ytid=$yy; break; } }
}
$curtrk = $TRK[$day][$trk];
$curcol = $TRKCOL[$trk];
$cur_label = $DAYS[$day].' · '.$curtrk;
// 이전/다음 트랙(현재 Day 내 순환)
$__tk = array_keys($TRK[$day]); $__pos = array_search($trk, $__tk, true);
$prev_t = $__tk[($__pos - 1 + count($__tk)) % count($__tk)];
$next_t = $__tk[($__pos + 1) % count($__tk)];

// ── CGChat 채팅 URL — 트랙별 전용 서버(griff-<트랙>.cgchat.io), 방 no는 day+track별 고유(2025 방식 동일) ──
//   서버: t1→griff-1(1000명)·t2→griff-2(600)·t3→griff-3(600)·t4→griff-4(500). day1/day2 같은 트랙은 같은 서버, 방 no만 다름.
$chIndex = array('d1t1'=>1,'d1t2'=>2,'d1t3'=>3,'d1t4'=>4,'d2t1'=>5,'d2t2'=>6,'d2t3'=>7,'d2t4'=>8);
$__ci = isset($chIndex['d'.$day.'t'.$trk]) ? $chIndex['d'.$day.'t'.$trk] : 1;
$__trk = (isset($TRK[$day][$trk])) ? $trk : '1'; // 안전: 1~4
$chat_base = 'https://griff-'.$__trk.'.cgchat.io/chat?sk=griff&no=griffroom2026_'.$__ci;
$__ui   = rawurlencode('{"btnPopupChat":"0","btnEmoji":"1"}');
$__view = rawurlencode('{"sendBtn":"333333","bgColor":"333333","msgViewType":"0","chatOneLine":"0","isChatHistory":"1","sysMsgColor":"7e7e7e","chatTime":"1"}');
$__my   = rawurlencode('{"nkColor":"7e7e7e","msgColor":"000000"}');
$__ctrl = rawurlencode('{"banWord":"***나쁜말***","autoLink":"1","maxLength":"0","msgInterval":"0","mobileFocus":"0"}');
$chat_src = $chat_base.'&ui='.$__ui.'&view='.$__view.'&my='.$__my.'&control='.$__ctrl.'&tg=https://unrealsummit16.cafe24.com/og/tg.svg';
if ($is_adm) {
    $chat_src .= '&lv=3&id='.rawurlencode('언리얼페스트').'&nk=admin';
} else {
    $__vemail = !empty($_SESSION['ufs_live_email']) ? $_SESSION['ufs_live_email'] : '';
    $__vphone = preg_replace('/[^0-9]/', '', !empty($_SESSION['ufs_live_phone']) ? $_SESSION['ufs_live_phone'] : '');
    $__nk = (function_exists('mb_substr') ? mb_substr($viewer,0,12,'UTF-8') : substr($viewer,0,12)).'('.substr($__vphone,-4).')';
    $chat_src .= '&lv=2&id='.rawurlencode($__vemail).'&nk='.rawurlencode($__nk);
}
$has_stream = ($live_active && $ytid !== '');
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>온라인 라이브 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--panel2:#0b0b0e;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--text:#eaeaef;--muted:#8b8b96;--accent:<?= $curcol ?>}
*{box-sizing:border-box}
html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;-webkit-font-smoothing:antialiased;word-break:keep-all;overflow-wrap:break-word;
  background-image:radial-gradient(900px 500px at 78% -8%, rgba(0,193,213,.10), transparent 60%),radial-gradient(700px 400px at 0% 0%, rgba(48,127,226,.06), transparent 55%)}
a{color:inherit;text-decoration:none}
.blink{animation:blink 1.25s steps(1,end) infinite}@keyframes blink{50%{opacity:.28}}
.dot{display:inline-block;width:8px;height:8px;border-radius:50%;vertical-align:middle}

/* top bar */
.lv-top{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:13px clamp(16px,4vw,40px);background:rgba(8,8,10,.72);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
.lv-logo{display:flex;align-items:center;gap:14px;min-width:0}
.lv-toplogo{height:24px;width:auto;display:block}
.lv-user{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--muted);white-space:nowrap}
.lv-user b{color:var(--text);font-weight:700}
.lv-out{padding:6px 13px;border:1px solid var(--line2);border-radius:999px;font-size:12px;color:var(--muted);transition:.15s}
.lv-out:hover{border-color:var(--teal);color:var(--teal)}

.lv-wrap{max-width:1680px;margin:0 auto;padding:clamp(16px,2.4vw,30px) clamp(14px,4vw,40px) 56px}

/* channel bar */
.lv-chan{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.lv-tracks{display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:1;min-width:260px}
.lv-tk{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border:1px solid var(--line);border-radius:11px;background:var(--panel);font-size:13px;font-weight:700;color:var(--muted);transition:.15s}
.lv-tk .dot{width:9px;height:9px;box-shadow:0 0 0 3px rgba(255,255,255,.03)}
.lv-tk:hover{border-color:var(--line2);color:var(--text)}
.lv-tk.on{color:#fff;background:transparent;box-shadow:none}
.lv-nowb{margin-left:3px;padding:2px 8px;font-size:10px;font-weight:800;letter-spacing:.02em;background:var(--tkc);color:#fff;line-height:1.55}
.lv-nav{margin-left:auto;display:flex;gap:6px}
.lv-nav a{width:38px;height:38px;display:grid;place-items:center;border:1px solid var(--line);border-radius:10px;background:var(--panel);color:var(--muted);transition:.15s}
.lv-nav a:hover{border-color:var(--teal);color:var(--teal)}

/* unified player */
.lv-player{display:flex;align-items:stretch;border:1px solid var(--line);border-radius:16px;overflow:hidden;background:#000;box-shadow:0 40px 90px -30px rgba(0,0,0,.75),0 0 0 1px rgba(0,193,213,.05)}
.lv-video{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;background:#000}
.lv-frame{position:relative;width:100%;padding-top:56.25%;background:radial-gradient(120% 120% at 50% 30%, #131318, #000)}
.lv-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.lv-frame:fullscreen{padding-top:0;height:100%}
.lv-frame:-webkit-full-screen{padding-top:0;height:100%}
.lv-hold{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;text-align:center;padding:24px}
.lv-hold .ic{width:64px;height:64px;border-radius:50%;display:grid;place-items:center;border:1px solid var(--line2);background:#0c0c10;color:var(--accent)}
.lv-hold h3{margin:0;font-size:20px;font-weight:900;color:#fff;letter-spacing:-.01em}
.lv-hold p{margin:0;font-size:13px;color:var(--muted);line-height:1.7}
/* control bar */
.lv-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 16px;background:rgba(12,12,16,.92);border-top:1px solid var(--line)}
.lv-now{display:flex;align-items:center;gap:10px;min-width:0}
.lv-now .tag{display:inline-flex;align-items:center;gap:6px;padding:4px 9px;border-radius:6px;font-size:10px;font-weight:900;letter-spacing:.1em;background:rgba(239,68,68,.16);color:#ff6b6b}
.lv-now .lab{font-size:14px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lv-now .lab i{font-style:normal;color:var(--accent)}
.lv-ctrl{display:flex;gap:8px;flex-shrink:0}
.lv-btn{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border:1px solid var(--line2);border-radius:9px;background:#141418;color:#cfcfd6;font-size:12px;font-weight:700;cursor:pointer;transition:.15s}
.lv-btn:hover{border-color:var(--teal);color:#fff}
.lv-btn svg{width:15px;height:15px}
/* chat */
.lv-chat{flex:0 0 380px;display:flex;flex-direction:column;border-left:1px solid var(--line);background:var(--panel2)}
.lv-chat-h{display:flex;align-items:center;gap:8px;padding:13px 15px;font-size:13px;font-weight:800;color:#fff;border-bottom:1px solid var(--line)}
.lv-chat-h .dot{width:7px;height:7px;background:#ff6b6b}
.lv-chat iframe{flex:1;width:100%;border:0;min-height:0;background:#111}
.hidechat .lv-chat{display:none}

/* footer */
.lv-foot{margin-top:28px;padding-top:22px;border-top:1px solid var(--line);display:flex;justify-content:flex-start;align-items:center}
.lv-foot img{height:22px;width:auto;opacity:.6;transition:opacity .15s}
.lv-foot a:hover img{opacity:1}
.lv-notice{position:relative;margin:0 0 16px;font-size:13px;color:#cfd0d6;background:linear-gradient(180deg,rgba(0,193,213,.06),transparent);border:1px solid rgba(0,193,213,.22);border-radius:12px;padding:12px 42px 12px 16px;display:flex;gap:10px;align-items:flex-start}
.lv-notice .dot{width:7px;height:7px;background:var(--teal);margin-top:7px;flex:none}
.lv-nx{position:absolute;top:6px;right:8px;width:26px;height:26px;display:grid;place-items:center;background:transparent;border:0;color:var(--muted);font-size:20px;line-height:1;cursor:pointer;border-radius:6px;transition:.15s}
.lv-nx:hover{color:#fff;background:rgba(255,255,255,.06)}

/* gate */
.lv-gate{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:24px}
.lv-gcard{width:100%;max-width:440px;background:linear-gradient(180deg,rgba(255,255,255,.03),transparent),var(--panel);border:1px solid var(--line);border-radius:20px;padding:38px 34px;box-shadow:0 40px 90px -40px rgba(0,0,0,.8)}
.lv-glogo{width:200px;max-width:64%;height:auto;display:block;margin-bottom:22px}
.lv-gcard h1{font-size:23px;font-weight:900;color:#fff;margin:0 0 8px;letter-spacing:-.01em}
.lv-gcard p{font-size:13.5px;color:var(--muted);margin:0 0 20px;line-height:1.7}
.lv-gcard input{width:100%;padding:14px 16px;background:#08080b;border:1px solid var(--line2);border-radius:11px;color:#fff;font-size:15px;margin-bottom:12px;transition:.15s}
.lv-gcard input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.lv-gcard button{width:100%;padding:15px;background:var(--teal);color:#00232a;border:0;border-radius:11px;font-size:15px;font-weight:900;cursor:pointer;transition:.15s;letter-spacing:.01em}
.lv-gcard button:hover{filter:brightness(1.06)}
.lv-err{color:#ff8a8a;font-size:13px;margin-bottom:12px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);padding:9px 12px;border-radius:9px}
/* gate: 채널 선택 */
.lv-gtrk{margin:2px 0 18px}
.lv-gtrk-lb{font-size:12px;font-weight:800;color:var(--muted);margin:0 0 9px;letter-spacing:.02em}
.lv-gtrk-opts{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.lv-gtrk-opts label{display:flex;align-items:center;gap:9px;padding:12px 13px;border:1px solid var(--line2);background:#08080b;color:var(--muted);font-size:13px;font-weight:700;cursor:pointer;transition:.15s;user-select:none}
.lv-gtrk-opts label:hover{border-color:var(--line2);color:#cfd0d6}
.lv-gtrk-opts input{position:absolute;opacity:0;width:0;height:0}
.lv-gtrk-opts label .dot{width:10px;height:10px;flex:none;box-shadow:0 0 0 3px rgba(255,255,255,.03)}
.lv-gtrk-opts label:has(input:checked){color:#fff;border-color:var(--tkc,#00C1D5);box-shadow:0 0 0 2px rgba(0,193,213,.10) inset;background:linear-gradient(180deg,rgba(255,255,255,.04),transparent),#0b0b10}

/* 라이선스 문의 — 플로팅 버튼 + 모달 */
.lv-inqbtn{position:fixed;right:22px;bottom:22px;z-index:60;display:inline-flex;align-items:center;gap:9px;padding:13px 20px;
  background:var(--teal);color:#00232a;border:0;border-radius:999px;font-size:14px;font-weight:900;cursor:pointer;letter-spacing:.01em;
  box-shadow:0 14px 34px -10px rgba(0,193,213,.55),0 4px 12px rgba(0,0,0,.4);transition:.15s;transform:scale(.7);transform-origin:100% 100%}
.lv-inqbtn:hover{filter:brightness(1.06);transform:scale(.7) translateY(-2px)}
.lv-inqbtn svg{width:17px;height:17px}
/* 문의 폼 — 자동입력 잠금·힌트·동의 */
.lv-inqmodal input[readonly]{background:#0b0b0f;color:#b9b9c2;cursor:default}
.lv-inqhint{margin:-6px 0 13px;font-size:11.5px;color:#6b6b76}
.lv-inqagree{display:flex;align-items:flex-start;gap:8px;margin:2px 0 4px;font-size:12.5px;color:var(--muted);cursor:pointer;font-weight:600}
.lv-inqmodal .lv-inqagree input{width:16px;height:16px;min-width:16px;margin:1px 0 0;padding:0;background:transparent;border:0;accent-color:var(--teal);flex:none;cursor:pointer}
.lv-inqagree b{color:var(--teal);font-weight:800}
.lv-inqagmore{background:none;border:0;color:var(--teal);font-size:12px;text-decoration:underline;cursor:pointer;padding:0}
.lv-inqagbox{display:none;margin:2px 0 12px;padding:10px 12px;background:#08080b;border:1px solid var(--line);font-size:11.5px;color:#9a9aa4;line-height:1.65}
.lv-inqmask{position:fixed;inset:0;z-index:70;display:none;align-items:center;justify-content:center;padding:22px;background:rgba(4,4,6,.72);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
.lv-inqmask.on{display:flex}
.lv-inqmodal{width:100%;max-width:460px;background:linear-gradient(180deg,rgba(255,255,255,.03),transparent),var(--panel);border:1px solid var(--line2);border-radius:16px;padding:26px 26px 24px;box-shadow:0 50px 100px -30px rgba(0,0,0,.85);position:relative}
.lv-inqmodal h2{margin:0 0 6px;font-size:20px;font-weight:900;color:#fff;letter-spacing:-.01em}
.lv-inqmodal p.sub{margin:0 0 18px;font-size:13px;color:var(--muted);line-height:1.65}
.lv-inqmodal label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin:0 0 6px}
.lv-inqmodal input,.lv-inqmodal textarea{width:100%;padding:12px 14px;background:#08080b;border:1px solid var(--line2);border-radius:10px;color:#fff;font-size:14px;margin:0 0 13px;font-family:inherit;transition:.15s}
.lv-inqmodal textarea{min-height:120px;resize:vertical;line-height:1.6}
.lv-inqmodal input:focus,.lv-inqmodal textarea:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,193,213,.12)}
.lv-inqmodal .go{width:100%;padding:14px;background:var(--teal);color:#00232a;border:0;border-radius:10px;font-size:15px;font-weight:900;cursor:pointer;transition:.15s}
.lv-inqmodal .go:hover{filter:brightness(1.06)}
.lv-inqmodal .go:disabled{opacity:.6;cursor:default}
.lv-inqx{position:absolute;top:12px;right:14px;width:30px;height:30px;display:grid;place-items:center;background:transparent;border:0;color:var(--muted);font-size:24px;line-height:1;cursor:pointer;border-radius:8px;transition:.15s}
.lv-inqx:hover{color:#fff;background:rgba(255,255,255,.06)}
.lv-inqmsg{font-size:13px;margin:0 0 12px;padding:9px 12px;border-radius:9px;display:none}
.lv-inqmsg.err{display:block;color:#ff8a8a;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)}
.lv-inqmsg.ok{display:block;color:#7fe0b0;background:rgba(0,193,213,.08);border:1px solid rgba(0,193,213,.28)}
.lv-inqdone{text-align:center;padding:14px 0 4px}
.lv-inqdone .ic{width:60px;height:60px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;background:rgba(0,193,213,.12);color:var(--teal)}
.lv-inqdone h2{margin:0 0 8px}
.lv-inqdone p{font-size:13.5px;color:var(--muted);line-height:1.7;margin:0 0 18px}
@media (max-width:600px){.lv-inqbtn{right:14px;bottom:14px;padding:12px 17px;font-size:13px} .lv-inqbtn .txt{display:none}}
@media (max-width:900px){
  .lv-player{flex-direction:column}
  .lv-chat{flex-basis:auto;border-left:0;border-top:1px solid var(--line);height:52vh}
  .lv-nav{display:none}
}
/* 영상·버튼·폼 라운드 제거(각진 스타일). 원형 표시점(dot/ic)은 유지 */
.lv-player,.lv-frame,.lv-chat,.lv-chat iframe,.lv-btn,.lv-tk,.lv-nav a,.lv-out,.lv-notice,.lv-nowb,.lv-gcard,.lv-gcard input,.lv-gcard button,.lv-err,.lv-inqbtn,.lv-inqmodal,.lv-inqmodal input,.lv-inqmodal textarea,.lv-inqmodal .go,.lv-inqmsg{border-radius:0}
</style>
</head>
<body class="<?= $has_stream ? '' : '' ?>">

<header class="lv-top">
  <div class="lv-logo">
    <a href="index.php" aria-label="Unreal Fest Seoul 2026"><img class="lv-toplogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
  </div>
  <?php if ($verified && (!$live_ended || $is_adm)): ?>
  <div class="lv-user"><?php if ($live_ended && $is_adm): ?><span style="color:#ff8a8a;font-weight:700;font-size:12px">🛑 종료모드(관리자 미리보기)</span><?php endif; ?><span><b><?= e($viewer) ?></b>님</span><a class="lv-out" href="live.php?logout=1">로그아웃</a></div>
  <?php endif; ?>
</header>

<?php if ($live_ended && !$is_adm): ?>
  <div class="lv-gate">
    <div class="lv-gcard" style="text-align:center">
      <img class="lv-glogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026" style="margin:0 auto 24px">
      <h1>온라인 라이브가 종료되었습니다</h1>
      <p>다시보기는 곧 오픈될 예정입니다.<br>준비되는 대로 안내드리겠습니다.</p>
      <a href="index.php" style="display:inline-block;margin-top:6px;padding:14px 28px;background:var(--teal);color:#00232a;font-size:15px;font-weight:900;letter-spacing:.01em">홈으로</a>
    </div>
  </div>
<?php elseif (!$verified): ?>
  <div class="lv-gate">
    <div class="lv-gcard">
      <img class="lv-glogo" src="white_logo.svg" alt="Unreal Fest Seoul 2026">
      <h1>온라인 라이브 시청</h1>
      <p>참가 등록에 사용하신 <b style="color:#cfd0d6">이메일과 전화번호</b>를 입력하시면 실시간 세션 시청 화면으로 입장합니다.</p>
      <?php if ($gate_err): ?><div class="lv-err"><?= e($gate_err) ?></div><?php endif; ?>
      <form method="post">
        <input type="email" name="live_email" autocapitalize="off" autocomplete="off" placeholder="이메일 입력" required autofocus>
        <input type="text" name="live_phone" inputmode="numeric" autocomplete="off" placeholder="전화번호 입력" required>
        <div class="lv-gtrk">
          <p class="lv-gtrk-lb">시청할 채널 선택 · <?= e($DAYS[$defDay]) ?></p>
          <div class="lv-gtrk-opts">
            <?php $__i=0; foreach ($TRK[$defDay] as $tk=>$tl): $c=$TRKCOL[$tk]; ?>
              <label style="--tkc:<?= $c ?>"><input type="radio" name="live_trk" value="<?= $tk ?>"<?= $__i===0?' checked':'' ?>><span class="dot" style="background:<?= $c ?>"></span><?= e($tl) ?></label>
            <?php $__i++; endforeach; ?>
          </div>
        </div>
        <button type="submit">입장하기 →</button>
      </form>
      <p style="text-align:center;margin:14px 0 0"><a href="find-account.php" style="font-size:13px;color:var(--teal);font-weight:700;border-bottom:1px solid rgba(0,193,213,.4);padding-bottom:1px">이메일·전화번호가 기억나지 않으세요? 등록정보 찾기</a></p>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">등록 확인이 안 되면 사무국으로 문의해 주세요.<br>info@epiclounge.co.kr</p>
    </div>
  </div>
<?php else: ?>
  <div class="lv-wrap" id="lvWrap">

    <!-- 채널 바 -->
    <div class="lv-chan">
      <div class="lv-tracks">
        <?php foreach ($TRK[$day] as $tk=>$tl): $c=$TRKCOL[$tk]; ?>
          <a class="lv-tk <?= (string)$trk===(string)$tk?'on':'' ?>" style="--tkc:<?= $c ?>" href="live.php?d=<?= $day ?>&t=<?= $tk ?>"><span class="dot" style="background:<?= $c ?>"></span><?= e($tl) ?><?php if ((string)$trk===(string)$tk): ?><span class="lv-nowb">시청 중</span><?php endif; ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($live_notice !== ''): ?><div class="lv-notice" id="lvNotice" data-nk="<?= e(substr(md5($live_notice),0,10)) ?>"><span class="dot"></span><span><?= e($live_notice) ?></span><button type="button" class="lv-nx" onclick="ufsCloseNotice()" aria-label="공지 닫기">&times;</button></div><?php endif; ?>

    <!-- 플레이어 -->
    <div class="lv-player">
      <div class="lv-video">
        <div class="lv-frame" id="lvFrame">
          <?php if ($has_stream): ?>
            <iframe src="https://www.youtube.com/embed/<?= e($ytid) ?>?rel=0&autoplay=1&modestbranding=1" title="<?= e($cur_label) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe>
          <?php else: ?>
            <div class="lv-hold">
              <div class="ic"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
              <h3>곧 시작합니다</h3>
              <p><b style="color:<?= $curcol ?>"><?= e($curtrk) ?></b> 채널의 라이브가 아직 시작되지 않았습니다.<br>세션 시간에 맞춰 다시 접속하거나 다른 채널을 확인해 주세요.</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="lv-bar">
          <div class="lv-now">
            <span class="lab"><?= e($DAYS[$day]) ?> · <i><?= e($curtrk) ?></i></span>
          </div>
          <div class="lv-ctrl">
            <?php if ($has_stream): ?>
            <button type="button" class="lv-btn" id="chBtn" onclick="ufsToggleChat()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span id="chTxt">채팅 숨기기</span></button>
            <?php endif; ?>
            <button type="button" class="lv-btn" onclick="ufsFS()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>전체화면</button>
          </div>
        </div>
      </div>
      <?php if ($has_stream): ?>
      <aside class="lv-chat" id="lvChat">
        <div class="lv-chat-h">실시간 채팅</div>
        <iframe src="<?= e($chat_src) ?>" title="라이브 채팅" allow="clipboard-write"></iframe>
      </aside>
      <?php endif; ?>
    </div>

    <footer class="lv-foot">
      <a href="https://epiclounge.co.kr" target="_blank" rel="noopener"><img src="https://epiclounge.co.kr/resource/images/common/logo_dark.svg" alt="EPIC LOUNGE"></a>
    </footer>
  </div>

  <!-- 라이선스 문의 플로팅 버튼 -->
  <button type="button" class="lv-inqbtn" onclick="ufsInqOpen()" aria-label="라이선스 문의하기">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><span class="txt">라이선스 문의하기</span>
  </button>

  <!-- 라이선스 문의 모달 -->
  <div class="lv-inqmask" id="lvInqMask" onclick="if(event.target===this)ufsInqClose()">
    <div class="lv-inqmodal" role="dialog" aria-modal="true" aria-labelledby="lvInqTitle">
      <button type="button" class="lv-inqx" onclick="ufsInqClose()" aria-label="닫기">&times;</button>
      <div id="lvInqForm">
        <h2 id="lvInqTitle">라이선스 문의</h2>
        <p class="sub">언리얼 엔진 라이선스 관련 문의를 남겨 주시면 담당자가 확인 후 회신드립니다.</p>
        <div class="lv-inqmsg" id="lvInqMsg"></div>
        <?php $__ilock = (!$is_adm && !empty($_SESSION['ufs_live_email'])); ?>
        <label for="lvInqName">이름</label>
        <input type="text" id="lvInqName" maxlength="100" placeholder="이름" value="<?= $verified ? e($viewer) : '' ?>"<?= $__ilock ? ' readonly' : '' ?>>
        <label for="lvInqEmail">이메일</label>
        <input type="email" id="lvInqEmail" autocapitalize="off" placeholder="이메일" value="<?= $__ilock ? e($_SESSION['ufs_live_email']) : '' ?>"<?= $__ilock ? ' readonly' : '' ?>>
        <?php if ($__ilock): ?><p class="lv-inqhint">체크인하신 등록 정보로 자동 입력됩니다.</p><?php endif; ?>
        <label for="lvInqContent">문의 내용</label>
        <textarea id="lvInqContent" maxlength="5000" placeholder="문의하실 내용을 입력해 주세요."></textarea>
        <label class="lv-inqagree"><input type="checkbox" id="lvInqAgree"><span><b>[필수]</b> 개인정보 수집·이용 동의 <button type="button" class="lv-inqagmore" onclick="ufsInqAgree()">자세히</button></span></label>
        <div class="lv-inqagbox" id="lvInqAgBox">수집 항목: 이름, 이메일, 문의 내용 · 수집 목적: 라이선스 문의 응대 및 회신 · 보유 기간: 문의 처리 완료 후 1년 이내 파기. 동의를 거부하실 수 있으나 이 경우 문의 접수가 제한됩니다.</div>
        <button type="button" class="go" id="lvInqGo" onclick="ufsInqSubmit()">문의 보내기</button>
      </div>
      <div class="lv-inqdone" id="lvInqDone" style="display:none">
        <div class="ic"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></div>
        <h2>문의가 접수되었습니다</h2>
        <p>담당자가 확인 후 입력하신 이메일로 회신드리겠습니다.<br>감사합니다.</p>
        <button type="button" class="go" onclick="ufsInqClose()">닫기</button>
      </div>
    </div>
  </div>

  <script>
  function ufsToggleChat(){
    var w=document.getElementById('lvWrap'), t=document.getElementById('chTxt');
    if(!w) return;
    var hid=w.classList.toggle('hidechat');
    if(t) t.textContent = hid ? '채팅 보이기' : '채팅 숨기기';
  }
  function ufsFS(){
    var el=document.getElementById('lvFrame'); if(!el) return;
    var fsEl=document.fullscreenElement||document.webkitFullscreenElement;
    if(fsEl){ (document.exitFullscreen||document.webkitExitFullscreen).call(document); }
    else { (el.requestFullscreen||el.webkitRequestFullscreen).call(el); }
  }
  function ufsCloseNotice(){
    var n=document.getElementById('lvNotice'); if(!n) return;
    n.style.display='none';
    try{ localStorage.setItem('ufsLiveNoticeClosed', n.getAttribute('data-nk')); }catch(e){}
  }
  (function(){
    var n=document.getElementById('lvNotice'); if(!n) return;
    try{ if(localStorage.getItem('ufsLiveNoticeClosed')===n.getAttribute('data-nk')) n.style.display='none'; }catch(e){}
  })();

  // 라이선스 문의 모달
  function ufsInqOpen(){
    var m=document.getElementById('lvInqMask'); if(!m) return;
    document.getElementById('lvInqForm').style.display='';
    document.getElementById('lvInqDone').style.display='none';
    var msg=document.getElementById('lvInqMsg'); msg.className='lv-inqmsg'; msg.textContent='';
    m.classList.add('on');
  }
  function ufsInqClose(){ var m=document.getElementById('lvInqMask'); if(m) m.classList.remove('on'); }
  function ufsInqAgree(){ var b=document.getElementById('lvInqAgBox'); if(b) b.style.display = (b.style.display==='block'?'none':'block'); }
  function ufsInqSubmit(){
    var nm=document.getElementById('lvInqName').value.trim();
    var em=document.getElementById('lvInqEmail').value.trim();
    var ct=document.getElementById('lvInqContent').value.trim();
    var ag=document.getElementById('lvInqAgree').checked;
    var msg=document.getElementById('lvInqMsg');
    var re=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!nm||!em||!ct||!re.test(em)){ msg.className='lv-inqmsg err'; msg.textContent='이름·이메일·내용을 올바르게 입력해 주세요.'; return; }
    if(!ag){ msg.className='lv-inqmsg err'; msg.textContent='개인정보 수집·이용에 동의해 주세요.'; return; }
    var btn=document.getElementById('lvInqGo'); btn.disabled=true; btn.textContent='보내는 중…';
    var fd=new FormData();
    fd.append('inq_action','submit'); fd.append('inq_name',nm); fd.append('inq_email',em); fd.append('inq_content',ct); fd.append('inq_agree','1');
    fetch('live.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(d){
        btn.disabled=false; btn.textContent='문의 보내기';
        if(d&&d.ok){ document.getElementById('lvInqForm').style.display='none'; document.getElementById('lvInqDone').style.display=''; }
        else { msg.className='lv-inqmsg err'; msg.textContent=(d&&d.msg)?d.msg:'접수 중 오류가 발생했습니다.'; }
      })
      .catch(function(){ btn.disabled=false; btn.textContent='문의 보내기'; msg.className='lv-inqmsg err'; msg.textContent='네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.'; });
  }
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') ufsInqClose(); });
  </script>
<?php endif; ?>
</body></html>
