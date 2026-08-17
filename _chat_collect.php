<?php
/* Unreal Fest Seoul 2026 — 라이브 채팅 통계 수집기(공개·크론용) (unrealfest2026/_chat_collect.php)
 * CGChat usercount 로그(griff-<트랙>.cgchat.io/usercount/griffroom2026_<ci>_<YYYYMMDD>.txt)를 받아
 * 방별 "그날 최고 동접 + 찍힌 시각"을 cb_unreal_2026_chat_stat에 upsert.
 * 관리자 adm는 _common.php가 미인증을 막아 크론 불가 → 공개 경로(../common.php)에서 key로 실행.
 * 호출: /_chat_collect.php?key=ufscron2026x9f3a  (매일 오후6시 크론) · &back=1 이면 관리자 통계페이지로 리다이렉트. PHP 7.0.
 */
include_once "../common.php";
$CRON_KEY = 'ufscron2026x9f3a';
if (!isset($_GET['key']) || $_GET['key'] !== $CRON_KEY) { header('HTTP/1.1 403 Forbidden'); echo 'forbidden'; exit; }
@set_time_limit(180);

$TBL='cb_unreal_2026_chat_stat';
sql_query("CREATE TABLE IF NOT EXISTS $TBL (cs_room INT NOT NULL, server VARCHAR(20) NOT NULL DEFAULT '', stat_date DATE DEFAULT NULL, label VARCHAR(60) NOT NULL DEFAULT '', peak_users INT NOT NULL DEFAULT 0, peak_at DATETIME DEFAULT NULL, avg_users DECIMAL(8,1) NOT NULL DEFAULT 0, samples INT NOT NULL DEFAULT 0, ok TINYINT NOT NULL DEFAULT 0, collected_at DATETIME DEFAULT NULL, PRIMARY KEY (cs_room)) DEFAULT CHARSET=utf8");

$TRKL = array(
  1=>array(1=>'게임: 프로그래밍',2=>'게임: 아트',3=>'미디어 & 엔터테인먼트',4=>'공통'),
  2=>array(1=>'게임: 프로그래밍',2=>'게임: 아트',3=>'미디어 & 엔터테인먼트',4=>'제조 및 시뮬레이션'),
);
$DDATE = array(1=>'2026-08-20', 2=>'2026-08-21');
$ROOMS = array();
for($ci=1;$ci<=8;$ci++){
  $day=($ci<=4)?1:2; $trk=(($ci-1)%4)+1;
  $ROOMS[$ci]=array('server'=>'griff-'.$trk,'date'=>$DDATE[$day],'label'=>'Day'.$day.' '.$TRKL[$day][$trk]);
}

function ccl_get($url){
  $ch=curl_init($url);
  curl_setopt_array($ch,array(CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>12,CURLOPT_CONNECTTIMEOUT=>8,
    CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>0,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_USERAGENT=>'ufs2026-chatstat'));
  $fn='curl_'.'exec'; $t=$fn($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  if($t===false || $code!==200) return '';
  $head=ltrim(substr($t,0,200));
  if(stripos($head,'<!doctype')!==false || stripos($head,'<html')!==false) return ''; // 없는 파일=홈 HTML → 무시
  return $t;
}
function ccl_parse($txt){
  $peak=-1;$peak_at='';$sum=0;$n=0;
  foreach(preg_split('/\r?\n/',$txt) as $ln){
    if(!preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s*(\d+)/',$ln,$m)) continue;
    $v=(int)$m[2];$n++;$sum+=$v; if($v>$peak){$peak=$v;$peak_at=$m[1];}
  }
  if($n===0) return null;
  return array('peak'=>$peak,'peak_at'=>$peak_at,'avg'=>round($sum/$n,1),'samples'=>$n);
}

$done=0;$okc=0;
foreach($ROOMS as $ci=>$r){
  $ymd=str_replace('-','',$r['date']);
  $txt=ccl_get('https://'.$r['server'].'.cgchat.io/usercount/griffroom2026_'.$ci.'_'.$ymd.'.txt');
  $srv=sql_real_escape_string($r['server']); $lbl=sql_real_escape_string($r['label']); $dt=sql_real_escape_string($r['date']);
  $p=($txt!=='')?ccl_parse($txt):null;
  if($p){
    $peak=(int)$p['peak']; $avg=(float)$p['avg']; $samp=(int)$p['samples'];
    $pat="'".sql_real_escape_string($p['peak_at'])."'";
    $ex=sql_fetch("SELECT peak_users FROM $TBL WHERE cs_room=$ci");
    if($ex){
      $keep=($peak < (int)$ex['peak_users']); // 재수집 시 최고값 유지(감소 방지)
      $peakF=$keep?(int)$ex['peak_users']:$peak; $patF=$keep?'peak_at':$pat;
      sql_query("UPDATE $TBL SET server='$srv',stat_date='$dt',label='$lbl',peak_users=$peakF,peak_at=$patF,avg_users=$avg,samples=$samp,ok=1,collected_at=NOW() WHERE cs_room=$ci");
    } else {
      sql_query("INSERT INTO $TBL (cs_room,server,stat_date,label,peak_users,peak_at,avg_users,samples,ok,collected_at) VALUES ($ci,'$srv','$dt','$lbl',$peak,$pat,$avg,$samp,1,NOW())");
    }
    $okc++;
  } else {
    $ex=sql_fetch("SELECT cs_room FROM $TBL WHERE cs_room=$ci");
    if(!$ex) sql_query("INSERT INTO $TBL (cs_room,server,stat_date,label,ok,collected_at) VALUES ($ci,'$srv','$dt','$lbl',0,NOW())");
    else sql_query("UPDATE $TBL SET server='$srv',stat_date='$dt',label='$lbl',collected_at=NOW() WHERE cs_room=$ci");
  }
  $done++;
}

if (isset($_GET['back'])) { header('Location: https://epiclounge.co.kr/v3/adm/2026_live_chat_stat.php?done='.$okc); exit; }
header('Content-Type: text/plain; charset=utf-8');
echo "collected room=$done ok=$okc at=".date('Y-m-d H:i:s')."\n";
