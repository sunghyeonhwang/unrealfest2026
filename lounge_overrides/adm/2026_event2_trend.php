<?php
/* Unreal Fest Seoul 2026 — 관리자: 일자별 등록 추세 (adm/2026_event2_trend.php)
 * 목록(2026_event2_list.php)에서 분리. 집계쿼리는 이 페이지에서만 실행(목록 트래픽 절감).
 * 날짜 오름차순(위=과거 → 아래=최신). 행별 복사 버튼 + 전체 복사. PHP 7.0 호환.
 */
$sub_menu = '700320';
include_once('./_common.php');
if (!function_exists('is_admin') || !is_admin($member['mb_id'])) {
    alert('관리자 로그인이 필요합니다.', G5_ADMIN_URL);
}
$g5['title'] = 'UFS 2026 일자별 등록 추세';
function e2($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// 일자별 등록 추세 (apply_reg_datetime 기준)
$trend = array();
// 등록시각 t → '다음 오전 10시' 아침으로 라벨(매일 오전 10시 기준): label = DATE(t + 14h)
//   t<10:00 → 당일 아침 / t>=10:00 → 익일 아침. 각 행 = 해당일 오전 10:00 시점 스냅샷.
$rt = sql_query("SELECT DATE(apply_reg_datetime + INTERVAL 14 HOUR) d,
    SUM(apply_pay_status=10 AND apply_product_code='NORMAL_ALL') allday,
    SUM(apply_pay_status=10 AND apply_product_code IN ('NORMAL_20','NORMAL_21')) dayone,
    SUM(apply_pay_status<>0 AND (free_yn='Y' OR apply_product_code='ONLINE')) onl,
    SUM(apply_pay_status<>0) act,
    SUM(apply_pay_status=0) cancel,
    SUM(apply_pay_status=0 AND apply_product_code IN ('NORMAL_ALL','NORMAL_20','NORMAL_21')) cancel_off,
    SUM(apply_pay_status=0 AND (free_yn='Y' OR apply_product_code='ONLINE')) cancel_on
  FROM cb_unreal_2026_event2_apply
  WHERE apply_temp_yn='N' AND apply_reg_datetime IS NOT NULL AND apply_reg_datetime>'1970-01-01'
  GROUP BY DATE(apply_reg_datetime + INTERVAL 14 HOUR) ORDER BY d ASC");
if ($rt) { while ($x = $rt->fetch_assoc()) { $trend[] = $x; } }
$cum_tot=0;$cum_off=0;$cum_on=0;$trend_max=1;
foreach ($trend as $i=>$row) {
    $off=(int)$row['allday']+(int)$row['dayone'];
    $cum_tot+=(int)$row['act']; $cum_off+=$off; $cum_on+=(int)$row['onl'];
    $trend[$i]['off']=$off; $trend[$i]['cum_tot']=$cum_tot; $trend[$i]['cum_off']=$cum_off; $trend[$i]['cum_on']=$cum_on;
    if ((int)$row['act']>$trend_max) $trend_max=(int)$row['act'];
}
// 가장 최근 '지나간' 오전 10시 = 마지막 확정 스냅샷일. 그 이후(=오늘 10시 이후) 라벨은 집계중.
$ufs_last_snap = ((int)date('H') >= 10) ? date('Y-m-d') : date('Y-m-d', strtotime('-1 day'));

include_once('./admin.head.php');   // ← 왼쪽 관리자 메뉴 + 상단 chrome
?>
<style>
#container_title{display:none}
.ufs26{padding:8px 4px 40px;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;color:#1f2330}
.ufs26 h1{font-size:1.4em;font-weight:800;margin:0 0 6px}
.ufs26 .sub{color:#8a90a2;font-size:13px;margin:0 0 18px}
.ufs26 .toprow{display:flex;align-items:center;gap:8px;margin-bottom:16px}
.ufs26 .btn{height:36px;box-sizing:border-box;border:1px solid #d6dae3;border-radius:8px;padding:0 14px;font-size:13px;font-weight:700;cursor:pointer;background:#fff;color:#3a4153;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;line-height:1}
.ufs26 .btn.pri{background:#00C1D5;border-color:#00C1D5;color:#062a2f}
.ufs26 table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e6e8ee;border-radius:10px;overflow:hidden;font-size:13px}
.ufs26 th{background:#f5f6fa;text-align:left;padding:11px 12px;color:#6b7280;font-weight:700;white-space:nowrap}
.ufs26 td{padding:10px 12px;border-top:1px solid #eef0f5;white-space:nowrap}
.ufs26 tr:hover td{background:#fafbfd}
.ufs26 .copybtn{border:1px solid #d6dae3;background:#fff;border-radius:6px;padding:4px 10px;font-size:12px;font-weight:700;cursor:pointer;color:#3a4153}
.ufs26 .copybtn:hover{border-color:#00C1D5;color:#0a7a86}
.ufs26 .trend-chart{display:flex;align-items:flex-end;gap:6px;height:200px;background:#fff;border:1px solid #e6e8ee;border-radius:10px;padding:18px 16px 6px;margin-bottom:14px;overflow-x:auto}
.ufs26 .tcol{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;min-width:30px}
.ufs26 .tval{font-size:11px;font-weight:800;color:#3a4153;margin-bottom:4px}
.ufs26 .tbar{width:22px;border-radius:4px 4px 0 0;overflow:hidden;background:#eef0f5}
.ufs26 .tbar .seg-on{background:#2aa7b5}
.ufs26 .tbar .seg-off{background:#00C1D5}
.ufs26 .tlbl{font-size:10px;color:#8a90a2;margin-top:6px;white-space:nowrap}
.ufs26 .trend-legend{display:flex;gap:16px;font-size:12px;color:#6b7280;margin:0 0 12px 2px}
.ufs26 .trend-legend i{display:inline-block;width:11px;height:11px;border-radius:2px;margin-right:5px;vertical-align:-1px}
</style>

<div class="ufs26">
  <h1>일자별 등록 추세 <span style="display:inline-block;background:#00C1D5;color:#062a2f;font-size:12px;font-weight:800;border-radius:6px;padding:3px 10px;vertical-align:middle;margin-left:6px">매일 오전 10시 기준</span></h1>
  <p class="sub">각 행 = 해당 날짜 <b>오전 10:00 시점</b>의 누적 등록. 신규·취소 = <b>전일 10:00 ~ 당일 10:00</b> 변동분. 기준 = 등록 일시(apply_reg_datetime) · 날짜 오름차순(위=과거 → 아래=최신). <span style="color:#e67e22;font-weight:700">집계중</span> = 오늘 오전 10시 이후 누적(다음 10시에 확정).</p>

  <div class="toprow">
    <a href="2026_event2_list.php" class="btn">← 등록 현황 목록</a>
    <button type="button" class="btn pri" onclick="ufsCopyAll()" style="margin-left:auto">표 전체 복사</button>
  </div>

  <div class="trend-legend"><span><i style="background:#00C1D5"></i>오프라인</span><span><i style="background:#2aa7b5"></i>온라인</span><span style="margin-left:auto">막대 높이 = 전일 10시~당일 10시 신규 등록</span></div>
  <div class="trend-chart">
    <?php if (!$trend): ?><div style="color:#8a90a2;padding:20px">데이터 없음</div><?php endif; ?>
    <?php foreach ($trend as $row): $act=(int)$row['act'];
      $bh = $trend_max>0 ? max(2, (int)round($act/$trend_max*160)) : 2;
      $oh = $act>0 ? (int)round($row['off']/$act*$bh) : 0; $nh = $bh-$oh; ?>
    <div class="tcol" title="<?= e2($row['d']) ?> · 전체 <?= $act ?> (오프 <?= $row['off'] ?>/온 <?= (int)$row['onl'] ?>)">
      <div class="tval"><?= $act ?></div>
      <div class="tbar" style="height:<?= $bh ?>px"><div class="seg-on" style="height:<?= $nh ?>px"></div><div class="seg-off" style="height:<?= $oh ?>px"></div></div>
      <div class="tlbl"><?= date('n/j', strtotime($row['d'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <table id="trendTable">
    <thead><tr>
      <th>기준일(오전10시)</th><th>전체 누적</th><th>취소건</th><th>전체 등록자 수</th>
      <th>오프라인 누적</th><th>오프라인 양일권</th><th>오프라인 일일권</th><th>온라인 누적</th><th>온라인 등록자 수</th>
      <th class="nocopy">+카운트(합)</th><th class="nocopy">−카운트(합)</th><th class="nocopy">복사</th>
    </tr></thead>
    <tbody>
    <?php foreach ($trend as $i=>$row): ?>
      <tr<?= ($i===0 || $row['d'] > $ufs_last_snap) ? ' class="xcopyall"' : '' ?>>
        <td><?= e2($row['d']) ?><?php if ($row['d'] > $ufs_last_snap): ?> <span style="color:#e67e22;font-size:10px;font-weight:800">집계중</span><?php endif; ?></td>
        <td><b><?= number_format($row['cum_tot']) ?></b></td>
        <td style="color:#9aa0af"><?= number_format($row['cancel']) ?></td>
        <td><?= number_format($row['act']) ?></td>
        <td><?= number_format($row['cum_off']) ?></td>
        <td><?= number_format($row['allday']) ?></td>
        <td><?= number_format($row['dayone']) ?></td>
        <td><?= number_format($row['cum_on']) ?></td>
        <td><?= number_format($row['onl']) ?></td>
        <td class="nocopy" style="color:#c0392b;white-space:nowrap;line-height:1.35"><b style="font-size:14px">+<?= number_format((int)$row['onl']+(int)$row['off']) ?></b><br><span style="font-size:11px">오프라인 <?= number_format($row['off']) ?>, 온라인 <?= number_format((int)$row['onl']) ?></span></td>
        <td class="nocopy" style="color:#1d68c4;white-space:nowrap;line-height:1.35"><b style="font-size:14px">−<?= number_format((int)$row['cancel_on']+(int)$row['cancel_off']) ?></b><br><span style="font-size:11px">오프라인 <?= number_format((int)$row['cancel_off']) ?>, 온라인 <?= number_format((int)$row['cancel_on']) ?></span></td>
        <td class="nocopy"><button type="button" class="copybtn" onclick="ufsCopyRow(this)">복사</button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
function ufsCopyText(t){
  if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(t); return; }
  var ta=document.createElement('textarea'); ta.value=t; ta.style.position='fixed'; ta.style.opacity='0';
  document.body.appendChild(ta); ta.focus(); ta.select();
  try { document.execCommand('copy'); } catch(e){}
  document.body.removeChild(ta);
}
function ufsIsNoCopy(el){ return (el.className||'').indexOf('nocopy')>=0; }
function ufsCellText(el){ return (el.innerText||el.textContent).replace(/,/g,'').trim(); }
function ufsCopyRow(btn){
  var tr=btn.parentNode.parentNode;
  var tds=tr.querySelectorAll('td'), arr=[];
  for (var i=0;i<tds.length;i++){ if(ufsIsNoCopy(tds[i])) continue; arr.push(ufsCellText(tds[i])); }
  ufsCopyText(arr.join('\t'));
  var o=btn.innerText; btn.innerText='복사됨'; setTimeout(function(){btn.innerText=o;},900);
}
function ufsCopyAll(){
  var t=document.getElementById('trendTable'), out=[];
  var ths=t.querySelectorAll('thead th'), h=[];
  for (var i=0;i<ths.length;i++){ if(ufsIsNoCopy(ths[i])) continue; h.push((ths[i].innerText||ths[i].textContent).trim()); }
  out.push(h.join('\t'));
  var trs=t.querySelectorAll('tbody tr'), n=0;
  for (var r=0;r<trs.length;r++){
    if ((trs[r].className||'').indexOf('xcopyall')>=0) continue;  // 1행·집계중 행 제외
    var tds=trs[r].querySelectorAll('td'), row=[];
    for (var c=0;c<tds.length;c++){ if(ufsIsNoCopy(tds[c])) continue; row.push(ufsCellText(tds[c])); }
    out.push(row.join('\t')); n++;
  }
  ufsCopyText(out.join('\n'));
  alert('표 전체('+n+'행)를 복사했습니다. 1행·집계중 행과 +/−카운트 열은 제외됩니다. 엑셀/시트에 붙여넣기 하세요.');
}
</script>
<?php
include_once('./admin.tail.php');
