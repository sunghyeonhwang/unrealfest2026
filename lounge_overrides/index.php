<?php

// 현재 호스트명이 www.epiclounge.co.kr 인지 확인
if ($_SERVER['HTTP_HOST'] === 'www.epiclounge.co.kr') {
  // 강제로 epiclounge.co.kr로 301 리디렉션
  $redirect = 'https://epiclounge.co.kr' . $_SERVER['REQUEST_URI'];
  header('Location: ' . $redirect, true, 301);
  exit();
}

/* ── 언리얼 페스트 서울 2026 기간 한정: 홈 접속 → 페스트 페이지로 (임시 302). 기간 밖엔 자동 해제. ── */
$ufs_rd_start = '2026-06-23 00:00:00';
$ufs_rd_end   = '2026-08-21 23:59:59';
$ufs_rd_now   = date('Y-m-d H:i:s');
$ufs_rd_uri = strtok($_SERVER['REQUEST_URI'], '?');  // 쿼리 제거
$ufs_rd_isroot = ($ufs_rd_uri === '/' || $ufs_rd_uri === '' || $ufs_rd_uri === '/v3/');
if ($ufs_rd_isroot && $ufs_rd_now >= $ufs_rd_start && $ufs_rd_now <= $ufs_rd_end) {  // 맨 끝 루트 접속만 (/index.php·/v3/index.php는 평소 홈 유지)
  header('Location: https://epiclounge.co.kr/unrealfest2026/index.php', true, 302);
  exit();
}

$g5_path = '.';
include_once ('./_common.php');
include_once ('./lib/latest.lib.php');

// SEO 및 마케팅 설정 가져오기
$v3_seo = sql_fetch(" SELECT * FROM v3_seo_config WHERE seo_page = 'default' ");
$seo_title = ($v3_seo['seo_title']) ? $v3_seo['seo_title'] : "에픽 라운지";
$seo_description = ($v3_seo['seo_description']) ? $v3_seo['seo_description'] : "";
$seo_keywords = ($v3_seo['seo_keywords']) ? $v3_seo['seo_keywords'] : "";
$seo_og_image = ($v3_seo['seo_og_image']) ? $v3_seo['seo_og_image'] : "";

$seo_ga_id = trim($v3_seo['seo_ga_id']);
$seo_gtm_id = trim($v3_seo['seo_gtm_id']);
$seo_pixel_id = trim($v3_seo['seo_pixel_id']);
$seo_kakao_pixel_id = trim($v3_seo['seo_kakao_pixel_id']);
$seo_naver_verif = trim($v3_seo['seo_naver_verif']);
$seo_google_verif = trim($v3_seo['seo_google_verif']);
$seo_extra_head = $v3_seo['seo_extra_head'];
$seo_extra_body = $v3_seo['seo_extra_body'];

?>

<!DOCTYPE html>
<html lang="ko">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <?php include 'inc/marketing_head.php'; ?>
  
  <link rel="mask-icon" href="https://unrealsummit16.cafe24.com/2025/ufest25/images/mask-icon.svg" color="#424242">
  <link rel="apple-touch-icon" sizes="57x57" href="/v3/favicon/v3/favicon/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/v3/favicon/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/v3/favicon/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/v3/favicon/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/v3/favicon/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/v3/favicon/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/v3/favicon/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/v3/favicon/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/v3/favicon/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/v3/favicon/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/v3/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/v3/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/v3/favicon/favicon-16x16.png">
  <link rel="manifest" href="/v3/favicon/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/v3/favicon/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <title><?php echo get_text($seo_title); ?></title>
</head>

<body>
<?php include 'inc/marketing_body.php'; ?>
  <style>
    #quick_banner {
      display: none
    }
  </style>
  <link rel="stylesheet" href="/v3/resource/css/main26.css">
  <style>
    /* Prevent SplitText flicker */
    .bg_slide_box .bg_slide_title, 
    .bg_slide_box .bg_slide_text {
      visibility: hidden;
      opacity: 0;
    }
  </style>

  <script src="/v3/resource/js/jquery-3.4.1.min.js"></script>
  <script src="/v3/resource/js/slick.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="/v3/resource/js/ScrollTrigger.min.js"></script>
  <script src="/v3/resource/js/jquery.menu.min.js"></script>
  <script src="/v3/resource/js/jquery.responsive.min.js"></script>
  <script src="/v3/resource/js/common26.js?v=20260209"></script>
  <script src="/v3/resource/js/main26.js"></script>

  <style>
    /* Cinematic Hero Styles */
    .cinematic-hero {
      position: relative;
      width: 100%;
      height: 765px; /* 815px - 50px */
      margin-top: 80px;
      overflow: hidden;
      background: #000;
      z-index: 1;
    }

    .swiper-slide {
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .hero-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    .hero-bg video, .hero-bg img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transform: scale(1);
    }



    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.4);
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 10;
      text-align: center;
      color: #fff;
      max-width: 900px;
      padding: 0 20px;
    }

    .hero-title {
      font-size: 80px;
      font-weight: 900;
      margin-bottom: 20px;
      letter-spacing: -2px;
      line-height: 1.1;
      opacity: 0;
      height: 420px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .hero-title img {
        height: auto;
    }



    .hero-cta {
      opacity: 0;
    }

    .hero-cta a {
      display: inline-block;
      padding: 14px 60px;
      border-radius: 10px;
      background: transparent;
      border: 1px solid #A7A7A7;
      color: #fff;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .hero-cta a:hover {
      background: #fff;
      color: #000;
      transform: translateY(-3px);
    }

    /* Navigation Custom */
    .swiper-button-next, .swiper-button-prev {
      color: #fff;
      opacity: 0.5;
      transition: 0.3s;
    }
    .swiper-button-next:hover, .swiper-button-prev:hover { opacity: 1; }
    
    /* Custom Pagination (Simple Dots) */
    .swiper-custom-pagination {
      position: absolute;
      bottom: 50px !important;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: center;
      z-index: 1000;
      pointer-events: auto; /* 클릭 차단 해제 */
    }
    .swiper-custom-pagination .swiper-pagination-bullet {
      width: 12px;
      height: 12px;
      background: #888; /* 회색 */
      border-radius: 50%;
      opacity: 1;
      margin: 0 4px !important;
      cursor: pointer;
      transition: background-color 0.3s ease;
      /* 클릭 영역 확장: 투명 테두리를 주어 실제 클릭 가능한 범위를 넓힘 */
      border: 8px solid transparent;
      background-clip: padding-box;
    }
    .swiper-custom-pagination .swiper-pagination-bullet:hover,
    .swiper-custom-pagination .swiper-pagination-bullet-active {
      background-color: #fff; /* background 단축 속성 대신 background-color 사용으로 clip 속성 유지 */
      opacity: 1;
    }

    @media (max-width: 768px) {

      /* [Fix 1] 햄버거 아이콘 다크모드 — 검은 라인 → 흰색 */
      body.dark-theme .lnb_m_nav .open .line { background: #fff !important; }

      /* [Fix 5] 모바일 메뉴 z-index — hero 위로 올리기 */
      #header { z-index: 1100 !important; }
      #header #lnb { z-index: 1200 !important; }

      /* Hero */
      .cinematic-hero { height: 500px; margin-top: 60px; }
      .hero-title { font-size: 40px; height: 280px; }
      .hero-title img { max-width: 280px; }
      .hero-cta a { padding: 10px 40px; font-size: 14px; }
      .swiper-button-prev, .swiper-button-next { display: none; }

      /* [Fix 2] Hero-News 간격 제거 — 레거시 margin-top 무효화 */
      .news_sec { margin-top: 0 !important; padding-bottom: 60px; }

      /* [Fix 4] 섹션 타이틀 통일 — 모든 섹션 동일 레이아웃 */
      .news_sec .sec_title,
      .event_sec .sec_title,
      .resource_list .resource_title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 40px 0 20px;
        text-align: left;
      }
      .news_sec .sec_title h2,
      .event_sec .sec_title h2,
      .resource_list .resource_title h2 { font-size: 22px; margin: 0; }
      .news_sec .sec_title a,
      .event_sec .sec_title a,
      .resource_list .resource_title a {
        position: static !important;
        display: inline-block;
        margin-top: 0;
        flex-shrink: 0;
        font-size: 13px;
        padding: 4px 12px;
      }
      .event_sec .sec_title a { color: #aaa; }

      /* [Fix 3] 뉴스 카드 — 가로 스크롤 스냅 (카드형 캐러셀) */
      .news_sec .con {
        width: 100%;
        margin: 0;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        scroll-padding: 0 16px;
        padding: 0 0 12px;
      }
      .news_sec .con::-webkit-scrollbar { display: none; }
      .news_sec .con .scroll_box {
        width: max-content;
        display: flex;
        flex-direction: row;
        gap: 12px;
        padding: 0 16px;
      }
      .news_sec .con .list_box {
        width: 280px;
        flex-shrink: 0;
        padding: 0;
        scroll-snap-align: start;
        aspect-ratio: 3 / 4;
      }
      .news_sec .con .list_box .img_box { border-radius: 12px; aspect-ratio: 3 / 4; }
      .news_sec .con .list_box .news_text {
        padding: 0 16px 16px;
        bottom: 0;
      }
      .news_sec .con .list_box .news_text .news_text_title { font-size: 12px; }
      .news_sec .con .list_box .news_text .news_text_info { font-size: 14px; margin: 4px 0 0; line-height: 1.3; }

      /* BG Slide section mobile */
      .bg_slide_list_box { height: 380px !important; }
      .bg_slide_box .slick-slide { height: 380px !important; }
      .bg_slide_box .bg_slide_list1 .bg_slide_title,
      .bg_slide_box .bg_slide_list2 .bg_slide_title { font-size: 24px; margin: 0 auto 12px; }
      .bg_slide_box .bg_slide_list1 .bg_slide_text,
      .bg_slide_box .bg_slide_list2 .bg_slide_text {
        font-size: 14px;
        padding: 0 20px;
        word-break: keep-all;
        line-height: 1.5;
      }
      .bg_slide_box .bg_slide_list1 .bg_slide_text br,
      .bg_slide_box .bg_slide_list2 .bg_slide_text br { display: none; }
      .bg_slide_box .bg_slide_list1 .bg_slide_btn,
      .bg_slide_box .bg_slide_list2 .bg_slide_btn { margin-top: 24px; }
      .bg_slide_box .bg_slide_list1 .bg_slide_btn a,
      .bg_slide_box .bg_slide_list2 .bg_slide_btn a {
        width: 160px; height: 36px; line-height: 34px; font-size: 14px;
      }
      .bg_slide_box .slider-dots-box { bottom: 20px; }

      /* Event section mobile */
      .event_sec { padding: 60px 0; }

      /* Resource section mobile — 1열 */
      .resource_list .con { width: 100%; margin: 0 0 40px 0; }
      .resource_list .con .list_box {
        width: 100%;
        padding: 0;
        margin: 0 0 20px;
      }
      .resource_list .con .list_box .img_box { border-radius: 8px; }
      .resource_list .con .list_box .text_box { padding: 12px 0; }
      .resource_list .con .list_box .text_box .title { font-size: 16px; }
      .resource_list .con .list_box .text_box .text { font-size: 13px; }

      /* ===== 풀스크린 모바일 메뉴 ===== */

      /* 1) #lnb 풀스크린 오버레이 */
      #header #lnb {
        width: 100vw !important;
        right: auto !important;
        left: 0 !important;
        top: 0 !important;
        height: 100vh !important;
        background: #0a0a0e !important;
        transform: translateX(100%);
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto !important;
        padding: 0 24px 40px !important;
        display: flex !important;
        flex-direction: column !important;
      }

      /* 2) 열린 상태 */
      #header #lnb.v4-menu-open {
        transform: translateX(0) !important;
      }

      /* 3) 로고 영역 */
      #header .m_logo {
        margin: 20px 0 !important;
        text-align: left !important;
      }
      #header .m_logo a.logo_img img {
        width: 140px !important;
      }

      /* 4) 검색바 다크 스타일 */
      .m_top_search_btn_box .top_search_box {
        background: rgba(255,255,255,0.08) !important;
        border: 1px solid rgba(255,255,255,0.15) !important;
        border-radius: 12px !important;
        margin: 0 0 24px !important;
        width: 100% !important;
        padding: 12px 16px !important;
      }
      .m_top_search_btn_box .top_search_box input {
        color: #fff !important;
        background: transparent !important;
      }
      .m_top_search_btn_box .top_search_box input::placeholder {
        color: rgba(255,255,255,0.4);
      }
      .m_top_search_btn_box .top_search_box button img {
        filter: invert(1) brightness(2);
      }
      .m_top_search_btn_box .top_btn2 {
        display: none !important;
      }

      /* 5) 메인 메뉴 */
      #lnb .top1menu {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        flex: 1;
      }
      #lnb .top1menu > li {
        border-bottom: 1px solid rgba(255,255,255,0.1) !important;
        background: transparent !important;
      }
      #lnb .top1menu .depth1_ti {
        padding: 18px 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
      }
      #lnb .top1menu .depth1_ti span {
        font-size: 22px !important;
        font-weight: 700 !important;
        letter-spacing: -0.02em;
      }
      /* 메뉴 화살표 (CSS pseudo-element) */
      #lnb .top1menu .depth1_ti::after {
        content: '' !important;
        width: 10px; height: 10px;
        border-right: 2px solid rgba(255,255,255,0.5);
        border-bottom: 2px solid rgba(255,255,255,0.5);
        transform: rotate(45deg);
        transition: transform 0.25s ease;
        display: block !important;
        position: static !important;
        background: none !important;
      }
      #lnb .top1menu .depth1.v4-sub-open .depth1_ti::after {
        transform: rotate(-135deg);
      }

      /* 6) 서브메뉴 복원 — common26.css의 display:none!important 오버라이드 */
      #lnb .top1menu .depth1.v4-sub-open > div {
        display: block !important;
      }
      #lnb ul .top2m,
      #header #lnb .top1menu .depth1 > div.top2m,
      #lnb .top1menu .depth1 > div,
      #lnb .top1menu .depth1 > div > .menu_bg,
      #lnb .top1menu .depth1 > div > div {
        position: static !important;
        width: 100% !important;
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 0 12px 16px !important;
        height: auto !important;
      }
      #lnb .lnb_title_box { display: none !important; }
      #lnb .lnb_banner { display: none !important; }
      #lnb .lnb_menu_box { margin: 0 !important; padding: 0 !important; width: 100% !important; }
      #lnb .depth2 { width: 100% !important; }

      /* 7) 서브메뉴 depth2 스타일 */
      #lnb .depth2 {
        display: flex !important;
        flex-direction: column !important;
        gap: 0 !important;
      }
      #lnb .depth2 > li {
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
      }
      #lnb .depth2 > li > a {
        color: rgba(255,255,255,0.65) !important;
        font-size: 16px !important;
        padding: 10px 0 !important;
        display: block !important;
        background: transparent !important;
      }
      #lnb .depth2 > li > a:hover {
        color: #33aeec !important;
      }

      /* 8) 닫기 버튼 — #lnb 내부 우상단 */
      .lnb_close {
        position: fixed !important;
        right: 24px !important;
        top: 20px !important;
        left: auto !important;
        background: transparent !important;
        z-index: 10001 !important;
        display: none !important;
      }
      body.v4-menu-active .lnb_close {
        display: block !important;
      }
      .lnb_close button {
        width: 44px !important;
        height: 44px !important;
      }
      .lnb_close button:before,
      .lnb_close button:after {
        background-color: #fff !important;
      }

      /* 9) 마스크 */
      .mask {
        display: none !important;
      }

      /* 10) 햄버거 버튼 */
      .lnb_m_nav {
        z-index: 1100 !important;
      }
      .lnb_m_nav .open .line {
        background: #fff !important;
      }

      /* 11) 메뉴 하단 다크모드 토글 */
      .v4-menu-footer {
        padding: 24px 0 0;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: auto;
      }
      .v4-theme-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
      }
      .v4-theme-toggle-row > span {
        font-size: 15px;
        font-weight: 500;
        color: rgba(255,255,255,0.7);
      }
      .v4-theme-switch {
        position: relative;
        width: 52px;
        height: 28px;
        background: rgba(255,255,255,0.15);
        border-radius: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
        padding: 0;
        overflow: hidden;
      }
      /* 토글 원형 노브 */
      .v4-theme-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 22px;
        height: 22px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        z-index: 2;
      }
      /* 달 아이콘 (라이트모드 = 오른쪽에 달) */
      .v4-theme-switch::before {
        content: '';
        position: absolute;
        top: 6px;
        right: 8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: transparent;
        box-shadow: -3px -1px 0 0 rgba(255,255,255,0.8);
        transition: opacity 0.3s ease;
        z-index: 1;
        opacity: 1;
      }
      /* 다크모드 ON 상태 */
      body.dark-theme .v4-theme-switch {
        background: #33aeec;
      }
      body.dark-theme .v4-theme-switch::after {
        transform: translateX(24px);
      }
      body.dark-theme .v4-theme-switch::before {
        opacity: 0;
      }

      /* 12) 라이트 모드 메뉴 오버라이드 */
      body:not(.dark-theme) #header #lnb {
        background: #fff !important;
      }
      body:not(.dark-theme) #lnb .top1menu .depth1_ti span {
        color: #111 !important;
      }
      body:not(.dark-theme) #lnb .top1menu .depth1_ti::after {
        border-color: rgba(0,0,0,0.3) !important;
      }
      body:not(.dark-theme) #lnb .top1menu > li {
        border-color: rgba(0,0,0,0.1) !important;
      }
      body:not(.dark-theme) #lnb .depth2 > li {
        background: transparent !important;
      }
      body:not(.dark-theme) #lnb .depth2 > li > a {
        color: rgba(0,0,0,0.6) !important;
        background: transparent !important;
      }
      body:not(.dark-theme) .m_top_search_btn_box .top_search_box {
        background: rgba(0,0,0,0.05) !important;
        border-color: rgba(0,0,0,0.15) !important;
      }
      body:not(.dark-theme) .m_top_search_btn_box .top_search_box input {
        color: #111 !important;
      }
      body:not(.dark-theme) .m_top_search_btn_box .top_search_box button img {
        filter: none !important;
      }
      body:not(.dark-theme) .lnb_close button:before,
      body:not(.dark-theme) .lnb_close button:after {
        background-color: #111 !important;
      }
      body:not(.dark-theme) .lnb_m_nav .open .line {
        background: #000 !important;
      }
      body:not(.dark-theme) .v4-menu-footer {
        border-color: rgba(0,0,0,0.1);
      }
      body:not(.dark-theme) .v4-theme-toggle-row span {
        color: rgba(0,0,0,0.6);
      }
      body:not(.dark-theme) .v4-theme-switch {
        background: rgba(0,0,0,0.15);
      }
      body:not(.dark-theme) .v4-theme-switch::after {
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
      }
    }

    /* Community Event Section - Editorial Grid */
    .event_sec {
      padding: 100px 0;
      background: linear-gradient(to bottom, #000 0%, #212121 100%); /* 이벤트 섹션만 그라데이션 적용 */
    }
    .event_sec .sec_title {
      position: relative;
      margin-bottom: 30px; /* 여백 축소 */
      text-align: center;
    }
    /* Unified Section Title Styles */
    .event_sec .sec_title h2 {
      font-size: 32px;
      font-weight: 900;
      color: #fff; /* 흰색 타이틀 */
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .news_sec .sec_title h2,
    .resource_list .resource_title h2 {
      font-size: 32px;
      font-weight: 900;
      color: #000;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .event_sec .sec_title a {
      position: absolute;
      top: 5px;
      right: 0px;
      border: 1px solid #e5e5e5;
      border-radius: 4px;
      padding: 5px 15px;
      font-size: 14px;
      color: #888;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    .event_sec .sec_title a:hover {
      color: #333;
      border: 1px solid #ccc;
      background: #f9f9f9;
    }
    .event_grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr); /* 2열 배치 */
      gap: 30px;
    }
    .event_item_card {
      position: relative;
      background: #111; /* 다크 배경 */
      aspect-ratio: 16 / 9; /* 2행 2열을 위해 넓은 비율 유지 */
      overflow: hidden;
      cursor: pointer;
      border: 1px solid #222; /* 미세한 테두리 */
      box-shadow: none; /* 쉐도우 제거 */
      border-radius: 10px; /* 둥근 모서리 추가 */
    }
    .event_item_card > a {
      display: block;
      width: 100%;
      height: 100%;
      position: relative;
      overflow: hidden;
    }
    
    .event_item_card .img_wrap {
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1), opacity 0.5s ease;
      opacity: 0; /* 초기 투명 (로딩 시각화) */
      position: relative;
    }
    .event_item_card .img_wrap::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 50%;
      height: 100%;
      background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 100%);
      transform: skewX(-25deg);
      transition: none;
      z-index: 1;
    }
    .event_item_card.loaded .img_wrap {
      opacity: 1;
    }
    .event_item_card:hover .img_wrap {
      transform: scale(1.1);
      /* filter: brightness(0.8); 제거 - 선명도 유지 */
    }
    .event_item_card:hover .img_wrap::after {
      left: 150%;
      transition: all 0.6s ease;
    }

    /* Overlay Content - Status Only (Solid & Sharp) */
    .event_overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      padding: 15px;
      z-index: 2;
    }

    .event_overlay .status_badge {
      display: inline-block;
      font-size: 10px;
      font-weight: 900; /* Bold (Darker Weight) */
      padding: 5px 12px;
      background: #000; /* 투명 제외, 블랙 배경 */
      color: #fff; /* 텍스트 흰색 */
      border: 1.5px solid #fff; /* 아웃라인 강조 */
      text-transform: uppercase;
      letter-spacing: 0.05em;
      box-shadow: none; /* 쉐도우 제거 */
    }
    /* 상태별 포인트 (텍스트 컬러나 미세한 톤으로 구분) */
    .event_overlay .status_badge.type1 { border-color: #000; color: #fff; background: #000; } 
    .event_overlay .status_badge.type2 { opacity: 0.8; } 
    .event_overlay .status_badge.type3 { 
      background: transparent; /* 투명 버전 */
      border: 1.5px solid #1FC3E8; /* 연한 하늘색 아웃라인 */
      color: #1FC3E8; 
      font-weight: 700;
      box-shadow: none;
    } /* 결과발표 - 요청 스타일 반영 */

    .event_item_card:hover .event_overlay {
      background: transparent; /* 호버 시 어두워지는 효과 제거 */
    }
    
    /* Event Tab Styles - Modern Structured Capsule */
    .event_tab_container {
      background: none;
      padding: 5px 0;
      margin-bottom: 30px;
      display: flex;
      justify-content: center;
    }
    .event_tabs {
      display: flex;
      background: #1a1a1a; /* 다크 탭 배경 */
      padding: 6px;
      border-radius: 12px;
      border: 1px solid #333;
      width: fit-content;
      gap: 4px;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
    }
    .event_tab_btn {
      font-size: 15px;
      font-weight: 700;
      color: #888;
      background: transparent;
      border: none;
      padding: 12px 35px;
      border-radius: 8px;
      cursor: pointer;
      position: relative;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 1;
    }
    .event_tab_btn:hover {
      color: #fff;
    }
    .event_tab_btn:after {
      content: '';
      position: absolute;
      bottom: 6px; /* 박스 내부 하단 배치 */
      left: 50%;
      width: 0;
      height: 3px;
      border-radius: 10px;
      transform: translateX(-50%);
      transition: all 0.4s cubic-bezier(0.68, -0.6, 0.32, 1.6);
    }
    
    /* 활성화 상태 - 플로팅 박스 효과 (다크 테마) */
    .event_tab_btn.active { 
      background: #333;
      color: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .event_tab_btn.active:after { width: 40%; } /* 적절한 포인트 길이 */

    /* 개별 탭 컬러 포인트 */
    .event_tab_btn[data-type="community"]:after { background: #1FC3E8; }
    .event_tab_btn[data-type="result"]:after { background: #C6FFF7; }
    .event_tab_btn[data-type="global"]:after { background: #FDCCF8; }
    .event_grid_container { display: none; }
    .event_grid_container.active { display: block; animation: fadeIn 0.5s ease forwards; }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @media (max-width: 1024px) {
      .event_grid { grid-template-columns: repeat(2, 1fr); }
      .event_tab_btn { min-width: 100px; padding: 12px 20px; font-size: 14px; }
    }
    @media (max-width: 640px) {
      .event_grid { grid-template-columns: 1fr; }
      .event_tabs { width: 90%; }
      .event_tab_btn { flex: 1; min-width: 0; padding: 10px 0; }
    }

  </style>

  <script>
    function switchEventTab(type) {
      $('.event_tab_btn').removeClass('active');
      $(`.event_tab_btn[data-type="${type}"]`).addClass('active');
      
      $('.event_grid_container').removeClass('active');
      $(`#event_grid_${type}`).addClass('active');

      // Update "View All" link
      const links = {
        'community': 'https://epiclounge.co.kr/contents/event_list.php?category=%EC%BB%A4%EB%AE%A4%EB%8BA%88%ED%8B%B0%20%EC%9D%B4%EB%B2%A4%ED%8A%B8',
        'result': 'https://epiclounge.co.kr/contents/event_list.php?status=%EA%B2%B0%EA%B3%BC%EB%B0%9C%ED%91%9C',
        'global': 'https://epiclounge.co.kr/contents/global_event_list.php?category=%EA%B8%80%EB%A1%9C%EB%B2%8C%20%EC%9D%B4%EB%B2%A4%ED%8A%B8'
      };
      $('#event_view_all').attr('href', links[type]);
    }
  </script>


  <?php
  define('_INDEX_', true);
  if (!defined('_GNUBOARD_'))
    exit;  // 개별 페이지 접근 불가
  ?>


  <!-- <div id="quick_banner">
    <ul>
      <li><a href="https://twitter.com/intent/tweet?text=언리얼 페스트 2023 서울, 등록이 시작되었습니다.!&url=https://epiclounge.co.kr" title="새창" target="_blank"><img src="/v3/resource/images/event/quick_sns_1.png" /></a></li>
      <li><a href="https://www.facebook.com/sharer/sharer.php?u=https://epiclounge.co.kr" title="새창" target="_blank"><img src="/v3/resource/images/event/quick_sns_2.png" /></a></li>
      <li><a href="#n" onclick="clip(); return false;" title="새창" target="_blank"><img src="/v3/resource/images/event/quick_sns_3.png" /></a></li>
    </ul>
    <a href="#event_main_sec_1" class="top_btn"><img src="/v3/resource/images/event/arrow_top_btn.png" /></a>
  </div> -->



  <?php
  if (defined('_INDEX_')) {  // index에서만 실행
    include G5_BBS_PATH . '/newwin.inc.php';  // 팝업레이어
  }
  ?>
  <?php include 'inc/common_header26.php'; ?>
  <!-- container -->
  <div class="container">
    <section class="swiper cinematic-hero">
      <div class="swiper-wrapper">
        <?php
        // DB에서 메인 비주얼 데이터 조회
        $sql_visual = ' SELECT * FROM v3_visual_main WHERE vm_display = 1 ORDER BY vm_order ASC, vm_id DESC ';
        $result_visual = sql_query($sql_visual);

        while ($row = sql_fetch_array($result_visual)) {
          $duration = $row['vm_duration'] ? $row['vm_duration'] : 7000;
          ?>
        <div class="swiper-slide" data-swiper-autoplay="<?php echo $duration; ?>">
          <div class="hero-bg">
            <?php if ($row['vm_bg_type'] == 'video') { ?>
            <!-- Video Background -->
            <video autoplay loop muted playsinline style="<?php echo ($row['vm_order'] == 2) ? 'filter: brightness(0.6) contrast(1.2);' : ''; ?>">
              <source src="<?php echo $row['vm_bg_url']; ?>" type="video/mp4">
            </video>
            <?php } else { ?>
            <!-- Image Background -->
            <img src="<?php echo $row['vm_bg_url']; ?>" alt="Visual Background" style="width:100%; height:100%; object-fit:cover;">
            <?php } ?>
            <div class="hero-overlay"></div>
          </div>
          <div class="hero-content">
            <div class="hero-title">
              <img src="<?php echo $row['vm_title_img']; ?>" alt="Visual Title" style="width: 800px;">
            </div>
            <div class="hero-cta" style="margin-top: 0;">
              <a href="<?php echo $row['vm_link_url']; ?>" target="_blank"><?php echo $row['vm_btn_text']; ?></a>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <!-- Controls -->
      <div class="swiper-custom-pagination"></div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </section>





    <section class="news_sec">
      <div class="wrap">
        <div class="sec_title">
          <h2>새소식</h2>
          <a href="https://epiclounge.co.kr/contents/news_list.php">+ 전체보기</a>
        </div>
        <div class="con">
          <div class="scroll_box">


            <?

            $main_news_result = sql_query("select * from v3_main_banner_news where bn_use_at = 'Y' order by bn_id desc limit 3");
            foreach ($main_news_result as $news) {
              ?>
            <div class="list_box">
              <a href="<?= $news['bn_url'] ?>" target="_blank">
                <div class="img_box">
                  <img src='/data/main_news/<?php echo $news['bn_id']; ?>' />
                </div>
                <div class="news_text">
                  <span class="news_text_title"><?= $news['bn_title'] ?></span>
                  <span class="news_text_info"><?= $news['bn_info'] ?></span>
                </div>
              </a>
            </div>
            <?
            }
            ?>
          </div>
        </div>
      </div>
    </section>

    <section class="bg_slide_box">
      <div class="bg_slide_list_box">

        <!-- Slide 1 -->
        <div class="bg_slide_list bg_slide_list1">
          <video autoplay muted loop playsinline class="bg_video">
            <source src="https://cms-assets.unrealengine.com/AiKUh5PQCTaOFnmJDZJBfz/AFmdAcaBROOy9YytjJ3a" type="video/mp4">
          </video>
          <div class="txt_wrap">
            <p class="bg_slide_title">에픽 메가그랜트</p>
            <p class="bg_slide_text">에픽 메가그랜트는 리얼타임 3D 개발을 새로운 차원으로 혁신하는<br>커뮤니티의 유망한 신규 프로젝트에 투자하는 프로그램입니다.</p>
            <p class="bg_slide_btn"><a href="#" target="_blank">바로가기</a></p>
          </div>
        </div>

        <!-- Slide 2 -->
        <div class="bg_slide_list bg_slide_list2">
          <video autoplay muted loop playsinline class="bg_video">
            <source src="https://cms-assets.unrealengine.com/AiKUh5PQCTaOFnmJDZJBfz/d66nnRxEQpatoaaSG3ww" type="video/mp4">
          </video>
          <div class="txt_wrap">
            <p class="bg_slide_title">언리얼 엔진 5</p>
            <p class="bg_slide_text">언리얼 엔진은 개발자에 의한, 개발자를 위한, 모두에게 공평한 조건으로 제작되었습니다.<br>누구나 세계에서 가장 개방적이고 진보된 리얼타임 제작 툴을 사용하여 아이디어를 실현할 수 있습니다.</p>
            <p class="bg_slide_btn"><a href="#" target="_blank">다운로드</a></p>
          </div>
        </div>

      </div>
      <div class="slider-dots-box"></div>


    </section>

    <!-- 이벤트 섹션 (탭 적용) -->
    <section class="event_sec">
      <div class="wrap">
        <div class="sec_title">
          <h2>이벤트</h2>
          <a href="https://epiclounge.co.kr/v3/contents/v4/event_list.php?category=%EC%BB%A4%EB%AE%A4%EB%8B%88%ED%8B%B0%20%EC%9D%B4%EB%B2%A4%ED%8A%B8" id="event_view_all">+ 전체보기</a>
        </div>

        <div class="event_tab_container">
          <div class="event_tabs">
            <button type="button" class="event_tab_btn active" data-type="community" onclick="switchEventTab('community')">커뮤니티</button>
            <button type="button" class="event_tab_btn" data-type="result" onclick="switchEventTab('result')">결과발표</button>
            <button type="button" class="event_tab_btn" data-type="global" onclick="switchEventTab('global')">글로벌</button>
          </div>
        </div>
        
        <!-- 커뮤니티 그리드 -->
        <div id="event_grid_community" class="event_grid_container active">
          <div class="event_grid">
            <?php
            $sql_community = " SELECT * FROM v3_rsc_event_bbs 
                               WHERE category = '커뮤니티 이벤트' AND display_yn = 'Y' 
                               AND (status = '진행중' OR status = '종료')
                               ORDER BY ordr DESC, rsc_bbs_idx DESC 
                               LIMIT 4 ";
            $res_comm = sql_query($sql_community);
            while ($row_ev = sql_fetch_array($res_comm)) {
                $st_class = ($row_ev['status'] == '종료') ? "type2" : "type1";
                $ev_thumb = "/v3/resource/images/sub/event_list_img.jpg";
if (!empty($row_ev['thumb_img'])) {
    if (strpos($row_ev['thumb_img'], 'http') === 0) {
        $ev_thumb = $row_ev['thumb_img'];
    } elseif (file_exists(G5_DATA_PATH.'/event/'.$row_ev['thumb_img'])) {
        $ev_thumb = G5_DATA_URL.'/event/'.$row_ev['thumb_img'];
    }
}
                $ev_link = "/v3/contents/v4/event_view.php?rsc_bbs_idx=".$row_ev['rsc_bbs_idx'];
            ?>
            <div class="event_item_card" id="ev_comm_<?php echo $i; ?>">
              <a href="<?php echo $ev_link; ?>">
                <div class="img_wrap" style="background-image: url('<?php echo $ev_thumb; ?>');" onload="this.parentElement.parentElement.classList.add('loaded')"></div>
                <script>
                  (function(){
                    var img = new Image();
                    img.src = '<?php echo $ev_thumb; ?>';
                    img.onload = function() { document.getElementById('ev_comm_<?php echo $i; ?>').classList.add('loaded'); };
                  })();
                </script>
                <div class="event_overlay">
                  <span class="status_badge <?php echo $st_class; ?>"><?php echo $row_ev['status']; ?></span>
                </div>
              </a>
            </div>
            <?php $i++; } ?>
          </div>
        </div>

        <!-- 결과발표 그리드 -->
        <div id="event_grid_result" class="event_grid_container">
          <div class="event_grid">
            <?php
            $sql_result = " SELECT * FROM v3_rsc_event_bbs 
                               WHERE display_yn = 'Y' 
                               AND status = '결과발표'
                               ORDER BY ordr DESC, rsc_bbs_idx DESC 
                               LIMIT 4 ";
            $res_res = sql_query($sql_result);
            $j = 0;
            while ($row_ev = sql_fetch_array($res_res)) {
                $st_class = "type3";
                $ev_thumb = "/v3/resource/images/sub/event_list_img.jpg";
if (!empty($row_ev['thumb_img'])) {
    if (strpos($row_ev['thumb_img'], 'http') === 0) {
        $ev_thumb = $row_ev['thumb_img'];
    } elseif (file_exists(G5_DATA_PATH.'/event/'.$row_ev['thumb_img'])) {
        $ev_thumb = G5_DATA_URL.'/event/'.$row_ev['thumb_img'];
    }
}
                $ev_link = "/v3/contents/v4/event_view.php?rsc_bbs_idx=".$row_ev['rsc_bbs_idx'];
            ?>
            <div class="event_item_card" id="ev_res_<?php echo $j; ?>">
              <a href="<?php echo $ev_link; ?>">
                <div class="img_wrap" style="background-image: url('<?php echo $ev_thumb; ?>');"></div>
                <script>
                  (function(){
                    var img = new Image();
                    img.src = '<?php echo $ev_thumb; ?>';
                    img.onload = function() { document.getElementById('ev_res_<?php echo $j; ?>').classList.add('loaded'); };
                  })();
                </script>
                <div class="event_overlay">
                  <span class="status_badge <?php echo $st_class; ?>"><?php echo $row_ev['status']; ?></span>
                </div>
              </a>
            </div>
            <?php $j++; } ?>
          </div>
        </div>

        <!-- 글로벌 그리드 -->
        <div id="event_grid_global" class="event_grid_container">
          <div class="event_grid">
            <?php
            $sql_global = " SELECT * FROM v3_rsc_global_event_bbs 
                            WHERE display_yn = 'Y' 
                            AND (status = '진행중' OR status = '종료')
                            ORDER BY ordr DESC, rsc_bbs_idx DESC 
                            LIMIT 4 ";
            $res_global = sql_query($sql_global);
            $k = 0;
            while ($row_ev = sql_fetch_array($res_global)) {
                $st_class = ($row_ev['status'] == '종료') ? "type2" : "type1";
                $ev_thumb = "/v3/resource/images/sub/event_list_img.jpg";
if (!empty($row_ev['thumb_img'])) {
    if (strpos($row_ev['thumb_img'], 'http') === 0) {
        $ev_thumb = $row_ev['thumb_img'];
    } elseif (file_exists(G5_DATA_PATH.'/event/'.$row_ev['thumb_img'])) {
        $ev_thumb = G5_DATA_URL.'/event/'.$row_ev['thumb_img'];
    }
}
                $ev_link = "/v3/contents/global_event_view.php?rsc_bbs_idx=".$row_ev['rsc_bbs_idx'];
            ?>
            <div class="event_item_card" id="ev_gl_<?php echo $k; ?>">
              <a href="<?php echo $ev_link; ?>">
                <div class="img_wrap" style="background-image: url('<?php echo $ev_thumb; ?>');"></div>
                <script>
                  (function(){
                    var img = new Image();
                    img.src = '<?php echo $ev_thumb; ?>';
                    img.onload = function() { document.getElementById('ev_gl_<?php echo $k; ?>').classList.add('loaded'); };
                  })();
                </script>
                <div class="event_overlay">
                  <span class="status_badge <?php echo $st_class; ?>"><?php echo $row_ev['status']; ?></span>
                </div>
              </a>
            </div>
            <?php $k++; } ?>
          </div>
        </div>

      </div>
    </section>


    <section class="resource_list">
      <div class="wrap">
        <div class="resource_title">
          <h2>리소스</h2>
          <a href="https://epiclounge.co.kr/contents/replay.php">+ 전체보기</a>
        </div>
        <div class="con">
          <?

          $main_rsc_result = sql_query("select * from v3_main_banner_rsc where bn_use_at = 'Y' order by bn_id desc limit 6");
          foreach ($main_rsc_result as $rsc) {
            ?>
          <div class="list_box">
            <a href="<?= $rsc['bn_url'] ?>" target="_blank">
              <div class="img_box">
                <img src='/v3/data/main_res/<?php echo $rsc['bn_id']; ?>' />
              </div>
              <div class="text_box">
                <span class="cate"><?= $rsc['bn_tag'] ?></span>
                <span class="title"><?= $rsc['bn_title'] ?></span>
                <span class="text"><?= $rsc['bn_info'] ?></span>
              </div>
            </a>
          </div>
          <?
          }
          ?>
        </div>
      </div>
    </section>


    <!-- GSAP 3 & SplitText Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- Note: SplitText is a paid plugin. Use your licensed version here. -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/SplitText.min.js"></script>

    <script>
      let splitTitle, splitText;

      function animateBgSlideText(activeSlide) {
        const title = activeSlide.find('.bg_slide_title');
        const text = activeSlide.find('.bg_slide_text');

        // Kill any ongoing tweens
        gsap.killTweensOf([title, text]);
        if (splitTitle) { splitTitle.revert(); splitTitle = null; }
        if (splitText) { splitText.revert(); splitText = null; }

        // Initial setup for the SplitText or animation
        gsap.set([title, text], { autoAlpha: 0, visibility: "visible" });

        // Force a small delay to ensure DOM is ready and styles are applied
        requestAnimationFrame(() => {
          if (window.SplitText) {
            splitTitle = new SplitText(title, { type: "chars, words" });
            splitText = new SplitText(text, { type: "words" });

            gsap.set([title, text], { autoAlpha: 1 });

            gsap.from(splitTitle.chars, {
              duration: 0.8,
              y: 50,
              autoAlpha: 0,
              stagger: 0.04,
              ease: "power4.out",
              overwrite: "auto"
            });

            gsap.from(splitText.words, {
              duration: 0.8,
              autoAlpha: 0,
              y: 30,
              stagger: 0.08,
              ease: "power2.out",
              delay: 0.4,
              overwrite: "auto"
            });
          } else {
            // Fallback if SplitText is missing
            gsap.to([title, text], { 
              duration: 0.8, 
              y: 0, 
              autoAlpha: 1, 
              stagger: 0.2,
              ease: "power2.out",
              startAt: { y: 20, autoAlpha: 0 }
            });
          }
        });
      }

      const $bgSlider = $('.bg_slide_list_box');

      $bgSlider.on('init', function(event, slick) {
        const firstSlide = $(slick.$slides[0]);
        // Slight delay to wait for initial page load/CSS
        setTimeout(() => animateBgSlideText(firstSlide), 600);
      });

      $bgSlider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        dots: true,
        appendDots: $('.slider-dots-box'),
        dotsClass: 'slider-dots',
        autoplay: true,
        autoplaySpeed: 6000,
        fade: true,
        cssEase: 'cubic-bezier(0.7, 0, 0.3, 1)',
        speed: 800
      });

      $bgSlider.on('beforeChange', function(event, slick, currentSlide, nextSlide) {
        // Hide currently active slide's text immediately when transition starts
        const currentSlideElem = $(slick.$slides[currentSlide]);
        const nextSlideElem = $(slick.$slides[nextSlide]);
        gsap.set(currentSlideElem.find('.bg_slide_title, .bg_slide_text'), { autoAlpha: 0 });
        gsap.set(nextSlideElem.find('.bg_slide_title, .bg_slide_text'), { autoAlpha: 0 });
      });

      $bgSlider.on('afterChange', function(event, slick, currentSlide) {
        const activeSlide = $(slick.$slides[currentSlide]);
        animateBgSlideText(activeSlide);
      });

      // Cinematic Swiper Initialization
      // Cinematic Swiper Initialization
      const animateSlide = (swiper) => {
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;

        const title = activeSlide.querySelector('.hero-title');
        const cta = activeSlide.querySelector('.hero-cta');
        const bgMedia = activeSlide.querySelector('.hero-bg video, .hero-bg img');

        // GSAP Reset & Animation
        if (title && cta) {
          gsap.killTweensOf([title, cta]);
          gsap.set([title, cta], { autoAlpha: 0, y: 0 });
        }
        
        if (bgMedia) {
          gsap.killTweensOf(bgMedia);
          gsap.set(bgMedia, { scale: 1 });
        }

        const tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: 0.5 } }); // GSAP 애니메이션 속도 0.5초로 단축
        
        // Text Fade In
        if (title && cta) {
          tl.to(title, { autoAlpha: 1, y: 0 })
            .to(cta, { autoAlpha: 1, y: 0 }, '-=0.3'); // 겹치는 시간 조정
        }

        // Ken Burns Effect via GSAP
        if (bgMedia) {
          gsap.fromTo(bgMedia, 
            { scale: 1 },
            { scale: 1.15, duration: 10, ease: 'none', overwrite: 'auto' }
          );
        }
      };

      const heroSwiper = new Swiper('.cinematic-hero', {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: true,
        loopAdditionalSlides: 2,
        speed: 800, // 슬라이드 전환 속도 0.8초로 단축 (반응성 향상)
        watchSlidesProgress: true,
        autoplay: {
          delay: 6000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-custom-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        on: {
          init: function () {
            animateSlide(this);
          },
          transitionStart: function () {
            animateSlide(this);
          }
        }
      });;
    </script>
  </div>

  <?php include 'inc/common_footer.php'; ?>

  <!-- //wrap -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
  <script type="text/javascript">
    var g5_cookie_domain = "";
    document.addEventListener('DOMContentLoaded', function() {
      var trigger = new ScrollTrigger({

        offset: {
          x: 0,
          y: -100
        },
        addHeight: true
      }, document.body, window);
    });



    $(function() {
      $("a").on("click", function() {
        var divName = $(this).attr("id"),
          topPosition = $("." + divName).offset().top;
        $('html, body').animate({
          scrollTop: topPosition - 0
        }, 500);
        return false; //리턴펄스로 스크롤이 최상위로 갔다가 돌아오는 현상 없어짐
      });
    });
  </script>

  <!-- v4 모바일 메뉴 오버라이드 -->
  <script>
  (function($) {
    if ($(window).width() > 768) return;

    var $lnb = $('#lnb');
    var $hamburger = $('.lnb_m_nav');

    // 1) mobile_menu() 함수를 오버라이드 — jquery.responsive가 재호출해도 우리 코드 유지
    window.mobile_menu = function() {
      var $depth1 = $lnb.find('.top1menu');
      $depth1.find('> li > div').addClass('top2m');
      // 기존 mobile_menu의 핸들러 등록을 차단 — 우리 아코디언 사용
    };

    // 2) 기존 이벤트 핸들러 완전 제거
    $hamburger.off();
    $('.mask, .lnb_close button').off();
    $lnb.find('.top1menu > li > a').off();
    $lnb.find('.top1menu ul > li a').off();

    // 3) 인라인 스타일 제거 + 애니메이션 중단
    $lnb.stop(true, true).removeAttr('style');
    $('.lnb_close').stop(true, true).removeAttr('style');
    $('.gnb_navi').stop(true, true).removeAttr('style');
    $('.link_set').stop(true, true).removeAttr('style');

    // 4) 서브메뉴 초기 숨김 (mobile_menu가 slideUp 했던 것 대체)
    $lnb.find('.top1menu > li > div').hide();

    // 5) 열기
    $hamburger.on('click', function(e) {
      e.stopImmediatePropagation();
      $lnb.addClass('v4-menu-open');
      $('body').addClass('v4-menu-active');
      $('body, html').css('overflow', 'hidden');
      $hamburger.fadeOut(200);
    });

    // 6) 닫기
    function closeMenu() {
      $lnb.removeClass('v4-menu-open');
      $('body').removeClass('v4-menu-active');
      $('body, html').css('overflow', '');
      $lnb.find('.depth1').removeClass('v4-sub-open');
      $hamburger.delay(200).fadeIn(200);
    }
    $('.lnb_close button').on('click', function(e) {
      e.stopImmediatePropagation();
      closeMenu();
    });

    // 7) 서브메뉴 아코디언 — CSS 클래스 토글 방식 (slideDown은 !important에 의해 차단됨)
    $lnb.find('.top1menu .depth1_ti').off().on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      var $parent = $(this).closest('.depth1');
      var wasOpen = $parent.hasClass('v4-sub-open');

      // 다른 서브메뉴 닫기
      $lnb.find('.depth1.v4-sub-open').not($parent).removeClass('v4-sub-open');

      // 토글
      $parent.toggleClass('v4-sub-open', !wasOpen);
    });

    // 8) 서브메뉴 링크 클릭 시 정상 이동
    $lnb.find('.depth2 a').on('click', function(e) {
      e.stopPropagation();
    });

    // 9) 메뉴 하단 다크모드 토글 추가
    var isDark = localStorage.getItem('theme') === 'dark' || $('body').hasClass('dark-theme');
    var footerHtml = '<div class="v4-menu-footer">' +
      '<div class="v4-theme-toggle-row">' +
        '<span>' + (isDark ? '다크 모드' : '라이트 모드') + '</span>' +
        '<button class="v4-theme-switch" type="button" aria-label="테마 전환"></button>' +
      '</div>' +
    '</div>';
    $lnb.append(footerHtml);

    // 토글 클릭 이벤트
    $lnb.find('.v4-theme-switch').on('click', function(e) {
      e.stopPropagation();
      $('body').toggleClass('dark-theme');
      var nowDark = $('body').hasClass('dark-theme');
      localStorage.setItem('theme', nowDark ? 'dark' : 'light');
      $lnb.find('.v4-theme-toggle-row > span').text(nowDark ? '다크 모드' : '라이트 모드');
    });

    // 10) 리사이즈 시 메뉴 닫기
    $(window).on('resize', function() {
      if ($(window).width() > 768) {
        closeMenu();
      }
    });

  })(jQuery);
  </script>

</body>

</html>