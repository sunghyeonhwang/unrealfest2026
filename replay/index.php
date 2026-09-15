<?php
/* Unreal Fest Seoul 2026 — 세션 다시보기 (replay/index.php)
 *
 * 오프라인 등록자 전용 다시보기. 기획: replay/REPLAY_PLANNING.md
 *  - 체크인 = 등록 이메일 + 연락처(뒤 8자리). ONLINE(온라인 전용) 상품 제외.
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
    unset($_SESSION['ufs_replay_ok'], $_SESSION['ufs_replay_name'], $_SESSION['ufs_replay_no'], $_SESSION['ufs_replay_email'], $_SESSION['ufs_replay_at']);
    header('Location: ./'); exit;
}

$verified = (rv_auth_ok() || $is_adm);
$viewer   = !empty($_SESSION['ufs_replay_name']) ? $_SESSION['ufs_replay_name'] : ($is_adm ? '관리자' : '');
$apply_no = !empty($_SESSION['ufs_replay_no']) ? (int)$_SESSION['ufs_replay_no'] : 0;

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
            // 오프라인 등록 자격: 정상 등록(임시X, 취소X) + 온라인 전용 상품 제외(기획 4.1/4.2)
            $row = sql_fetch("SELECT apply_no, apply_user_name, apply_user_email
                FROM cb_unreal_2026_event2_apply
                WHERE apply_user_email='" . sql_real_escape_string($em) . "'
                  AND apply_user_phone LIKE '%" . sql_real_escape_string(substr($phd, -8)) . "%'
                  AND apply_temp_yn='N' AND apply_pay_status<>0
                  AND apply_product_code<>'ONLINE'
                ORDER BY apply_no DESC LIMIT 1");
            if ($row) {
                if (function_exists('session_regenerate_id')) @session_regenerate_id(true);   // 세션 고정 방지(기획 12.2)
                $_SESSION['ufs_replay_ok']    = 1;
                $_SESSION['ufs_replay_name']  = $row['apply_user_name'];
                $_SESSION['ufs_replay_no']    = (int)$row['apply_no'];
                $_SESSION['ufs_replay_email'] = strtolower($row['apply_user_email']);
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

/* ── 공개 세션 로드 (인증 후에만 — 인증 전 HTML 에 영상·PDF 정보 미포함) ── */
$DAYS = array('1' => 'Day 1 · 8월 20일(목)', '2' => 'Day 2 · 8월 21일(금)');
$sessions = array(); $tracks = array(); $JS = array();
if ($verified && $svc_open) {
    $rs = @sql_query("SELECT a.ag_no, a.ag_day, a.ag_track, a.ag_time, a.ag_title, a.ag_desc,
               a.ag_sp_name, a.ag_sp_role, a.ag_sp_company,
               v.rv_vimeo, v.rv_vimeo_hash, v.rv_thumb, v.rv_video_public,
               v.rv_pdf_url, v.rv_pdf_name, v.rv_pdf_size, v.rv_pdf_public
        FROM cb_unreal_2026_replay_vod v
        JOIN cb_unreal_2026_agenda a ON a.ag_no = v.rv_agno
        WHERE v.rv_show='Y' AND a.ag_is_active='Y'
        ORDER BY a.ag_day ASC, a.ag_sort ASC, a.ag_no ASC");
    if ($rs) { while ($x = sql_fetch_array($rs)) $sessions[] = $x; }

    foreach ($sessions as $s) {
        if ($s['ag_track'] !== '' && !in_array($s['ag_track'], $tracks, true)) $tracks[] = $s['ag_track'];
        $has_video = ($s['rv_video_public'] === 'Y' && $s['rv_vimeo'] !== '');
        $has_pdf   = ($s['rv_pdf_public'] === 'Y' && $s['rv_pdf_url'] !== '');
        $JS[(int)$s['ag_no']] = array(
            'title'   => $s['ag_title'],
            'day'     => (int)$s['ag_day'],
            'time'    => $s['ag_time'],
            'track'   => $s['ag_track'],
            'desc'    => $s['ag_desc'],
            'sp'      => trim($s['ag_sp_name']),
            'sp_role' => trim($s['ag_sp_role']),
            'sp_co'   => trim($s['ag_sp_company']),
            'embed'   => $has_video ? rv_vimeo_embed($s['rv_vimeo'], $s['rv_vimeo_hash']) : '',
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
<title>세션 다시보기 — Unreal Fest Seoul 2026</title>
<style>
:root{--bg:#08080a;--panel:#0e0e12;--line:#1e1e25;--line2:#2a2a33;--teal:#00C1D5;--mint:#00FFC8;--text:#eaeaef;--muted:#8b8b96}
*{box-sizing:border-box}html,body{margin:0}
body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;-webkit-font-smoothing:antialiased;word-break:keep-all;
 background-image:radial-gradient(900px 500px at 78% -8%, rgba(0,193,213,.10), transparent 60%)}
a{color:inherit;text-decoration:none}
button{font-family:inherit}
.rv-top{position:sticky;top:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px clamp(16px,4vw,40px);background:rgba(8,8,10,.72);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
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
.rv-search{flex:1;min-width:180px;max-width:320px;padding:9px 13px;background:var(--panel);border:1px solid var(--line2);color:var(--text);font-size:13px}
.rv-search:focus,.rv-sel:focus{outline:none;border-color:var(--teal)}
/* grid */
.rv-day{font-size:15px;font-weight:800;color:#fff;margin:26px 0 12px;padding-left:11px;border-left:3px solid var(--teal)}
.rv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.rv-card{border:1px solid var(--line);background:var(--panel);cursor:pointer;transition:.15s;overflow:hidden;display:flex;flex-direction:column;text-align:left;padding:0;color:inherit}
.rv-card:hover,.rv-card:focus-visible{border-color:var(--teal);transform:translateY(-2px);outline:none}
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
.rv-minfo{font-size:12.5px;color:var(--teal);font-weight:700;margin:0 0 8px}
.rv-mtitle{font-size:18px;font-weight:800;color:#fff;margin:0 0 10px;line-height:1.45}
.rv-mdesc{font-size:13.5px;color:#c9c9d2;line-height:1.8;margin:0 0 16px;white-space:pre-line}
.rv-msp{display:flex;gap:12px;align-items:center;border:1px solid var(--line);background:var(--panel);padding:12px 15px;margin:0 0 16px;font-size:13px}
.rv-msp .nm{font-weight:800;color:#fff}
.rv-msp .co{color:var(--muted);font-size:12px;margin-top:2px}
.rv-pdfbox{border:1px solid var(--line);background:var(--panel);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 16px}
.rv-pdfbox .pn{font-size:13px;font-weight:700;color:#fff}
.rv-pdfbox .ps{font-size:11.5px;color:var(--muted);margin-top:3px}
.rv-pdfbtn{display:inline-block;padding:10px 20px;background:transparent;border:1px solid var(--mint);color:var(--mint);font-size:13px;font-weight:800;cursor:pointer}
.rv-pdfbtn:hover{background:rgba(0,255,200,.08)}
.rv-nopdf{font-size:12.5px;color:var(--muted);border:1px dashed var(--line2);padding:11px 14px;margin:0 0 16px}
.rv-copyright{font-size:11.5px;color:#6b6b76;line-height:1.7;border-top:1px solid var(--line);padding-top:13px}
@media(max-width:640px){.rv-filters{gap:8px}.rv-search{max-width:none}}
</style>
</head>
<body>
<header class="rv-top">
  <a href="../index.php" aria-label="Unreal Fest Seoul 2026"><img class="rv-toplogo" src="../white_logo.svg" alt="Unreal Fest Seoul 2026"></a>
  <?php if ($verified && $svc_open): ?><div class="rv-user"><span><b><?= rv_e($viewer) ?></b>님</span><a class="rv-out" href="./?logout=1">로그아웃</a></div><?php endif; ?>
</header>

<?php if (!$svc_open): ?>
  <!-- 서비스 미운영 상태 -->
  <div class="rv-gate">
    <div class="rv-gcard" style="text-align:center">
      <img class="rv-glogo" src="../white_logo.svg" alt="Unreal Fest Seoul 2026" style="margin-left:auto;margin-right:auto">
      <h1>세션 다시보기</h1>
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
      <h1>세션 다시보기</h1>
      <p>언리얼 페스트 서울 2026 <b style="color:#cfd0d6">오프라인 등록자</b>를 위한 다시보기 페이지입니다. 등록 시 입력한 이메일과 연락처로 체크인해 주세요.</p>
      <?php if ($gate_err): ?><div class="rv-err"><?= rv_e($gate_err) ?><br><span style="color:#c9a0a0">온라인 전용 등록자는 다시보기를 이용할 수 없습니다.</span></div><?php endif; ?>
      <form method="post" action="./">
        <input type="hidden" name="rv_csrf" value="<?= rv_e($csrf) ?>">
        <input type="email" name="rv_email" autocapitalize="off" autocomplete="email" placeholder="등록 이메일" required autofocus>
        <input type="tel" name="rv_phone" inputmode="numeric" autocomplete="tel" placeholder="등록 연락처" required>
        <button type="submit">다시보기 입장 →</button>
      </form>
      <p style="font-size:12px;color:#6b6b76;margin:16px 0 0;line-height:1.7">온라인 전용 등록자는 다시보기를 이용할 수 없습니다.<br>등록 확인이 안 되면 사무국으로 문의해 주세요.<br>02-326-3701 · info@epiclounge.co.kr</p>
    </div>
  </div>

<?php else: ?>
  <!-- 목록 -->
  <div class="rv-wrap">
    <h1 class="rv-h1">세션 다시보기</h1>
    <p class="rv-sub">언리얼 페스트 서울 2026 세션 영상과 강연자료를 다시 확인하실 수 있습니다.<?php if ($period_end !== ''): ?> 다시보기는 <b style="color:#cfd0d6"><?= rv_e(substr($period_end, 0, 10)) ?></b>까지 제공됩니다.<?php endif; ?></p>
    <p class="rv-legal">영상과 강연자료의 저작권은 발표자 및 각 권리자에게 있습니다. 등록자 개인의 학습 목적으로만 이용할 수 있으며, 무단 녹화·복제·배포·재업로드를 금지합니다.</p>
    <?php if ($notice !== ''): ?><div class="rv-notice"><?= nl2br(rv_e($notice)) ?></div><?php endif; ?>

    <?php if (!$sessions): ?>
      <div class="rv-empty">다시보기가 준비 중입니다.<br>영상과 강연자료가 준비되는 대로 순차적으로 공개됩니다.</div>
    <?php else: ?>

    <div class="rv-filters">
      <div class="rv-tabs" role="tablist">
        <button type="button" class="rv-tab on" data-day="all">전체</button>
        <button type="button" class="rv-tab" data-day="1">Day 1</button>
        <button type="button" class="rv-tab" data-day="2">Day 2</button>
      </div>
      <?php if (count($tracks) > 1): ?>
      <select class="rv-sel" id="rvTrack" aria-label="트랙 필터">
        <option value="">모든 트랙</option>
        <?php foreach ($tracks as $t): ?><option value="<?= rv_e($t) ?>"><?= rv_e($t) ?></option><?php endforeach; ?>
      </select>
      <?php endif; ?>
      <input type="search" class="rv-search" id="rvSearch" placeholder="세션명 · 발표자 검색" aria-label="세션 검색">
    </div>

    <?php foreach (array('1','2') as $d): ?>
      <?php $dsess = array(); foreach ($sessions as $s) { if ((string)(int)$s['ag_day'] === $d) $dsess[] = $s; } if (!$dsess) continue; ?>
      <div class="rv-day" data-dayhead="<?= $d ?>"><?= rv_e($DAYS[$d]) ?></div>
      <div class="rv-grid" data-daygrid="<?= $d ?>">
        <?php foreach ($dsess as $s):
          $agno = (int)$s['ag_no'];
          $has_video = ($s['rv_video_public'] === 'Y' && $s['rv_vimeo'] !== '');
          $has_pdf   = ($s['rv_pdf_public'] === 'Y' && $s['rv_pdf_url'] !== '');
          $thumb = trim($s['rv_thumb']);
          $hay = mb_strtolower($s['ag_title'] . ' ' . $s['ag_sp_name'] . ' ' . $s['ag_sp_company'], 'UTF-8');
        ?>
        <button type="button" class="rv-card" data-id="<?= $agno ?>" data-day="<?= (int)$s['ag_day'] ?>"
                data-track="<?= rv_e($s['ag_track']) ?>" data-hay="<?= rv_e($hay) ?>">
          <div class="rv-thumb"<?= $thumb !== '' ? ' style="background-image:url(\'' . rv_e($thumb) . '\')"' : '' ?>>
            <?php if ($thumb === ''): ?><div class="ph"></div><?php endif; ?>
            <?php if ($has_video): ?>
            <div class="play"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="11" fill="rgba(0,0,0,.45)"/><path d="M10 8.5l6 3.5-6 3.5z" fill="#fff"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="rv-meta">
            <?php if ($s['ag_track'] !== ''): ?><div class="rv-trk"><?= rv_e($s['ag_track']) ?></div><?php endif; ?>
            <div class="rv-title"><?= rv_e($s['ag_title']) ?></div>
            <?php if ($s['ag_sp_name'] !== ''): ?><div class="rv-sp"><?= rv_e($s['ag_sp_name']) ?><?= $s['ag_sp_company'] !== '' ? ' · ' . rv_e($s['ag_sp_company']) : '' ?></div><?php endif; ?>
            <?php if ($s['ag_time'] !== ''): ?><div class="rv-dt">Day <?= (int)$s['ag_day'] ?> · <?= rv_e($s['ag_time']) ?></div><?php endif; ?>
            <div class="rv-badges">
              <?php if ($has_video): ?><span class="rv-bdg v">다시보기</span><?php endif; ?>
              <?php if ($has_pdf): ?><span class="rv-bdg p">강연자료</span><?php endif; ?>
              <?php if (!$has_video && !$has_pdf): ?><span class="rv-bdg w">준비 중</span>
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
        <p class="rv-minfo" id="rvMInfo"></p>
        <h2 class="rv-mtitle" id="rvMTitle"></h2>
        <div class="rv-msp" id="rvMSp" style="display:none"><div><div class="nm" id="rvMSpNm"></div><div class="co" id="rvMSpCo"></div></div></div>
        <p class="rv-mdesc" id="rvMDesc"></p>
        <div class="rv-pdfbox" id="rvMPdf" style="display:none">
          <div><div class="pn" id="rvMPdfNm"></div><div class="ps" id="rvMPdfSz"></div></div>
          <a class="rv-pdfbtn" id="rvMPdfBtn" href="#" target="_blank" rel="noopener">강연자료 PDF 보기 ↗</a>
        </div>
        <div class="rv-nopdf" id="rvMNoPdf" style="display:none">발표자료가 제공되지 않는 세션입니다.</div>
        <p class="rv-copyright">영상과 강연자료의 저작권은 발표자 및 각 권리자에게 있습니다. 등록자 개인의 학습 목적으로만 이용할 수 있으며, 무단 녹화·복제·배포·재업로드를 금지합니다.</p>
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
  function rvOpen(id){
    var d = RV[id]; if (!d) return;
    $id('rvMTop').textContent = d.title;
    $id('rvMInfo').textContent = 'Day ' + d.day + (d.time ? ' · ' + d.time : '') + (d.track ? ' · ' + d.track : '');
    $id('rvMTitle').textContent = d.title;
    $id('rvMDesc').textContent = d.desc || '';
    if (d.sp) {
      $id('rvMSp').style.display = 'flex';
      $id('rvMSpNm').textContent = d.sp;
      $id('rvMSpCo').textContent = [d.sp_role, d.sp_co].filter(Boolean).join(' · ');
    } else { $id('rvMSp').style.display = 'none'; }
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
      no.textContent = '해당 세션의 다시보기 영상은 준비 중입니다.';
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
    if (card) { rvOpen(parseInt(card.getAttribute('data-id'), 10)); return; }
    if (ev.target === $id('rvMask') || ev.target === $id('rvClose')) rvCloseModal();
  });
  document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape') rvCloseModal(); });

  /* 필터 — Day 탭 + 트랙 + 검색 */
  var rvDay = 'all', rvTrk = '', rvQ = '';
  function rvFilter(){
    var shown = 0;
    document.querySelectorAll('.rv-card').forEach(function(c){
      var ok = (rvDay === 'all' || c.getAttribute('data-day') === rvDay)
            && (rvTrk === '' || c.getAttribute('data-track') === rvTrk)
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
  var trkSel = $id('rvTrack');
  if (trkSel) trkSel.addEventListener('change', function(){ rvTrk = trkSel.value; rvFilter(); });
  var srch = $id('rvSearch');
  if (srch) srch.addEventListener('input', function(){ rvQ = srch.value.trim().toLowerCase(); rvFilter(); });
  </script>
<?php endif; ?>
</body></html>
