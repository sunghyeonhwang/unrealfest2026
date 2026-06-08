<?php
/* Unreal Fest Seoul 2026 — DB 셋업 (1회용, 실행 후 삭제)
 * cb_unreal_2026_event2_apply 생성. 2025 INSERT/UPDATE 컬럼명 그대로 매칭.
 * 실행: /v3/unrealfest2026/setup_db.php?key=ufs2026setup
 * PHP 7.0 호환. charset=utf8 (실서버 제약).
 */
include_once "../common.php"; // sql_query (Gnuboard DB)

$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026setup') { http_response_code(403); exit('forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$sql = "CREATE TABLE IF NOT EXISTS cb_unreal_2026_event2_apply (
  apply_no INT UNSIGNED NOT NULL AUTO_INCREMENT,
  apply_user_name VARCHAR(100) NOT NULL DEFAULT '',
  apply_user_email VARCHAR(150) NOT NULL DEFAULT '',
  apply_user_phone VARCHAR(30) NOT NULL DEFAULT '',
  apply_coupon_no VARCHAR(60) NOT NULL DEFAULT '',
  apply_user_job VARCHAR(40) NOT NULL DEFAULT '',
  apply_user_company VARCHAR(150) NOT NULL DEFAULT '',
  apply_user_depart VARCHAR(150) NOT NULL DEFAULT '',
  apply_user_grade VARCHAR(60) NOT NULL DEFAULT '',
  apply_sector VARCHAR(60) NOT NULL DEFAULT '',
  apply_product_code VARCHAR(40) NOT NULL DEFAULT '',
  apply_user_ex1 VARCHAR(60) NOT NULL DEFAULT '',
  apply_user_ex2 VARCHAR(60) NOT NULL DEFAULT '',
  apply_user_ex3 VARCHAR(60) NOT NULL DEFAULT '',
  apply_user_ex4 VARCHAR(60) NOT NULL DEFAULT '',
  apply_user_email2 VARCHAR(150) NOT NULL DEFAULT '',
  apply_product_price VARCHAR(20) NOT NULL DEFAULT '',
  apply_product_name VARCHAR(120) NOT NULL DEFAULT '',
  apply_tshirt VARCHAR(10) NOT NULL DEFAULT '',
  apply_user_event_agree VARCHAR(1) NOT NULL DEFAULT '0',
  apply_password VARCHAR(40) NOT NULL DEFAULT '',
  apply_ci VARCHAR(255) NOT NULL DEFAULT '',
  apply_di VARCHAR(255) NOT NULL DEFAULT '',
  apply_track VARCHAR(150) NOT NULL DEFAULT '',
  apply_temp_yn CHAR(1) NOT NULL DEFAULT 'Y',
  apply_pay_status INT NOT NULL DEFAULT 0,
  pay_complete CHAR(1) NOT NULL DEFAULT 'N',
  free_yn CHAR(1) NOT NULL DEFAULT 'N',
  pay_moid VARCHAR(60) NOT NULL DEFAULT '',
  pay_tid VARCHAR(60) NOT NULL DEFAULT '',
  pay_totprice VARCHAR(20) NOT NULL DEFAULT '',
  pay_goodname VARCHAR(120) NOT NULL DEFAULT '',
  pay_applnum VARCHAR(60) NOT NULL DEFAULT '',
  pay_appldate VARCHAR(20) NOT NULL DEFAULT '',
  pay_appltime VARCHAR(20) NOT NULL DEFAULT '',
  pay_paymethod VARCHAR(30) NOT NULL DEFAULT '',
  pay_resultCode VARCHAR(10) NOT NULL DEFAULT '',
  pay_resultMsg VARCHAR(255) NOT NULL DEFAULT '',
  pay_result_map TEXT,
  pay_vact_num VARCHAR(60) NOT NULL DEFAULT '',
  pay_vact_bankcode VARCHAR(20) NOT NULL DEFAULT '',
  pay_vact_date VARCHAR(20) NOT NULL DEFAULT '',
  refund_msg VARCHAR(255) NOT NULL DEFAULT '',
  refund_time VARCHAR(20) NOT NULL DEFAULT '',
  refund_date VARCHAR(20) NOT NULL DEFAULT '',
  apply_reg_datetime DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (apply_no),
  KEY idx_ci (apply_ci),
  KEY idx_email (apply_user_email),
  KEY idx_status (apply_temp_yn, apply_pay_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

sql_query($sql);

$chk = sql_query("SHOW TABLES LIKE 'cb_unreal_2026_event2_apply'");
$exists = ($chk && $chk->num_rows > 0);
echo $exists ? "OK: cb_unreal_2026_event2_apply 생성됨\n" : "FAIL\n";

$cols = sql_query("SHOW COLUMNS FROM cb_unreal_2026_event2_apply");
if ($cols) {
    $n = 0;
    while ($r = $cols->fetch_assoc()) { $n++; }
    echo "컬럼 수: $n\n";
}
echo "이 파일은 실행 후 삭제하세요.\n";
