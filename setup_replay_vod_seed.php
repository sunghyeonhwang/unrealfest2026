<?php
/* Unreal Fest Seoul 2026 — 다시보기 Vimeo 영상 + 강연자료 PDF 일괄 등록 (1회용, 실행 후 삭제)
 * cb_unreal_2026_replay_vod 에 세션(ag_sid)별 Vimeo ID·해시·썸네일과 PDF 링크를 시딩한다.
 * 사전 조건:
 *   1) 관리자 2026_replay_vod.php 를 1회 이상 접속해 테이블이 생성되어 있어야 함.
 *   2) PDF 40개(replay/_pdf_upload/{sid}.pdf)를 Cloudflare 에 업로드한 뒤
 *      아래 $PDF_DOMAIN 에 도메인을 채워 넣어야 PDF 필드가 시딩됨(비워 두면 영상만 시딩).
 *      관리자 운영 설정의 'PDF 허용 도메인(replay_pdf_domain)'에도 같은 도메인을 넣을 것.
 * 실행: /v3/unrealfest2026/setup_replay_vod_seed.php?key=ufs2026replayseed        (미리보기)
 *       /v3/unrealfest2026/setup_replay_vod_seed.php?key=ufs2026replayseed&go=1   (실제 반영)
 * 이미 행이 있어도 안전: 값이 있는 필드만 갱신한다.
 * PHP 7.0 호환.
 */
include_once "../common.php"; // sql_query (Gnuboard DB)

$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'ufs2026replayseed') { http_response_code(403); exit('forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

$go = (isset($_GET['go']) && $_GET['go'] === '1');

/* ★ PDF 업로드 위치 — 자사 서버(epiclounge.co.kr, Cloudflare 프록시 경유) replay_pdf/ 폴더
 * {PDF_DOMAIN}/{sid}.pdf 로 링크 구성. PDF 시딩 시 replay_pdf_domain 설정도 함께 등록한다. */
$PDF_DOMAIN = 'https://epiclounge.co.kr/v3/unrealfest2026/replay_pdf';
$PDF_ALLOW_DOMAIN = 'epiclounge.co.kr';   // cb_unreal_2026_config.replay_pdf_domain 에 등록

/* 썸네일 — Figma 확정 디자인(B_언리얼페스트26 썸네일 확정)에서 추출해 서버에 업로드한 {sid}.jpg
 * SEED 배열의 vimeocdn 썸네일 값은 더 이상 사용하지 않는다(참고용). */
$THUMB_BASE = 'https://epiclounge.co.kr/v3/unrealfest2026/replay_thumb';

/* ag_sid → [vimeo id, hash, 썸네일(640x360), 메모(원본 파일명)]
 * Vimeo 폴더: Unreal Fest Seoul 2026 (30541330) — 2026-09-15 업로드 23편
 * 전 편 Embed only + 도메인 제한(epiclounge.co.kr) + 다운로드 비활성 확인 완료 */
$SEED = array(
    // Day 1 — 게임: 아트
    'd1-t1-s1' => array('1226802339', 'ae3b159b32', 'https://i.vimeocdn.com/video/2200878531-743adf37e7e3a50936c99dc1e634e488bc4d6f96bd660fa487e4bb3502eebdce-d_640x360', '김승호_뉴코어게임즈'),
    'd1-t1-s2' => array('1226802918', '2ef445a5ad', 'https://i.vimeocdn.com/video/2200879610-92ff6cdbac7b72de7f9f25dc079169f6230d0c9710f403f03290175357daed5c-d_640x360', 'Chris_day1_v2'),
    'd1-t1-s3' => array('1226802961', '222953b8ae', 'https://i.vimeocdn.com/video/2200879559-adb2ced4844bb11b0dd5ca704f1cece3084d155dbeec04c4c1fac5613925ce1b-d_640x360', 'Shao_day1_v2'),
    'd1-t1-s4' => array('1226802979', 'cef1531601', 'https://i.vimeocdn.com/video/2200879928-1b5db24377095c45a40003bc2b9365b9b7fb3978c670f5967db7bd8c37456dff-d_640x360', '강성구_그레이게임즈_v2'),
    'd1-t1-s5' => array('1226803013', 'b0aa2cbace', 'https://i.vimeocdn.com/video/2200879660-d966754dc1f70f71c65c68a25393b52c9244b65797ceab55d345803fb624a635-d_640x360', '안원철_넷마블_v2'),
    // Day 1 — 게임: 프로그래밍
    'd1-t2-s1' => array('1226803783', 'c21a584302', 'https://i.vimeocdn.com/video/2200880393-5ddbdcba95c23f62748b5da53845baabb5759e49dae31af5b27a37c9945d277a-d_640x360', '강현우_넥스트스테이지_v2'),
    'd1-t2-s2' => array('1226803822', '8e49c567c5', 'https://i.vimeocdn.com/video/2200881219-3c7f23ad59686481959347e59aa0c727516dccb9b32b8bb2988f7f5f5ca69dc6-d_640x360', 'Iso_Sega_v2'),
    'd1-t2-s3' => array('1226803889', '72bf7a903a', 'https://i.vimeocdn.com/video/2200881287-12e1bcaebf1e218494d447bc2246e69041b2d695dc072db25c9b4bfd8712c130-d_640x360', 'shinoyama_EGJ_v2'),
    'd1-t2-s4' => array('1226803915', '40224b9c31', 'https://i.vimeocdn.com/video/2200881475-f7668fc27edda5d7e6f463f4004f9eb250a2f0ea03b662b5454f7d67fe2b67bb-d_640x360', '이재호_EGK_v2'),
    'd1-t2-s5' => array('1226804149', 'a60a894308', 'https://i.vimeocdn.com/video/2200881392-6aa3c16a56bda07343b6005dc4f54de30035194fe09faafeda5a90adac49571d-d_640x360', 'Ari_day1_v2'),
    // Day 1 — 미디어 & 엔터테인먼트
    'd1-t3-s1' => array('1226805366', '931fbc0542', 'https://i.vimeocdn.com/video/2200883730-89d009e8aec079e11bdaefd931e9d60930389b5b95a0396ca8e2856ba66f61a5-d_640x360', '심성민_남기호_엑스온_v2'),
    'd1-t3-s2' => array('1226805391', 'e271641160', 'https://i.vimeocdn.com/video/2200882853-c201f9e7f4ed25f4a0aef433138b399ccee436bc50ed4ae205ace1ee7c6b25c0-d_640x360', '김지교_TRIC'),
    'd1-t3-s3' => array('1226805477', 'd446d48915', 'https://i.vimeocdn.com/video/2200883646-350e1ffdf9d5b6773455ace60e9634f6936e2a14dcfcae9231a37f132a2ad572-d_640x360', '국이경_상상방'),
    'd1-t3-s4' => array('1226805562', '03e7aeff23', 'https://i.vimeocdn.com/video/2200883050-cdfa454eb35327f1883bf40813737add6de572d6855f21f9d304f603e06102bd-d_640x360', '권도균_에이펀'),
    // Day 1 — 공통(제조 및 시뮬레이션 트랙)
    'd1-t4-s1' => array('1226807129', '703fa95d05', 'https://i.vimeocdn.com/video/2200884645-4dff040033e329b13ed18cc0247fc64386034d2904bd9f3ff66a60830eef543b-d_640x360', 'Kyle_EG_v2'),
    'd1-t4-s2' => array('1226807182', '5e0e4b0947', 'https://i.vimeocdn.com/video/2200885000-3359ddd654c1b2a731a4253a94f3b20eedc8ae48ca781126bdfedb5c47f3461a-d_640x360', 'Magnus_pt1_v2 (UEFN으로 제작하기)'),
    'd1-t4-s3' => array('1226807156', '6f0960b430', 'https://i.vimeocdn.com/video/2200886497-de1269e603237c6861d5a11f2786d2534b40d591337a80b8e4effc289083ae0f-d_640x360', '김정욱_EGK'),
    'd1-t4-s4' => array('1226807191', 'f9420084b3', 'https://i.vimeocdn.com/video/2200885013-590fc711b3545b8e9d1f9d4277d2fa47dbe3e14c179cd90177c854fae2d98a9b-d_640x360', 'Magnus_pt2_v2 (씬 그래프)'),
    'd1-t4-s5' => array('1226807647', 'e5eb58e291', 'https://i.vimeocdn.com/video/2200886117-518054c4c6adfd90bf1882a154021fdf92bafcdea068c1b44f410a446079af20-d_640x360', '박창현_EGK'),
    // Day 2 — 게임: 아트
    'd2-t1-s1' => array('1226809245', 'b7cfda3faa', 'https://i.vimeocdn.com/video/2200888151-25f53a524a085ca532365729f02c671eeed504e1edb7f7b7b4e124a1407ad216-d_640x360', '김대혁_EGK_v2'),
    // Day 2 — 게임: 프로그래밍 (2차 업로드 4편)
    'd2-t2-s1' => array('1226811422', '1c303c4429', 'https://i.vimeocdn.com/video/2200890273-36da746b5939b55dec0724dd37fc76b7d842890d6996f4f577bb6db1454e8b5a-d_640x360', '윤준기_EGK_1'),
    'd2-t2-s2' => array('1226811450', '4fa5a803f2', 'https://i.vimeocdn.com/video/2200891358-47c6a6799e59aa35588679a3144cc34a284a72142b1db68c2e9150d4085ca00d-d_640x360', 'Dmitriy_EGK_v2'),
    'd2-t2-s3' => array('1226811469', 'c11507fcfe', 'https://i.vimeocdn.com/video/2200890326-34847f6efd712792a7b20785f91b95f9ab6f5af771117148356d7afdf9af203d-d_640x360', 'Ari_day2_v2'),
    'd2-t2-s4' => array('1226811569', '522dfd5f7c', 'https://i.vimeocdn.com/video/2200891497-90314018282437f49d67f73b6a22ea4fab5745d557db25cb332b98ba4ef06bc4-d_640x360', 'Shao_day2_v2'),
    // Day 2 — M&E + 제조 (3차 업로드 8편, 일부는 트랜스코딩 중 등록)
    'd2-t3-s1' => array('1226823825', 'f7bc9c74bd', 'https://i.vimeocdn.com/video/2200905949-aba70120206c3a8bb0ae58403d13c8c9b52c2f7f004151aec3e3d68df299b101-d_640x360', 'Chris_day2_v2'),
    'd2-t3-s2' => array('1226823889', 'f14066847a', 'https://i.vimeocdn.com/video/2200905951-a1865857fcdf5559d75a2f75f2fef8e4925345aee97b3925220b058605575fdd-d_640x360', '김태환_KBS'),
    'd2-t3-s3' => array('1226823923', 'f8e76170bc', 'https://i.vimeocdn.com/video/2200905950-047ee7b799b0a029aaa78085529b22f386e1d50721bd026549ef018cc0db085e-d_640x360', '박성제_아크프레임'),
    'd2-t3-s4' => array('1226823980', 'e5e0e3fce5', 'https://i.vimeocdn.com/video/2200905844-0d98f8c8017251c0a97ceab5ff665974c327666a505f712647372d40cbe1c8a2-d_640x360', '서창규_무븐트'),
    'd2-t3-s5' => array('1226824521', 'b89ae5cf13', 'https://i.vimeocdn.com/video/2200906176-fce3f08c0d98e751eb493d80dc11b3f8f5b154475d5ae49b3cd1e1daa475a9eb-d_640x360', '이현우_블래스트'),
    'd2-t4-s1' => array('1226824543', '86fc7689fb', 'https://i.vimeocdn.com/video/2200907165-a0476d15cbd6d7eabdfabb568ea3bd3dc3e81743b59c8cce01b74e84947e570d-d_640x360', '패널토크'),
    'd2-t4-s2b' => array('1226824581', 'd0641646f6', 'https://i.vimeocdn.com/video/2200907306-47f2d717ce35b4d705ab20d733cb21bded1b66cb9e969be32590063a6988123b-d_640x360', '배정현_모빌테크'),
    'd2-t4-s3' => array('1226824587', '5d8146723d', 'https://i.vimeocdn.com/video/2200907096-f83a6b513ec9a233bff0e52172e6dfd1bf1511a5c27cc4b67e6e084afcbd55f2-d_640x360', '이은규_삼우이머션'),
    'd2-t4-s4' => array('1226824794', 'adc384a150', 'https://i.vimeocdn.com/video/2200907172-2d311b667eb5e0a3cc92e34cb21b1923ea6250462d782e38faf3b57dd779f097-d_640x360', '안윤미_테크트리'),
    'd2-t4-s5' => array('1226824881', 'b4d69002e4', 'https://i.vimeocdn.com/video/2200907377-b539a3c79a4053bf2d9519191214567566708e6be267cbf0c3f033a3f8fe5ffa-d_640x360', '김홍용_현대자동차'),
    'd2-t1-s2' => array('1226809258', '730022e663', 'https://i.vimeocdn.com/video/2200890491-0ec6bfebd14e1de4c9c3cbc56c4693a453c7b4686e6947f1cfc6b8dfcd5f5b89-d_640x360', '김동욱_전진경_룸톤'),
    'd2-t1-s3' => array('1226809482', '30fc4b0428', 'https://i.vimeocdn.com/video/2200890564-a9892ac8092d730e9ca415466d86d1e9829bc437ffc9db8965896bea3880c5c1-d_640x360', '권오찬_EGK'),
    'd2-t1-s4' => array('1226809547', '242360182c', 'https://i.vimeocdn.com/video/2200888058-598039c54f6b6b5418402dfade732ae08e39343fbd3456ce759d6491727fe7f0-d_640x360', '목영미_넥슨_v2'),
);

/* ag_sid → [표시명, 크기] — 파일은 {PDF_DOMAIN}/{sid}.pdf 로 업로드되어 있어야 함
 * 원본: replay/연사 pdf 파일/ (트랙 폴더 번호 = 세션 슬롯) → replay/_pdf_upload/{sid}.pdf 로 정리(40개)
 * ※ 게임 프로그래밍_Day2/5번 폴더 파일은 안윤미(d2-t4-s4) PDF 와 md5 동일한 중복이라 제외 */
$PDFS = array(
    'd1-t1-s1' => array('언리얼 엔진을 활용한 인디 게임 UI 콘셉트 구현 경험.pdf', '5.9MB'),
    'd1-t1-s2' => array('PCG 고급 개념과 검증된 워크플로.pdf', '8.7MB'),
    'd1-t1-s3' => array('언리얼 엔진에서 대규모 메타휴먼 군중 구현하기.pdf', '10.7MB'),
    'd1-t1-s4' => array('혼자 만드는 탑다운 익스트랙션 슈터 뎁스 맵 시야, 무버, GASP 무기 레이어링 실전기.pdf', '10.6MB'),
    'd1-t1-s5' => array('<몬길 STAR DIVE> 전학 온 TA의 서브컬처 개발 분투기.pdf', '5.0MB'),
    'd1-t2-s1' => array('언리얼 엔진 게임 개발의 AI 협업 NarshaADK 사례.pdf', '10.0MB'),
    'd1-t2-s2' => array('<소닉 레이싱 크로스월드> 에픽 온라인 서비스를 활용한 크로스플레이 구현 사례.pdf', '4.2MB'),
    'd1-t2-s3' => array('확장성 높은 멀티플랫폼 그래픽 실전 전략.pdf', '8.5MB'),
    'd1-t2-s4' => array('언리얼 엔진으로 카툰 렌더링 일곱 개의 벽 넘기.pdf', '5.7MB'),
    'd1-t2-s5' => array('프레임의 여정 언리얼 엔진의 프레임 타이밍, 동기화, 그리고 지연 시간.pdf', '4.2MB'),
    'd1-t3-s1' => array('AI 네이티브 버추얼 프로덕션 워크플로.pdf', '9.2MB'),
    'd1-t3-s2' => array('사라진 천년 도시, 서라벌을 다시 걷다 언리얼 엔진 기반 디지털 관광의 시대.pdf', '25.2MB'),
    'd1-t3-s3' => array('언리얼 엔진 리타기터로 모션 캡처 데이터 직결 파이프라인 구축하기.pdf', '5.1MB'),
    'd1-t3-s4' => array('UE5 기반 고품질 버추얼 방송국 VV World.pdf', '1.7MB'),
    // 'd1-t3-s5' => 미공개(케이팝 데몬 헌터스) — 2026-09-15 연사측 비공개 결정, 서버 PDF 삭제됨
    'd1-t4-s1' => array('에픽게임즈 스토어 2026 제품 업데이트.pdf', '1.7MB'),
    'd1-t4-s2' => array('UEFN으로 제작하기 벌스(Verse), 신규 툴, 크리에이터 워크플로.pdf', '6.2MB'),
    'd1-t4-s3' => array('탐지와 회피의 끝없는 루프 이지 안티 치트로 살펴보는 치트 진화와 대응.pdf', '1.9MB'),
    'd1-t4-s4' => array('씬 그래프 워크플로, 게임플레이, 그리고 앞으로의 방향.pdf', '6.1MB'),
    'd1-t4-s5' => array('빠르게 살펴보는 언리얼 엔진 5.8.pdf', '16.7MB'),
    'd2-t1-s1' => array('언리얼 엔진 5.8의 새로운 지형 제작 툴인 메시 터레인을 알아보자.pdf', '7.2MB'),
    'd2-t1-s2' => array('소규모 인디 팀의 시네마틱 어드벤처 게임 <인터스케이프> 개발기.pdf', '61.8MB'),
    'd2-t1-s3' => array('MHA 마커리스 모션 캡처 플러그인 이제 휴대폰으로 나만의 공격 모션을 만들어 보자.pdf', '5.6MB'),
    // 'd2-t1-s4' => PDF 미공개(목영미·우치 더 웨이페어러) — 2026-09-15 결정, 영상은 공개 유지, 서버 PDF 삭제됨
    // 'd2-t1-s5' => 미공개(<이환>) — 2026-09-15 비공개 결정, 서버 PDF 삭제됨
    'd2-t2-s1' => array('언리얼 엔진의 매스 프레임워크와 대규모 씬 처리를 위한 기능들.pdf', '5.4MB'),
    'd2-t2-s2' => array('시스테믹 셰이더 최적화 포트나이트의 셰이더를 60% 줄인 방법.pdf', '3.9MB'),
    'd2-t2-s3' => array('언리얼 엔진의 모범 사례에 대한 오해와 진실.pdf', '2.9MB'),
    'd2-t2-s4' => array('플레이어가 다녀간 자리 월드 상태 유지와 액터 LOD 시스템.pdf', '12.1MB'),
    'd2-t3-s1' => array('언리얼 엔진 5.8로 살펴보는 M&E 제작 최신 업데이트.pdf', '10.0MB'),
    'd2-t3-s2' => array('<역사스페셜 – 시간여행자>로 바라본 언리얼 엔진 버추얼 프로덕션.pdf', '12.8MB'),
    'd2-t3-s3' => array('언리얼 엔진 픽셀 스트리밍, 어디까지 가봤니?.pdf', '10.7MB'),
    'd2-t3-s4' => array('언리얼 댄스 에디터 메타휴먼으로 K-Pop 댄스 제작하기.pdf', '8.2MB'),
    'd2-t3-s5' => array('플레이브(PLAVE) 언리얼 엔진으로 확장하는 버추얼 아이돌의 세계.pdf', '3.6MB'),
    'd2-t4-s1' => array('패널 토크 인디 게임의 현재와 미래.pdf', '1.3MB'),
    // 'd2-t4-s2a' => 미공개(현대자동차 인포테인먼트) — 2026-09-15 비공개 결정, 서버 PDF 삭제됨
    'd2-t4-s2b' => array('실제 서킷을 언리얼 엔진으로 옮기기 LiDAR 스캔부터 디지털 트윈 구축까지.pdf', '16.9MB'),
    'd2-t4-s3' => array('언리얼 엔진과 스탠드얼론 HMD를 활용한 VR·MR 콘텐츠 개발.pdf', '8.2MB'),
    'd2-t4-s4' => array('게임 알고리즘을 활용한 군사 훈련 시뮬레이션 플랫폼 개발.pdf', '26.4MB'),
    'd2-t4-s5' => array('차량 3D 데이터에서 실시간 검증 콘텐츠까지.pdf', '8.1MB'),
);

$TBL = 'cb_unreal_2026_replay_vod';

$chk = sql_query("SHOW TABLES LIKE '$TBL'");
if (!$chk || $chk->num_rows === 0) exit("FAIL: $TBL 테이블이 없습니다. 관리자 2026_replay_vod.php 를 먼저 1회 접속하세요.\n");

$pdf_on = ($PDF_DOMAIN !== '');
if ($pdf_on && strpos($PDF_DOMAIN, 'https://') !== 0) exit("FAIL: PDF_DOMAIN 은 https:// 로 시작해야 합니다.\n");

echo $go ? "== 실제 반영 모드 ==\n" : "== 미리보기 모드 (반영하려면 &go=1 추가) ==\n";
echo $pdf_on ? "PDF 도메인: $PDF_DOMAIN\n\n" : "PDF 도메인 미설정 — 영상만 시딩(PDF 필드는 건너뜀)\n\n";

$sids = array_unique(array_merge(array_keys($SEED), array_keys($PDFS)));
sort($sids);

$ok = 0; $miss = array();
foreach ($sids as $sid) {
    $row = sql_fetch("SELECT ag_no, ag_title FROM cb_unreal_2026_agenda WHERE ag_sid='" . sql_real_escape_string($sid) . "' AND ag_is_active='Y'");
    if (!$row) { $miss[] = $sid; echo "MISS  $sid — agenda 에서 찾지 못함\n"; continue; }
    $agno = (int)$row['ag_no'];

    $parts = array("rv_show='Y'", "rv_upd_dt=NOW()");
    $parts[] = "rv_thumb='" . sql_real_escape_string($THUMB_BASE . '/' . $sid . '.jpg') . "'";
    $desc  = array();

    if (isset($SEED[$sid])) {
        $v = $SEED[$sid];
        $parts[] = "rv_vimeo='" . sql_real_escape_string($v[0]) . "'";
        $parts[] = "rv_vimeo_hash='" . sql_real_escape_string($v[1]) . "'";
        $parts[] = "rv_video_public='Y'";
        $parts[] = "rv_memo='" . sql_real_escape_string('seed 2026-09-15 · ' . $v[3]) . "'";
        $desc[]  = "vimeo {$v[0]}";
    }
    if ($pdf_on && isset($PDFS[$sid])) {
        $p = $PDFS[$sid];
        $url = rtrim($PDF_DOMAIN, '/') . '/' . $sid . '.pdf';
        $parts[] = "rv_pdf_url='" . sql_real_escape_string($url) . "'";
        $parts[] = "rv_pdf_name='" . sql_real_escape_string($p[0]) . "'";
        $parts[] = "rv_pdf_size='" . sql_real_escape_string($p[1]) . "'";
        $parts[] = "rv_pdf_public='Y'";
        $desc[]  = "pdf {$p[1]}";
    }
    if (count($desc) === 0) { echo "SKIP  $sid — 시딩할 값 없음\n"; continue; }

    echo str_pad($go ? 'SEED' : 'PLAN', 5) . " $sid (#$agno) {$row['ag_title']} ← " . implode(' + ', $desc) . "\n";
    if (!$go) { $ok++; continue; }

    $set = implode(', ', $parts);
    $ex = sql_fetch("SELECT rv_agno FROM $TBL WHERE rv_agno=$agno");
    if ($ex) sql_query("UPDATE $TBL SET $set WHERE rv_agno=$agno");
    else     sql_query("INSERT INTO $TBL SET rv_agno=$agno, $set");
    $ok++;
}

/* PDF 허용 도메인 설정 등록 (pdf.php 의 도메인 화이트리스트) */
if ($go && $pdf_on && isset($PDF_ALLOW_DOMAIN) && $PDF_ALLOW_DOMAIN !== '') {
    $ck = sql_real_escape_string('replay_pdf_domain');
    $cv = sql_real_escape_string($PDF_ALLOW_DOMAIN);
    $e = sql_fetch("SELECT cfg_key FROM cb_unreal_2026_config WHERE cfg_key='$ck'");
    if ($e) sql_query("UPDATE cb_unreal_2026_config SET cfg_val='$cv' WHERE cfg_key='$ck'");
    else    sql_query("INSERT INTO cb_unreal_2026_config (cfg_key,cfg_val) VALUES ('$ck','$cv')");
    echo "\nCONF  replay_pdf_domain = $PDF_ALLOW_DOMAIN\n";
}

echo "\n완료: " . count($sids) . "건 중 " . ($go ? "반영 $ok" : "매칭 $ok") . "건" . ($miss ? ', 미매칭 ' . count($miss) . '건(' . implode(', ', $miss) . ')' : '') . "\n";
echo "\n주의:\n";
echo " - 영상 " . count($SEED) . "편 / PDF " . count($PDFS) . "건. 공개 플래그를 Y 로 넣었지만 실제 노출은 운영 설정의 서비스 활성화(replay_enabled)가 켜져야 시작됩니다.\n";
echo " - PDF 는 replay/_pdf_upload/{sid}.pdf 40개를 Cloudflare 에 올린 뒤 \$PDF_DOMAIN 을 채우고 다시 실행하면 PDF 필드만 추가 갱신됩니다.\n";
echo " - 관리자 2026_replay_vod.php 에서 재생·링크 테스트 후, 이 파일은 삭제하세요.\n";
