<?php
$sub_menu = "700310";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

// 테이블 존재 확인 및 생성
$check_table = sql_query("SHOW TABLES LIKE 'cb_unreal_2026_speaker_apply'", false);
if (!sql_num_rows($check_table)) {
    // 테이블 직접 생성
    $sql_create = "CREATE TABLE `cb_unreal_2026_speaker_apply` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `speaker_name` varchar(100) DEFAULT NULL,
        `speaker_email` varchar(200) DEFAULT NULL,
        `speaker_ph` varchar(50) DEFAULT NULL,
        `speaker_cp` varchar(200) DEFAULT NULL,
        `speaker_cp_j` varchar(100) DEFAULT NULL,
        `speaker_pic` varchar(255) DEFAULT NULL,
        `speaker_hi` text,
        `industry` varchar(500) DEFAULT NULL,
        `product` varchar(500) DEFAULT NULL,
        `topic` varchar(500) DEFAULT NULL,
        `platform` varchar(500) DEFAULT NULL,
        `level` varchar(200) DEFAULT NULL,
        `speaker_table` text,
        `speaker_session` varchar(500) DEFAULT NULL,
        `speaker_takeaway` text,
        `speaker_target` text,
        `speaker_key` text,
        `speaker_reference` text,
        `demo` varchar(50) DEFAULT NULL,
        `does_demo` varchar(500) DEFAULT NULL,
        `speaker_version` varchar(200) DEFAULT NULL,
        `pdf_consent` varchar(50) DEFAULT NULL,
        `video_consent` varchar(50) DEFAULT NULL,
        `speaker_requests` text,
        `agree_text1` tinyint(1) DEFAULT '0',
        `agree_text2` tinyint(1) DEFAULT '0',
        `additional_speakers` text COMMENT '추가 발표자 정보(JSON)',
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_email` (`speaker_email`),
        KEY `idx_phone` (`speaker_ph`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    @sql_query($sql_create);
} else {
    // 테이블이 있으면 additional_speakers 컬럼 존재 여부 확인
    $check_column = sql_query("SHOW COLUMNS FROM cb_unreal_2026_speaker_apply LIKE 'additional_speakers'", false);
    if (!sql_num_rows($check_column)) {
        @sql_query("ALTER TABLE cb_unreal_2026_speaker_apply ADD COLUMN additional_speakers TEXT NULL COMMENT '추가 발표자 정보(JSON)'");
    }
    // speaker_type 컬럼 존재 여부 확인
    $check_column2 = sql_query("SHOW COLUMNS FROM cb_unreal_2026_speaker_apply LIKE 'speaker_type'", false);
    if (!sql_num_rows($check_column2)) {
        @sql_query("ALTER TABLE cb_unreal_2026_speaker_apply ADD COLUMN speaker_type VARCHAR(20) DEFAULT 'external' COMMENT '모집 유형(external/internal)'");
    }
}

// 새 테이블 사용
$sql_common = " from cb_unreal_2026_speaker_apply a ";
// 유효 신청만 집계(외부/내부). NULL/기타 값은 제외 — DB는 변경하지 않음.
$sql_search = " where a.speaker_type in ('external','internal') ";

// 검색어 조건
if ($stx) {
    $stx = clean_xss_tags($stx);
    $sql_search .= " and ( ";
    if ($sfl) {
        // 검색 필드 매핑 및 보안
        $allowed_fields = ['speaker_name', 'speaker_email', 'speaker_ph'];
        if ($sfl == 'apply_user_name') {
            $sfl = 'speaker_name';
        } elseif ($sfl == 'apply_user_email') {
            $sfl = 'speaker_email';
        } elseif ($sfl == 'apply_user_phone') {
            $sfl = 'speaker_ph';
        }
        
        if (!in_array($sfl, $allowed_fields)) {
            $sfl = 'speaker_name';
        }
        
        $sql_search .= " ($sfl like '%$stx%') ";
    }
    $sql_search .= " ) ";
}

// 날짜 필터링
if ($start_date) {
    $start_date = clean_xss_tags($start_date);
    $sql_search .= " and a.created_at > '$start_date'";
}
if ($end_date) {
    $end_date = clean_xss_tags($end_date);
    $sql_search .= " and a.created_at < '$end_date 23:59:59'";
}

// 모집 유형 필터링 (external/internal)
$speaker_type_filter = isset($_GET['speaker_type']) ? clean_xss_tags($_GET['speaker_type']) : '';
if ($speaker_type_filter && in_array($speaker_type_filter, ['external', 'internal'])) {
    $sql_search .= " and a.speaker_type = '$speaker_type_filter'";
}

// 유형별 카운트 조회
$cnt_all = sql_fetch("SELECT COUNT(*) as cnt FROM cb_unreal_2026_speaker_apply WHERE speaker_type in ('external','internal')");
$cnt_external = sql_fetch("SELECT COUNT(*) as cnt FROM cb_unreal_2026_speaker_apply WHERE speaker_type = 'external'");
$cnt_internal = sql_fetch("SELECT COUNT(*) as cnt FROM cb_unreal_2026_speaker_apply WHERE speaker_type = 'internal'");

$qstr = "&amp;stx=" . urlencode($stx) . "&amp;sfl=" . $sfl;
if ($start_date) $qstr .= "&amp;start_date=" . urlencode($start_date);
if ($end_date) $qstr .= "&amp;end_date=" . urlencode($end_date);
if ($speaker_type_filter) $qstr .= "&amp;speaker_type=" . urlencode($speaker_type_filter);

// 정렬
$sst  = "a.id";
$sod = "desc";
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

if (!$rows) {
    $rows = 51;
}
$total_page  = ceil($total_count / $rows);
if ($page < 1) { $page = 1; }
$from_record = ($page - 1) * $rows;

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$g5['title'] = '2026 스피커 신청 리스트';
include_once('./admin.head.php');

$colspan = 12;
?>
<!-- 부트스트랩, 폰트 등 외부 CSS/JS 로드 -->
<link rel="stylesheet" type="text/css" href="https://epiclounge.co.kr/cib/assets/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="https://epiclounge.co.kr/cib/assets/css/bootstrap-theme.min.css" />
<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="https://epiclounge.co.kr/cib/assets/css/datepicker3.css" />
<link rel="stylesheet" type="text/css" href="https://epiclounge.co.kr/cib/views/admin/basic/css/style.css" />
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/earlyaccess/nanumgothic.css" />

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/bootstrap-datepicker.kr.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/jquery.validate.extension.js"></script>
<script type="text/javascript" src="https://epiclounge.co.kr/cib/assets/js/common.js"></script>

<style>
    h1 {
        font-weight: bold;
        font-size: 1.5em;
        font-family: 'Malgun Gothic', "맑은 고딕", AppleGothic, Dotum, "돋움", sans-serif;
    }
    body table td {
        font-size: 12px;
    }
    .vm-summary {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .vm-summary-item {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .vm-summary-item h3 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        color: #6b7280;
    }
    .vm-summary-number {
        margin: 0;
        font-size: 1.8rem;
        font-weight: bold;
        color: #1f2937;
    }
    .search-form-group {
        margin: 1rem 0;
        padding: 1rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .type-filter-tabs {
        display: flex;
        gap: 0;
        margin: 1rem 0;
    }
    .type-filter-tabs a {
        padding: 10px 24px;
        text-decoration: none;
        color: #6b7280;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        font-weight: 500;
        font-size: 14px;
    }
    .type-filter-tabs a:first-child { border-radius: 6px 0 0 6px; }
    .type-filter-tabs a:last-child { border-radius: 0 6px 6px 0; }
    .type-filter-tabs a.active {
        background: #1f2937;
        color: white;
        border-color: #1f2937;
    }
    .badge-external {
        display: inline-block;
        padding: 2px 8px;
        background: #3b82f6;
        color: white;
        border-radius: 10px;
        font-size: 11px;
    }
    .badge-internal {
        display: inline-block;
        padding: 2px 8px;
        background: #f97316;
        color: white;
        border-radius: 10px;
        font-size: 11px;
    }
</style>

<div class="vm-summary">
    <div class="vm-summary-item">
        <h3>전체 신청</h3>
        <p class="vm-summary-number"><?php echo number_format($cnt_all['cnt']); ?></p>
    </div>
    <div class="vm-summary-item">
        <h3>외부 모집</h3>
        <p class="vm-summary-number" style="color: #3b82f6;"><?php echo number_format($cnt_external['cnt']); ?></p>
    </div>
    <div class="vm-summary-item">
        <h3>내부 모집</h3>
        <p class="vm-summary-number" style="color: #f97316;"><?php echo number_format($cnt_internal['cnt']); ?></p>
    </div>
</div>

<div class="type-filter-tabs">
    <a href="2026_event_speaker.php" class="<?php echo !$speaker_type_filter ? 'active' : ''; ?>">전체 (<?php echo number_format($cnt_all['cnt']); ?>)</a>
    <a href="2026_event_speaker.php?speaker_type=external" class="<?php echo $speaker_type_filter == 'external' ? 'active' : ''; ?>">외부 (<?php echo number_format($cnt_external['cnt']); ?>)</a>
    <a href="2026_event_speaker.php?speaker_type=internal" class="<?php echo $speaker_type_filter == 'internal' ? 'active' : ''; ?>">내부 (<?php echo number_format($cnt_internal['cnt']); ?>)</a>
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch search-form-group" method="get">
    <?php if ($speaker_type_filter) { ?>
    <input type="hidden" name="speaker_type" value="<?php echo htmlspecialchars($speaker_type_filter); ?>">
    <?php } ?>
    <div class="local_ov01 local_ov">
        <a href="2026_event_speaker.php" class="btn btn_03 ft_11">전체 <?php echo number_format($total_count); ?>명</a>
    </div>
    
    <label for="start_date">등록일 검색:</label>
    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="frm_input">
    ~
    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="frm_input">
    
    <select name="sfl">
        <option value="apply_user_name" <?php if($sfl=='speaker_name' || $sfl=='apply_user_name'){?>selected<?php } ?>>이름</option>
        <option value="apply_user_email" <?php if($sfl=='speaker_email' || $sfl=='apply_user_email'){?>selected<?php } ?>>이메일</option>
        <option value="apply_user_phone" <?php if($sfl=='speaker_ph' || $sfl=='apply_user_phone'){?>selected<?php } ?>>전화번호</option>
    </select>
    
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" id="stx" class="frm_input" placeholder="검색어 입력">
    
    <input type="submit" value="검색" class="btn_submit">
    
    <button type="button" class="btn btn-outline btn-success btn-sm" id="export_to_excel">
        <i class="fa fa-file-excel-o"></i> 엑셀 다운로드
    </button>
</form>

<p>검색된 리스트 : 총(<?php echo number_format($total_count); ?>명)</p>

<form name="fboardlist" id="fboardlist" action="#b" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    
    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <colgroup>
                <col style="width:70px">
                <col style="width:70px">
                <col style="width:110px">
                <col style="width:70px">
                <col style="width:100px">
                <col style="width:300px">
                <col style="width:70px">
                <col style="width:70px">
                <col style="width:200px">
                <col style="width:200px">
                <col style="width:70px">
                <col style="width:70px">
            </colgroup>
            <thead>
            <!-- 첫 번째 행 -->
            <tr>
                <th scope="col" rowspan="3">번호</th>
                <th scope="col">이름</th>
                <th scope="col">이메일</th>
                <th scope="col">연락처</th>
                <th scope="col">회사</th>
                <th scope="col" colspan="2">분야</th>
                <th scope="col">난이도</th>
                <th scope="col">주제</th>
                <th scope="col">플랫폼</th>
                <th scope="col">가입일</th>
                <th scope="col" rowspan="3">삭제</th>
            </tr>
            <!-- 두 번째 행 -->
            <tr>
                <th scope="col" colspan="2">약력</th>
                <th scope="col" colspan="2">세션제목</th>
                <th scope="col" colspan="2">세션소개</th>
                <th scope="col" colspan="2">요청사항</th>
                <th scope="col" colspan="2">스피커이미지</th>
            </tr>
            <!-- 세 번째 행 -->
            <tr>
                <th scope="col" colspan="2">제품군</th>
                <th scope="col" colspan="2">청강대상</th>
                <th scope="col" colspan="2">발표자료PDF</th>
                <th scope="col" colspan="2">발표영상</th>
                <th scope="col" colspan="2">목차</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i = 1; $row = sql_fetch_array($result); $i++) {
                $bg = 'bg' . ($i % 2);
                $number = $total_count + 1 - ($i + (($page - 1) * $rows));
                ?>
                <!-- 1행 -->
                <tr class="<?php echo $bg; ?>">
                    <td class="td_chk" rowspan="3"><?php echo $number; ?></td>
                    <td><?php echo htmlspecialchars($row['speaker_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['speaker_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['speaker_ph']); ?></td>
                    <td>
                        <?php 
                        echo htmlspecialchars($row['speaker_cp']); 
                        if (!empty($row['speaker_cp_j'])) {
                            echo " (" . htmlspecialchars($row['speaker_cp_j']) . ")";
                        }
                        ?>
                    </td>
                    <td colspan="2"><?php echo htmlspecialchars($row['industry']); ?></td>
                    <td><?php echo htmlspecialchars($row['level']); ?></td>
                    <td><?php echo htmlspecialchars($row['topic']); ?></td>
                    <td><?php echo htmlspecialchars($row['platform']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($row['created_at']); ?><br>
                        <?php
                        $type = $row['speaker_type'] ?? 'external';
                        if ($type == 'internal') {
                            echo '<span class="badge-internal">내부</span>';
                        } else {
                            echo '<span class="badge-external">외부</span>';
                        }
                        ?>
                    </td>
                    <td rowspan="3">
                        <a style="font-weight: 900; color: #dc3545; text-decoration: none;" 
                           href="javascript:;" 
                           onclick="del(<?php echo (int)$row['id']; ?>)">
                            삭제
                        </a>
                    </td>
                </tr>
                <!-- 2행 -->
                <tr class="<?php echo $bg; ?>">
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_hi'])); ?></td>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_session'])); ?></td>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_takeaway'])); ?></td>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_requests'])); ?></td>
                    <td colspan="2">
                        <?php
                        if (!empty($row['speaker_pic'])) {
                            $pic_url = '/v3/data/file/speak/' . htmlspecialchars($row['speaker_pic']);
                            echo '<a href="' . $pic_url . '" target="_blank" style="color: #0d6efd;">스피커이미지 보기</a>';
                        } else {
                            echo '<span style="color: #6c757d;">없음</span>';
                        }
                        ?>
                    </td>
                </tr>
                <!-- 3행 -->
                <tr class="<?php echo $bg; ?>">
                    <td colspan="2"><?php echo htmlspecialchars($row['product']); ?></td>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_target'])); ?></td>
                    <td colspan="2"><?php echo !empty($row['pdf_consent']) ? "예" : "아니오"; ?></td>
                    <td colspan="2"><?php echo !empty($row['video_consent']) ? "예" : "아니오"; ?></td>
                    <td colspan="2"><?php echo nl2br(htmlspecialchars($row['speaker_table'])); ?></td>
                </tr>
                <?php
                // 추가 발표자 정보 표시
                if (!empty($row['additional_speakers'])) {
                    $additional_speakers = json_decode($row['additional_speakers'], true);
                    if (is_array($additional_speakers) && count($additional_speakers) > 0) {
                        foreach ($additional_speakers as $idx => $speaker) {
                ?>
                <tr class="<?php echo $bg; ?>" style="background-color: #f0f8ff;">
                    <td style="text-align: center; font-weight: bold; color: #0078d4;">추가 <?php echo $idx + 1; ?></td>
                    <td><?php echo htmlspecialchars($speaker['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($speaker['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($speaker['phone'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($speaker['company'] ?? '') . ' (' . htmlspecialchars($speaker['title'] ?? '') . ')'; ?></td>
                    <td colspan="5"><?php echo nl2br(htmlspecialchars($speaker['bio'] ?? '')); ?></td>
                    <td>
                        <?php
                        if (!empty($speaker['pic'])) {
                            $pic_url = '/v3/data/file/speak/' . htmlspecialchars($speaker['pic']);
                            echo '<a href="' . $pic_url . '" target="_blank" style="color: #0d6efd;">이미지 보기</a>';
                        } else {
                            echo '<span style="color: #6c757d;">없음</span>';
                        }
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <?php
                        }
                    }
                }
                ?>
                <tr style="height:5px;"><td colspan="<?php echo $colspan; ?>"></td></tr>
                <?php
            }
            if ($i == 1)
                echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
            ?>
            </tbody>
        </table>
    </div>
    
    <style>
        .pagination { width: 100%; margin: auto; text-align: center; }
        .pagination ul { width: 520px; margin: auto; text-align: center; }
        .pagination li { float: left; margin: 10px; }
        .pagination li .active { font-weight: 800; }
    </style>
    
    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
</form>

<form class="form-inline" name="fsearch2" action="https://epiclounge.co.kr/v3/adm/2026_speaker_export_excel.php" method="get">
    <input type="hidden" name="export" value="">
    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
    <input type="hidden" name="stx" value="<?php echo htmlspecialchars($stx); ?>">
    <input type="hidden" name="sfl" value="<?php echo htmlspecialchars($sfl); ?>">
    <input type="hidden" name="speaker_type" value="<?php echo htmlspecialchars($speaker_type_filter); ?>">
</form>

<script type="text/javascript">
$(document).ready(function() {
    // 엑셀 다운로드
    $('#export_to_excel').on('click', function() {
        var f = document.fsearch2;
        f.export.value = "excel";
        f.submit();
        f.export.value = "";
    });
});

function del(val) {
    if (!val || isNaN(val)) {
        alert('잘못된 요청입니다.');
        return false;
    }
    
    if (confirm('정말 삭제하시겠습니까?\n\n삭제된 데이터는 복구할 수 없습니다.')) {
        location.replace('2026_event_speaker_proc.php?mode2=del&no=' + val);
    }
}
</script>

<?php
include_once('./admin.tail.php');
?>
