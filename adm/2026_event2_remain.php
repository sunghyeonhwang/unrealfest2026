<?php
/* Unreal Fest Seoul 2026 — 관리자: 트랙 정원 설정 (adm/2026_event2_remain.php)
 * 2026_event_ticket.date1(=정원) 설정. 등록현황(2026_event2_list)의 트랙 그래픽과 연동.
 * PHP 7.0 호환.
 */
$sub_menu = '700330';
include_once('./_common.php');
if (!function_exists('is_admin') || !is_admin($member['mb_id'])) {
    alert('관리자 로그인이 필요합니다.', G5_ADMIN_URL);
}
$g5['title'] = '트랙 정원 설정';
function e2($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function cnt2($w){ $r = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE $w"); return $r ? (int)$r['c'] : 0; }

$saved = isset($_GET['saved']);
$colors = array('DAY1_TR1'=>'#307FE2','DAY1_TR2'=>'#FF8F1C','DAY1_TR3'=>'#FA4616','DAY1_TR4'=>'#DD0AB2',
                'DAY2_TR1'=>'#307FE2','DAY2_TR2'=>'#FF8F1C','DAY2_TR3'=>'#FA4616','DAY2_TR4'=>'#DD0AB2');
$res = sql_query("SELECT * FROM 2026_event_ticket ORDER BY name");
$rows = array();
if ($res) { while ($r = $res->fetch_assoc()) { $rows[] = $r; } }

include_once('./admin.head.php');
?>
<style>
.ufs26{padding:8px 4px 40px;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;color:#1f2330}
.ufs26 h1{font-size:1.4em;font-weight:800;margin:0 0 6px}
.ufs26 .desc{color:#8a90a2;font-size:13px;margin:0 0 18px}
.ufs26 .saved{background:#e6fbf2;border:1px solid #9be7c9;color:#0c8b5a;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:13px}
.ufs26 table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e6e8ee;border-radius:10px;overflow:hidden;font-size:13px}
.ufs26 th{background:#f5f6fa;text-align:left;padding:11px 12px;color:#6b7280;font-weight:700}
.ufs26 td{padding:12px;border-top:1px solid #eef0f5;vertical-align:middle}
.ufs26 .dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:8px;vertical-align:middle}
.ufs26 input[type=number]{border:1px solid #d6dae3;border-radius:8px;padding:8px 10px;font-size:14px;width:110px;font-weight:700}
.ufs26 .rem{font-weight:800}
.ufs26 .btn{display:inline-flex;align-items:center;justify-content:center;line-height:1;height:44px;box-sizing:border-box;border-radius:8px;padding:0 26px;font-size:14px;font-weight:800;cursor:pointer;border:1px solid #00C1D5;background:#00C1D5;color:#062a2f;margin-top:18px}
.ufs26 .lnk{color:#2aa7b5;text-decoration:none;font-size:13px;font-weight:700}
</style>
<div class="ufs26">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <div>
      <h1>트랙 정원 설정</h1>
      <p class="desc">트랙별 정원을 설정하면 <a href="2026_event2_list.php" class="lnk">등록 현황</a>의 트랙 그래픽에 잔여 인원이 표시됩니다.</p>
    </div>
    <a href="2026_event2_list.php" class="lnk">← 등록 현황</a>
  </div>
  <?php if ($saved): ?><div class="saved">✓ 정원이 저장되었습니다.</div><?php endif; ?>

  <form method="post" action="2026_event2_remain_proc.php">
    <table>
      <thead><tr><th>트랙</th><th>현재 등록</th><th>정원</th><th>잔여</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r):
        $name = $r['name'];
        $reg = cnt2("apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($name)."%'");
        $cap = (int)$r['date1'];
        $rem = $cap - $reg;
        $color = isset($colors[$name]) ? $colors[$name] : '#888'; ?>
        <tr>
          <td><span class="dot" style="background:<?= $color ?>"></span><?= e2($r['label']) ?> <span style="color:#b6bcc9;font-size:11px">(<?= e2($name) ?>)</span></td>
          <td><?= number_format($reg) ?>명</td>
          <td><input type="number" name="cap[<?= e2($name) ?>]" value="<?= $cap ?>" min="0"></td>
          <td class="rem" style="color:<?= $rem<=0?'#e0492f':($rem<50?'#FF8F1C':'#0c8b5a') ?>"><?= number_format($rem) ?>명</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="btn">정원 저장</button>
  </form>
</div>
<?php include_once('./admin.tail.php');
