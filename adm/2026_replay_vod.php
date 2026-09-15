<?php
/* Unreal Fest Seoul 2026 — 관리자: 다시보기(Vimeo+PDF) 관리 (adm/2026_replay_vod.php)
 *
 * ⚠ 이 파일은 버전관리용 복사본. 실제 배포 위치 = www/v3/adm/2026_replay_vod.php (repo 밖).
 *
 * 신규 다시보기(/unrealfest2026/replay/) 콘텐츠 관리. 기획: unrealfest2026/replay/REPLAY_PLANNING.md
 *  - 세션 정보 = cb_unreal_2026_agenda 재사용(수정 없음).
 *  - 다시보기 전용 값만 cb_unreal_2026_replay_vod 에 저장:
 *      노출 / Vimeo ID·비공개 해시 / 썸네일 / 영상 공개 / PDF URL·표시명·크기 / PDF 공개
 *  - 운영 설정(cb_unreal_2026_config): replay_enabled, replay_start, replay_end,
 *      replay_notice, replay_pdf_domain(쉼표 구분 허용 도메인, 비우면 제한 없음)
 *  - 구 YouTube 다시보기 설정(2026_replay_config.php / cb_unreal_2026_replay)과는 별개.
 * PHP 7.0 호환. charset=utf8.
 */
$sub_menu = '700378';
include_once('./_common.php');
if (!function_exists('is_admin') || !is_admin($member['mb_id'])) { alert('관리자 로그인이 필요합니다.', G5_ADMIN_URL); }
$g5['title'] = '다시보기(Vimeo+PDF) 관리';

function rvA_e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* Vimeo 입력 정규화 — URL/ID 무엇을 넣어도 (숫자ID, 해시)로.
 *   https://vimeo.com/123456789/abcdef1234  (비공개 링크 = 해시 포함)
 *   https://player.vimeo.com/video/123456789?h=abcdef1234
 *   https://vimeo.com/123456789  /  123456789 */
function rvA_vimeo($s) {
    $s = trim((string)$s);
    if ($s === '') return array('', '');
    $id = ''; $hash = '';
    if (preg_match('~vimeo\.com/(?:video/)?(\d{6,12})(?:/([a-zA-Z0-9]{6,}))?~', $s, $m)) {
        $id = $m[1];
        if (isset($m[2]) && $m[2] !== '') $hash = strtolower($m[2]);
    } else if (preg_match('~^\d{6,12}$~', $s)) {
        $id = $s;
    }
    if ($hash === '' && preg_match('~[?&]h=([a-zA-Z0-9]+)~', $s, $m2)) $hash = strtolower($m2[1]);
    return array($id, $hash);
}

/* 설정 키-값 (라이브 안내와 같은 테이블) */
@sql_query("CREATE TABLE IF NOT EXISTS cb_unreal_2026_config (
    cfg_key VARCHAR(64) NOT NULL, cfg_val TEXT, PRIMARY KEY (cfg_key)) DEFAULT CHARSET=utf8");
function rvA_get($k, $def = '') {
    $r = @sql_fetch("SELECT cfg_val FROM cb_unreal_2026_config WHERE cfg_key='" . sql_real_escape_string($k) . "'");
    return ($r && $r['cfg_val'] !== '') ? $r['cfg_val'] : $def;
}
function rvA_set($k, $v) {
    $k = sql_real_escape_string($k); $v = sql_real_escape_string($v);
    $e = @sql_fetch("SELECT cfg_key FROM cb_unreal_2026_config WHERE cfg_key='$k'");
    if ($e) @sql_query("UPDATE cb_unreal_2026_config SET cfg_val='$v' WHERE cfg_key='$k'");
    else    @sql_query("INSERT INTO cb_unreal_2026_config (cfg_key,cfg_val) VALUES ('$k','$v')");
}
function rvA_dt($v) {   // datetime-local → 'Y-m-d H:i'
    $v = trim(str_replace('T', ' ', (string)$v));
    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $v) ? substr($v, 0, 16) : '';
}

/* 다시보기 전용 테이블 */
$TBL = 'cb_unreal_2026_replay_vod';
sql_query("CREATE TABLE IF NOT EXISTS $TBL (
    rv_agno INT NOT NULL,
    rv_show CHAR(1) NOT NULL DEFAULT 'Y',
    rv_vimeo VARCHAR(20) NOT NULL DEFAULT '',
    rv_vimeo_hash VARCHAR(40) NOT NULL DEFAULT '',
    rv_thumb VARCHAR(300) NOT NULL DEFAULT '',
    rv_video_public CHAR(1) NOT NULL DEFAULT 'N',
    rv_pdf_url VARCHAR(500) NOT NULL DEFAULT '',
    rv_pdf_name VARCHAR(200) NOT NULL DEFAULT '',
    rv_pdf_size VARCHAR(30) NOT NULL DEFAULT '',
    rv_pdf_public CHAR(1) NOT NULL DEFAULT 'N',
    rv_memo VARCHAR(255) NOT NULL DEFAULT '',
    rv_upd_dt DATETIME DEFAULT NULL,
    PRIMARY KEY (rv_agno)
) DEFAULT CHARSET=utf8");

$msg = ''; $warns = array();

/* ── 저장: 운영 설정 ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cfg'])) {
    rvA_set('replay_enabled', (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? '1' : '0');
    rvA_set('replay_start',  rvA_dt(isset($_POST['start']) ? $_POST['start'] : ''));
    rvA_set('replay_end',    rvA_dt(isset($_POST['end']) ? $_POST['end'] : ''));
    rvA_set('replay_notice', trim(isset($_POST['notice']) ? $_POST['notice'] : ''));
    rvA_set('replay_pdf_domain', strtolower(trim(isset($_POST['pdf_domain']) ? $_POST['pdf_domain'] : '')));
    $msg = '운영 설정을 저장했습니다.';
}

/* ── 저장: 세션별 콘텐츠 ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rows']) && isset($_POST['vm'])) {
    $vm = $_POST['vm'];
    $vh   = isset($_POST['vh']) ? $_POST['vh'] : array();
    $th   = isset($_POST['th']) ? $_POST['th'] : array();
    $pu   = isset($_POST['pu']) ? $_POST['pu'] : array();
    $pn   = isset($_POST['pn']) ? $_POST['pn'] : array();
    $ps   = isset($_POST['ps']) ? $_POST['ps'] : array();
    $memo = isset($_POST['memo']) ? $_POST['memo'] : array();
    $show = isset($_POST['show']) ? $_POST['show'] : array();
    $vpub = isset($_POST['vpub']) ? $_POST['vpub'] : array();
    $ppub = isset($_POST['ppub']) ? $_POST['ppub'] : array();
    $allow = strtolower(trim(rvA_get('replay_pdf_domain', '')));
    $n = 0;

    foreach ($vm as $agno => $vraw) {
        $agno = (int)$agno; if ($agno <= 0) continue;
        list($vid, $vhash) = rvA_vimeo($vraw);
        if (trim($vraw) !== '' && $vid === '') $warns[] = "세션 #$agno: Vimeo 입력을 해석하지 못해 비웠습니다.";
        $hin = strtolower(trim(isset($vh[$agno]) ? $vh[$agno] : ''));
        if ($hin !== '' && preg_match('/^[a-z0-9]+$/', $hin)) $vhash = $hin;   // 해시 직접 입력이 우선

        $purl = trim(isset($pu[$agno]) ? $pu[$agno] : '');
        if ($purl !== '') {
            $pp = @parse_url($purl);
            $okd = true;
            if (!$pp || !isset($pp['scheme']) || strtolower($pp['scheme']) !== 'https' || !isset($pp['host'])) {
                $warns[] = "세션 #$agno: PDF 링크는 https 주소만 허용됩니다 — 저장하지 않았습니다."; $purl = '';
            } else if ($allow !== '') {
                $okd = false; $host = strtolower($pp['host']);
                foreach (explode(',', $allow) as $d) {
                    $d = trim($d);
                    if ($d !== '' && ($host === $d || substr($host, -strlen('.' . $d)) === '.' . $d)) { $okd = true; break; }
                }
                if (!$okd) { $warns[] = "세션 #$agno: PDF 링크 도메인($host)이 허용 목록에 없습니다 — 저장하지 않았습니다."; $purl = ''; }
            }
        }

        $d = array(
            'rv_show'         => (isset($show[$agno]) && $show[$agno] === '1') ? 'Y' : 'N',
            'rv_vimeo'        => $vid,
            'rv_vimeo_hash'   => $vhash,
            'rv_thumb'        => trim(isset($th[$agno]) ? $th[$agno] : ''),
            'rv_video_public' => (isset($vpub[$agno]) && $vpub[$agno] === '1') ? 'Y' : 'N',
            'rv_pdf_url'      => $purl,
            'rv_pdf_name'     => trim(isset($pn[$agno]) ? $pn[$agno] : ''),
            'rv_pdf_size'     => trim(isset($ps[$agno]) ? $ps[$agno] : ''),
            'rv_pdf_public'   => (isset($ppub[$agno]) && $ppub[$agno] === '1') ? 'Y' : 'N',
            'rv_memo'         => trim(isset($memo[$agno]) ? $memo[$agno] : ''),
        );
        $ex = sql_fetch("SELECT rv_agno FROM $TBL WHERE rv_agno=$agno");
        // 새 행: 내용이 하나도 없으면 만들지 않는다(테이블 불필요 비대 방지)
        $empty = ($d['rv_vimeo'] === '' && $d['rv_pdf_url'] === '' && $d['rv_thumb'] === '' && $d['rv_memo'] === ''
                  && $d['rv_video_public'] === 'N' && $d['rv_pdf_public'] === 'N');
        if (!$ex && $empty) continue;
        $set = array();
        foreach ($d as $k => $v) $set[] = "$k='" . sql_real_escape_string($v) . "'";
        $set = implode(',', $set);
        if ($ex) sql_query("UPDATE $TBL SET $set, rv_upd_dt=NOW() WHERE rv_agno=$agno");
        else     sql_query("INSERT INTO $TBL SET rv_agno=$agno, $set, rv_upd_dt=NOW()");
        $n++;
    }
    $msg = "세션 콘텐츠를 저장했습니다. ($n개)";
}

/* ── 로드 ── */
$cur = array();
$rs = sql_query("SELECT * FROM $TBL");
if ($rs) { while ($x = sql_fetch_array($rs)) $cur[(int)$x['rv_agno']] = $x; }

$sessions = array();
// 순수 세션만 — 키노트(slot_type/트랙)와 공통 슬롯(휴식·점심·등록·경품 등)은 다시보기 대상에서 제외
$as = sql_query("SELECT ag_no, ag_sid, ag_day, ag_track, ag_time, ag_title, ag_sp_name, ag_sp_company
    FROM cb_unreal_2026_agenda
    WHERE ag_is_active='Y' AND ag_slot_type='session' AND ag_track<>'키노트'
    ORDER BY ag_day ASC, ag_sort ASC, ag_no ASC");
if ($as) { while ($x = sql_fetch_array($as)) $sessions[] = $x; }

$cnt_v = 0; $cnt_vp = 0; $cnt_p = 0; $cnt_pp = 0;
foreach ($cur as $c) {
    if ($c['rv_vimeo'] !== '') $cnt_v++;
    if ($c['rv_vimeo'] !== '' && $c['rv_video_public'] === 'Y') $cnt_vp++;
    if ($c['rv_pdf_url'] !== '') $cnt_p++;
    if ($c['rv_pdf_url'] !== '' && $c['rv_pdf_public'] === 'Y') $cnt_pp++;
}
$cfg_enabled = (rvA_get('replay_enabled', '0') === '1');
$cfg_start   = rvA_get('replay_start', '');
$cfg_end     = rvA_get('replay_end', '');
$cfg_notice  = rvA_get('replay_notice', '');
$cfg_domain  = rvA_get('replay_pdf_domain', '');

/* 체크인·이용 통계(있으면) */
$stat = array('gate_ok' => 0, 'gate_fail' => 0, 'play' => 0, 'pdf' => 0);
$sr = @sql_query("SELECT rl_type, COUNT(*) c FROM cb_unreal_2026_replay_log GROUP BY rl_type");
if ($sr) { while ($x = sql_fetch_array($sr)) { if (isset($stat[$x['rl_type']])) $stat[$x['rl_type']] = (int)$x['c']; } }

include_once('./admin.head.php');
?>
<style>
#rv_wrap{max-width:1500px;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif}
#rv_wrap h1{font-size:1.4em;font-weight:800;margin:6px 0 4px}
#rv_wrap h2{font-size:1.1em;font-weight:800;margin:24px 0 8px}
#rv_wrap .sub{color:#8a90a2;font-size:13px;margin:0 0 14px;line-height:1.6}
#rv_msg{margin:0 0 14px;padding:10px 15px;border-radius:8px;background:#e8f7ee;border:1px solid #b6e2c6;color:#1f7a44;font-size:13px;font-weight:700}
#rv_warn{margin:0 0 14px;padding:10px 15px;border-radius:8px;background:#fdf0ec;border:1px solid #ecc4b8;color:#a5432a;font-size:13px;line-height:1.7}
#rv_bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:0 0 12px}
#rv_bar .card{border:1px solid #e6e8ee;border-radius:8px;padding:9px 15px;background:#fafbfd;font-size:13px}
#rv_bar .card b{font-size:16px}
.rv-save{background:#00C1D5;color:#062a2f;border:0;padding:10px 24px;font-weight:800;border-radius:6px;cursor:pointer;font-size:14px}
#rv_cfg{border:1px solid #e6e8ee;border-radius:10px;background:#fff;padding:16px 18px;margin:0 0 22px}
#rv_cfg .row{display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:10px}
#rv_cfg label.lb{font-size:13px;font-weight:700;color:#454b5c;min-width:110px}
#rv_cfg input[type=text],#rv_cfg input[type=datetime-local],#rv_cfg textarea{padding:8px;border:1px solid #ccd;border-radius:5px;font-size:13px}
#rv_tbl{width:100%;border-collapse:collapse;font-size:12.5px;background:#fff;border:1px solid #e6e8ee}
#rv_tbl th,#rv_tbl td{border:1px solid #eef0f5;padding:8px 9px;vertical-align:top}
#rv_tbl th{background:#f5f6fa;color:#6b7280;font-weight:700;white-space:nowrap;vertical-align:middle}
#rv_tbl td.l{text-align:left}
#rv_tbl tr.dayhead td{background:#0e1420;color:#fff;font-weight:800;font-size:13px;vertical-align:middle}
#rv_tbl input[type=text]{width:100%;padding:6px 8px;border:1px solid #ccd;border-radius:5px;font-size:12px;box-sizing:border-box}
#rv_tbl .in2{display:flex;gap:6px;margin-top:6px}
#rv_tbl .lab{font-size:10.5px;color:#98a0b3;font-weight:700;display:block;margin:0 0 2px}
#rv_tbl .pubchk{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;color:#454b5c;margin-top:7px;white-space:nowrap}
#rv_tbl .pubchk input{width:16px;height:16px}
#rv_tbl .tlink{font-size:11px;color:#00A3B4;font-weight:700;text-decoration:none;margin-left:8px}
#rv_tbl .has{color:#1a9e54;font-weight:700}
#rv_tbl .sess-t{font-weight:700}
#rv_tbl .sess-s{color:#999;font-size:11px;margin-top:2px}
.rv-foot{position:sticky;bottom:0;background:#fff;padding:12px 0;border-top:1px solid #e6e8ee;margin-top:8px;z-index:5}
</style>
<div id="rv_wrap">
  <h1>다시보기(Vimeo+PDF) 관리 <span style="display:inline-block;background:#00C1D5;color:#062a2f;font-size:12px;font-weight:800;border-radius:6px;padding:3px 10px;vertical-align:middle;margin-left:6px">/unrealfest2026/replay/</span></h1>
  <p class="sub">신규 <a href="../unrealfest2026/replay/" target="_blank">등록자 전용 다시보기</a>(Vimeo 영상 + Cloudflare PDF)를 세션별로 관리합니다.
    세션·연사 정보는 아젠다 DB를 그대로 사용하며 여기서는 <b>다시보기 전용 값만</b> 저장합니다.<br>
    Vimeo 칸에는 <b>비공개 링크 전체 URL</b>(예: vimeo.com/123456789/abcd1234)을 붙여넣으면 ID·해시가 자동 분리됩니다.
    관리자는 체크인 없이 <a href="../unrealfest2026/replay/" target="_blank">사용자 화면 미리보기 ↗</a>가 가능합니다.
    구 YouTube 설정은 <a href="./2026_replay_config.php">다시보기(VOD) 설정</a>에 그대로 남아 있습니다(별개).</p>

  <?php if ($msg): ?><div id="rv_msg"><?= rvA_e($msg) ?></div><?php endif; ?>
  <?php if ($warns): ?><div id="rv_warn"><?= implode('<br>', array_map('rvA_e', $warns)) ?></div><?php endif; ?>

  <!-- 운영 설정 -->
  <form method="post">
  <div id="rv_cfg">
    <h2 style="margin-top:0">운영 설정</h2>
    <div class="row">
      <label class="lb">서비스 활성화</label>
      <label style="font-size:13px;font-weight:700"><input type="checkbox" name="enabled" value="1" <?= $cfg_enabled ? 'checked' : '' ?> style="width:17px;height:17px;vertical-align:-3px"> 켜기 (끄면 "준비 중" 안내만 표시)</label>
    </div>
    <div class="row">
      <label class="lb">제공 기간</label>
      <input type="datetime-local" name="start" value="<?= rvA_e($cfg_start !== '' ? str_replace(' ', 'T', $cfg_start) : '') ?>"> ~
      <input type="datetime-local" name="end" value="<?= rvA_e($cfg_end !== '' ? str_replace(' ', 'T', $cfg_end) : '') ?>">
      <span style="font-size:11.5px;color:#8a90a2">비우면 상시. 종료 후엔 "제공 기간 종료" 안내가 나갑니다.</span>
    </div>
    <div class="row">
      <label class="lb">공지 문구</label>
      <textarea name="notice" rows="2" style="flex:1;min-width:320px"><?= rvA_e($cfg_notice) ?></textarea>
    </div>
    <div class="row">
      <label class="lb">PDF 허용 도메인</label>
      <input type="text" name="pdf_domain" value="<?= rvA_e($cfg_domain) ?>" placeholder="예: files.epiclounge.co.kr (쉼표로 복수)" style="flex:1;min-width:320px">
      <span style="font-size:11.5px;color:#8a90a2">Cloudflare 폴더 도메인 확정 후 입력 권장. 비우면 https 만 검사.</span>
    </div>
    <div class="row" style="margin-bottom:0">
      <label class="lb"></label>
      <button type="submit" name="save_cfg" value="1" class="rv-save">운영 설정 저장</button>
      <span style="font-size:12px;color:#8a90a2">체크인 <b><?= $stat['gate_ok'] ?></b> · 실패 <b><?= $stat['gate_fail'] ?></b> · 재생 <b><?= $stat['play'] ?></b> · PDF <b><?= $stat['pdf'] ?></b></span>
    </div>
  </div>
  </form>

  <!-- 세션별 콘텐츠 -->
  <form method="post">
  <div id="rv_bar">
    <div class="card">전체 세션 <b><?= count($sessions) ?></b></div>
    <div class="card">영상 등록 <b style="color:#1a9e54"><?= $cnt_v ?></b> / 공개 <b style="color:#00849a"><?= $cnt_vp ?></b></div>
    <div class="card">PDF 등록 <b style="color:#1a9e54"><?= $cnt_p ?></b> / 공개 <b style="color:#00849a"><?= $cnt_pp ?></b></div>
    <button type="submit" name="save_rows" value="1" class="rv-save" style="margin-left:auto">전체 저장</button>
  </div>

  <table id="rv_tbl">
    <thead><tr>
      <th style="width:52px">Day/시간</th>
      <th>세션</th>
      <th style="width:46px">노출</th>
      <th style="width:26%">Vimeo 영상</th>
      <th style="width:16%">썸네일 URL</th>
      <th style="width:30%">강연자료 PDF (Cloudflare)</th>
    </tr></thead>
    <tbody>
    <?php $lastday = 0; foreach ($sessions as $s):
      $agno = (int)$s['ag_no'];
      $c = isset($cur[$agno]) ? $cur[$agno] : null;
      $vimeo = $c ? $c['rv_vimeo'] : '';        $vhash = $c ? $c['rv_vimeo_hash'] : '';
      $thumb = $c ? $c['rv_thumb'] : '';        $vpubY = ($c && $c['rv_video_public'] === 'Y');
      $purl  = $c ? $c['rv_pdf_url'] : '';      $pname = $c ? $c['rv_pdf_name'] : '';
      $psize = $c ? $c['rv_pdf_size'] : '';     $ppubY = ($c && $c['rv_pdf_public'] === 'Y');
      $showY = (!$c || $c['rv_show'] === 'Y');  // 행 미생성 = 기본 노출(저장 시 내용 있어야 생성됨)
      $vlink = $vimeo !== '' ? 'https://vimeo.com/' . $vimeo . ($vhash !== '' ? '/' . $vhash : '') : '';
    ?>
      <?php if ((int)$s['ag_day'] !== $lastday): $lastday = (int)$s['ag_day']; ?>
        <tr class="dayhead"><td colspan="6">Day<?= $lastday ?> · <?= $lastday === 1 ? '8월 20일(목)' : '8월 21일(금)' ?></td></tr>
      <?php endif; ?>
      <tr>
        <td style="text-align:center;white-space:nowrap">D<?= (int)$s['ag_day'] ?><br><span style="color:#999;font-size:11px"><?= rvA_e($s['ag_time']) ?></span></td>
        <td class="l">
          <div class="sess-t"><?= rvA_e($s['ag_title']) ?>
            <?php if ($vimeo !== ''): ?><span class="has" title="영상 등록됨">●</span><?php endif; ?>
            <?php if ($purl !== ''): ?><span class="has" style="color:#00849a" title="PDF 등록됨">▸</span><?php endif; ?>
          </div>
          <div class="sess-s"><?= rvA_e($s['ag_track']) ?><?= $s['ag_sp_name'] !== '' ? ' · ' . rvA_e($s['ag_sp_name']) : '' ?><?= $s['ag_sp_company'] !== '' ? ' (' . rvA_e($s['ag_sp_company']) . ')' : '' ?></div>
        </td>
        <td style="text-align:center"><input type="checkbox" name="show[<?= $agno ?>]" value="1" <?= $showY ? 'checked' : '' ?> style="width:17px;height:17px" title="다시보기 목록 노출"></td>
        <td>
          <span class="lab">Vimeo URL 또는 ID</span>
          <input type="text" name="vm[<?= $agno ?>]" value="<?= rvA_e($vimeo) ?>" placeholder="https://vimeo.com/… 또는 숫자 ID">
          <div class="in2">
            <div style="flex:1"><span class="lab">비공개 해시(h)</span><input type="text" name="vh[<?= $agno ?>]" value="<?= rvA_e($vhash) ?>" placeholder="(URL에 있으면 자동)"></div>
          </div>
          <label class="pubchk"><input type="checkbox" name="vpub[<?= $agno ?>]" value="1" <?= $vpubY ? 'checked' : '' ?>> 영상 공개</label>
          <?php if ($vlink !== ''): ?><a class="tlink" href="<?= rvA_e($vlink) ?>" target="_blank" rel="noopener">재생 테스트 ↗</a><?php endif; ?>
        </td>
        <td>
          <span class="lab">카드 썸네일(16:9)</span>
          <input type="text" name="th[<?= $agno ?>]" value="<?= rvA_e($thumb) ?>" placeholder="https://… (비우면 기본)">
          <?php if ($thumb !== ''): ?><a class="tlink" href="<?= rvA_e($thumb) ?>" target="_blank" rel="noopener">확인 ↗</a><?php endif; ?>
        </td>
        <td>
          <span class="lab">PDF URL (https)</span>
          <input type="text" name="pu[<?= $agno ?>]" value="<?= rvA_e($purl) ?>" placeholder="https://…/….pdf">
          <div class="in2">
            <div style="flex:1.6"><span class="lab">표시명</span><input type="text" name="pn[<?= $agno ?>]" value="<?= rvA_e($pname) ?>" placeholder="강연자료 PDF"></div>
            <div style="flex:1"><span class="lab">크기</span><input type="text" name="ps[<?= $agno ?>]" value="<?= rvA_e($psize) ?>" placeholder="예: 12MB"></div>
          </div>
          <label class="pubchk"><input type="checkbox" name="ppub[<?= $agno ?>]" value="1" <?= $ppubY ? 'checked' : '' ?>> PDF 공개</label>
          <?php if ($purl !== ''): ?><a class="tlink" href="<?= rvA_e($purl) ?>" target="_blank" rel="noopener">링크 테스트 ↗</a><?php endif; ?>
          <input type="hidden" name="memo[<?= $agno ?>]" value="<?= rvA_e($c ? $c['rv_memo'] : '') ?>">
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="rv-foot">
    <button type="submit" name="save_rows" value="1" class="rv-save">전체 저장</button>
    <span style="color:#999;font-size:12px;margin-left:8px">새 콘텐츠는 기본 <b>비공개</b>입니다. 영상/PDF 는 각각 공개 체크한 세션만 사용자에게 노출됩니다. 노출을 끄면 세션 자체가 목록에서 빠집니다.</span>
  </div>
  </form>
</div>
<?php include_once('./admin.tail.php'); ?>
