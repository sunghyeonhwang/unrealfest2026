<?php
$g5_path = '../..';
chdir($g5_path);
include_once('./_common.php');

// ----- SEO -----
$v3_seo = sql_fetch("SELECT * FROM v3_seo_config WHERE seo_page = 'cookie'");
if (empty($v3_seo['seo_title'])) {
    $v3_seo = sql_fetch("SELECT * FROM v3_seo_config WHERE seo_page = 'default'");
}
$seo_title = '쿠키 정책 | 에픽 라운지';

$seo_ga_id          = trim($v3_seo['seo_ga_id'] ?? '');
$seo_gtm_id         = trim($v3_seo['seo_gtm_id'] ?? '');
$seo_pixel_id       = trim($v3_seo['seo_pixel_id'] ?? '');
$seo_kakao_pixel_id = trim($v3_seo['seo_kakao_pixel_id'] ?? '');
$seo_naver_verif    = trim($v3_seo['seo_naver_verif'] ?? '');
$seo_google_verif   = trim($v3_seo['seo_google_verif'] ?? '');
$seo_extra_head     = $v3_seo['seo_extra_head'] ?? '';
$seo_extra_body     = $v3_seo['seo_extra_body'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script>(function(){try{var q=(location.search.match(/[?&]lang=(en|ko)/)||[])[1];var l=q||localStorage.getItem('ufsLegalLang')||'ko';if(l==='en')document.documentElement.className+=' is-en';}catch(e){}})();</script>
    <meta property="og:type" content="website">
    <meta property="og:title" content="쿠키 정책 | 에픽 라운지">
    <meta property="og:url" content="https://epiclounge.co.kr/contents/v4/cookie.php">

    <?php include G5_PATH.'/inc/marketing_head.php'; ?>

    <link rel="icon" type="image/png" sizes="32x32" href="/v3/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/v3/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/v3/favicon/apple-icon-180x180.png">

    <title><?php echo get_text($seo_title); ?></title>

    <link rel="stylesheet" href="/v3/resource/css/main26.css">
    <link rel="stylesheet" href="/v3/resource/css/sub.css">
    <link rel="stylesheet" href="/v3/resource/css/pages/detail.css?v=20260318b">

    <script src="/v3/resource/js/jquery-3.4.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<style>
/* UFS: 약관 가독성 — 목록 불릿 복원 + 간격 */
.v4-detail-content ul,.v4-detail-content ol{padding-left:24px;}
.v4-detail-content ul ul,.v4-detail-content ol ol,.v4-detail-content ul ol,.v4-detail-content ol ul{margin-top:6px;}
.v4-detail-content li{list-style:disc !important;}
.v4-detail-content li li{list-style:circle !important;}
.v4-detail-content li{margin-bottom:8px;line-height:1.7;}
.v4-detail-content li::marker{color:#00C1D5;}
.v4-detail-content h3{margin-top:30px;}
/* 표 */
.v4-detail-content .doc-scroll{overflow-x:auto;margin:14px 0;}
.v4-detail-content table{width:100%;border-collapse:collapse;font-size:14px;}
.v4-detail-content th,.v4-detail-content td{border:1px solid #e2e5ea;padding:9px 11px;text-align:left;vertical-align:top;line-height:1.6;}
.v4-detail-content th{background:#f5f6f8;font-weight:700;white-space:nowrap;}
.v4-detail-content .note{background:#e8f6f8;border:1px solid #a9dde4;border-radius:8px;padding:14px 16px;margin:16px 0;color:#14343b;}
.v4-detail-content .note p{margin:0;color:#14343b;}
.v4-detail-content .note a{color:#0e7c8b;}
.v4-detail-content .sub-note{font-size:13px;color:#8a8f98;}
/* KO/EN 언어 토글 */
.lang-en{display:none;}
html.is-en .lang-ko{display:none;}
html.is-en .lang-en{display:revert;}
.lang-switch{display:flex;justify-content:flex-end;margin:0 0 14px;}
.lang-switch .lang-btns{display:inline-flex;border:1px solid #d5d8de;border-radius:999px;overflow:hidden;}
.lang-switch button{appearance:none;-webkit-appearance:none;border:0;background:#fff;color:#8a8f98;font-size:13px;font-weight:700;padding:7px 18px;cursor:pointer;line-height:1;transition:background .15s,color .15s;}
.lang-switch button.is-active{background:#00C1D5;color:#fff;}
</style>
</head>
<body>
<?php include G5_PATH.'/inc/marketing_body.php'; ?>
<?php include G5_PATH.'/inc/common_header26.php'; ?>

<!-- sub_visual -->
<div id="sub_visual" class="resource_vi">
    <h2><span class="lang-ko">쿠키 정책</span><span class="lang-en">Cookie Policy</span></h2>
    <p></p>
</div>

<!-- 본문 컨테이너 -->
<div class="v4-detail-container">
    <div class="v4-detail-wrapper">

        <div class="lang-switch">
            <div class="lang-btns">
                <button type="button" data-lang="ko" class="is-active">한글</button>
                <button type="button" data-lang="en">EN</button>
            </div>
        </div>

        <div class="v4-detail-header">
            <h1 class="v4-detail-header__title"><span class="lang-ko">쿠키 정책</span><span class="lang-en">Cookie Policy</span></h1>
            <div class="v4-detail-header__meta">
                <span class="v4-detail-header__meta-item lang-ko">최종 업데이트: 2026년 2월 12일</span>
                <span class="v4-detail-header__meta-item lang-en">Last updated: February 12, 2026</span>
            </div>
        </div>

        <!-- ===================== 한글 ===================== -->
        <div class="v4-detail-content lang-ko">

            <div class="note"><p>본 쿠키 정책은 에픽 라운지(이하 "회사")가 운영하는 웹사이트(epiclounge.co.kr 및 하위 페이지)에서 쿠키 및 유사 기술을 어떻게 사용하는지 설명합니다. 본 정책은 <a href="/v3/contents/v4/personal.php">개인정보처리방침</a>을 보완하며, 두 문서를 함께 확인하시기 바랍니다.</p></div>

            <h3>제1조(쿠키란)</h3>
            <p>쿠키(Cookie)는 웹사이트를 방문할 때 이용자의 브라우저나 기기에 저장되는 작은 텍스트 파일입니다. 회사는 쿠키 외에도 로컬 스토리지, 픽셀(트래킹 태그), 소프트웨어 개발 키트(SDK) 등 유사한 기술을 함께 사용할 수 있으며, 본 정책에서는 이를 통틀어 "쿠키"라고 합니다. 쿠키는 방문자를 개인적으로 직접 식별하지는 않지만, 브라우저ㆍ기기 식별자 및 이용 기록 등과 결합하여 이용자를 구별하는 데 사용될 수 있습니다.</p>

            <h3>제2조(쿠키 사용 목적)</h3>
            <ul>
                <li><strong>필수 기능</strong> — 로그인ㆍ등록 상태 유지, 신청서 작성 및 결제 진행, 보안 및 부정 이용 방지, 언어 설정 저장 등 서비스의 기본 기능 제공</li>
                <li><strong>성능ㆍ분석</strong> — 방문자 수, 페이지 이용 경로, 체류 시간 등 통계를 익명 또는 가명 형태로 측정하여 서비스 개선</li>
                <li><strong>광고ㆍ행태정보</strong> — 광고 성과 측정(전환 추적), 관심사 기반 맞춤형 광고 제공 및 중복 노출 관리</li>
            </ul>

            <h3>제3조(쿠키의 종류)</h3>
            <p>보관 기간에 따라 브라우저 종료 시 삭제되는 <strong>세션 쿠키</strong>와 설정된 기간 동안 유지되는 <strong>지속 쿠키</strong>로 나뉘며, 설치 주체에 따라 회사가 설치하는 <strong>1자 쿠키(First-party)</strong>와 외부 사업자가 설치하는 <strong>제3자 쿠키(Third-party)</strong>로 나뉩니다. 회사는 다음과 같은 쿠키를 사용합니다.</p>
            <div class="doc-scroll"><table><thead><tr><th>유형</th><th>주요 항목</th><th>목적</th><th>설치 주체</th><th>보관 기간</th></tr></thead><tbody>
                <tr><td>필수ㆍ기능</td><td>세션 식별자(로그인ㆍ등록 세션), 언어 설정, 미리보기ㆍ쿠폰 등 서비스 상태값</td><td>등록ㆍ결제 진행, 로그인 상태 유지, 언어 설정 기억, 보안</td><td>1자</td><td>세션 종료 시 ~ 최대 1년</td></tr>
                <tr><td>분석</td><td>Google Analytics 4(_ga 등)</td><td>방문 통계, 이용 경로 분석, 서비스 개선</td><td>제3자</td><td>최대 2년</td></tr>
                <tr><td>광고ㆍ전환</td><td>Meta(Facebook) 픽셀, 카카오 픽셀, 네이버 전환 스크립트(CTS), 어크로스(Across) ADN 태그</td><td>광고 전환 측정, 맞춤형 광고 제공 및 성과 분석</td><td>제3자</td><td>최대 1년</td></tr>
            </tbody></table></div>
            <p class="sub-note">사용하는 도구 및 세부 항목은 서비스 운영에 따라 변경될 수 있으며, 변경 시 본 정책을 통해 안내합니다.</p>

            <h3>제4조(제3자 쿠키 및 맞춤형 광고)</h3>
            <p>회사는 광고 성과 측정과 맞춤형 광고 제공을 위해 아래 광고ㆍ분석 사업자의 쿠키 및 픽셀을 사용할 수 있습니다. 이 과정에서 이용자의 방문ㆍ전환 행태정보가 해당 사업자에게 전송될 수 있으며, 각 사업자의 개인정보 처리에는 해당 사업자의 방침이 적용됩니다.</p>
            <div class="doc-scroll"><table><thead><tr><th>사업자</th><th>용도</th><th>정보 처리ㆍ거부(옵트아웃)</th></tr></thead><tbody>
                <tr><td>Google (Google Analytics, Google Ads)</td><td>방문 분석, 광고 전환 측정</td><td>policies.google.com/technologies/partner-sites · 차단 도구: tools.google.com/dlpage/gaoptout</td></tr>
                <tr><td>Meta Platforms (Facebook·Instagram)</td><td>광고 전환 측정, 맞춤형 광고</td><td>facebook.com/about/ads · 계정 광고 설정에서 거부</td></tr>
                <tr><td>카카오 (Kakao)</td><td>광고 전환 측정, 맞춤형 광고</td><td>카카오 계정 &gt; 광고 설정에서 거부</td></tr>
                <tr><td>네이버 (NAVER)</td><td>광고 전환 측정</td><td>네이버 계정 &gt; 광고 설정 및 브라우저 쿠키 차단</td></tr>
                <tr><td>어크로스 (Across / ADN)</td><td>매체 통합 광고 전환 측정</td><td>브라우저 쿠키 차단 또는 아래 제5조의 방법으로 거부</td></tr>
            </tbody></table></div>
            <p><strong>행태정보 수집 안내</strong> — 회사가 맞춤형 광고를 위해 처리하는 행태정보는 다음과 같습니다.</p>
            <div class="doc-scroll"><table><thead><tr><th>구분</th><th>내용</th></tr></thead><tbody>
                <tr><td>수집 항목</td><td>웹사이트 방문 이력, 클릭ㆍ페이지 조회 기록, 등록ㆍ결제 등 전환 이벤트, 쿠키ㆍ광고 식별자, 기기ㆍ브라우저 정보, IP주소</td></tr>
                <tr><td>수집 방법</td><td>이용자가 웹사이트를 방문하거나 페이지를 이용할 때 쿠키ㆍ픽셀ㆍ스크립트를 통해 자동 수집</td></tr>
                <tr><td>수집 목적</td><td>광고 성과(전환) 측정, 관심사 기반 맞춤형 광고 제공, 중복 노출 관리</td></tr>
                <tr><td>보유ㆍ이용 기간</td><td>수집일로부터 최대 1년(각 광고 사업자의 정책에 따름). 목적 달성 또는 거부 시 지체 없이 파기</td></tr>
                <tr><td>거부 방법</td><td>브라우저 쿠키 차단(아래 제5조), 각 광고 사업자의 광고 설정, 등록 시 광고성 정보 수신 동의 철회</td></tr>
            </tbody></table></div>

            <h3>제5조(쿠키 설정 및 거부 방법)</h3>
            <p>이용자는 브라우저 또는 기기 설정을 통해 쿠키 저장을 거부하거나 이미 저장된 쿠키를 삭제할 수 있습니다. 다만 <strong>필수ㆍ기능 쿠키를 차단하면 로그인 상태 유지, 신청서 작성, 결제 등 일부 기능 이용이 제한될 수 있습니다.</strong></p>
            <div class="doc-scroll"><table><thead><tr><th>브라우저</th><th>설정 방법</th></tr></thead><tbody>
                <tr><td>Chrome</td><td>설정 &gt; 개인정보 보호 및 보안 &gt; 서드 파티 쿠키 또는 사이트 데이터 설정</td></tr>
                <tr><td>Edge</td><td>설정 &gt; 쿠키 및 사이트 권한 &gt; 쿠키 및 사이트 데이터 관리 및 삭제</td></tr>
                <tr><td>Safari</td><td>설정(환경설정) &gt; 개인정보 보호 &gt; 쿠키 및 웹사이트 데이터</td></tr>
                <tr><td>Firefox</td><td>설정 &gt; 개인정보 및 보안 &gt; 쿠키 및 사이트 데이터</td></tr>
                <tr><td>모바일 브라우저</td><td>각 앱의 설정 &gt; 개인정보 보호 또는 사이트 설정 메뉴</td></tr>
            </tbody></table></div>

            <h3>제6조(쿠키 정책의 변경)</h3>
            <p>본 쿠키 정책은 법령, 지침, 서비스 또는 사용 도구의 변경에 따라 개정될 수 있습니다. 중요한 변경이 있는 경우 홈페이지를 통해 변경 내용과 시행일을 사전에 안내합니다.</p>

            <h3>제7조(문의처)</h3>
            <p>쿠키 및 개인정보 처리에 관한 문의는 아래로 접수할 수 있습니다.</p>
            <ul>
                <li>개인정보 보호책임자: 오승훈 실장</li>
                <li>연락처: 02-326-3701, info@griff.co.kr</li>
            </ul>

        </div><!-- /.v4-detail-content lang-ko -->

        <!-- ===================== English ===================== -->
        <div class="v4-detail-content lang-en">

            <div class="note"><p>This Cookie Policy explains how Epic Lounge (the "Company") uses cookies and similar technologies on the website it operates (epiclounge.co.kr and its sub-pages). This Policy supplements the <a href="/v3/contents/v4/personal.php">Privacy Policy</a>; please review both documents together.</p></div>

            <h3>Article 1 (What Are Cookies)</h3>
            <p>A cookie is a small text file stored in a user's browser or device when the user visits a website. In addition to cookies, the Company may also use similar technologies such as local storage, pixels (tracking tags), and software development kits (SDKs), which are collectively referred to as "cookies" in this Policy. Cookies do not directly identify a visitor personally, but may be used to distinguish users when combined with browser/device identifiers, usage records, and the like.</p>

            <h3>Article 2 (Purposes of Using Cookies)</h3>
            <ul>
                <li><strong>Essential/functional</strong> — providing the basic functions of the Services, such as keeping login/registration sessions, filling out application forms and processing payment, security and prevention of misuse, and saving language settings</li>
                <li><strong>Performance/analytics</strong> — improving the Services by measuring statistics such as the number of visitors, page navigation paths, and dwell time in anonymized or pseudonymized form</li>
                <li><strong>Advertising/behavioral information</strong> — measuring advertising performance (conversion tracking), providing interest-based personalized advertising, and managing frequency of exposure</li>
            </ul>

            <h3>Article 3 (Types of Cookies)</h3>
            <p>By retention period, cookies are divided into <strong>session cookies</strong>, which are deleted when the browser is closed, and <strong>persistent cookies</strong>, which remain for a set period. By the party that sets them, they are divided into <strong>first-party cookies</strong> set by the Company and <strong>third-party cookies</strong> set by external businesses. The Company uses the following cookies.</p>
            <div class="doc-scroll"><table><thead><tr><th>Type</th><th>Main items</th><th>Purpose</th><th>Set by</th><th>Retention</th></tr></thead><tbody>
                <tr><td>Essential/functional</td><td>Session identifiers (login/registration session), language setting, service state values such as preview/coupon</td><td>Registration/payment processing, keeping login state, remembering language setting, security</td><td>First party</td><td>Until session ends – up to 1 year</td></tr>
                <tr><td>Analytics</td><td>Google Analytics 4 (_ga, etc.)</td><td>Visit statistics, navigation analysis, service improvement</td><td>Third party</td><td>Up to 2 years</td></tr>
                <tr><td>Advertising/conversion</td><td>Meta (Facebook) Pixel, Kakao Pixel, NAVER conversion script (CTS), Across ADN tag</td><td>Ad conversion measurement, personalized advertising and performance analysis</td><td>Third party</td><td>Up to 1 year</td></tr>
            </tbody></table></div>
            <p class="sub-note">The tools and detailed items used may change according to service operation, and any change will be announced through this Policy.</p>

            <h3>Article 4 (Third-Party Cookies and Personalized Advertising)</h3>
            <p>The Company may use the cookies and pixels of the advertising/analytics businesses below to measure advertising performance and provide personalized advertising. In this process, a user's visit/conversion behavioral information may be transmitted to the relevant business, and each business's own policy applies to its processing of personal information.</p>
            <div class="doc-scroll"><table><thead><tr><th>Business</th><th>Use</th><th>Processing / Opt-out</th></tr></thead><tbody>
                <tr><td>Google (Google Analytics, Google Ads)</td><td>Visit analysis, ad conversion measurement</td><td>policies.google.com/technologies/partner-sites · Opt-out tool: tools.google.com/dlpage/gaoptout</td></tr>
                <tr><td>Meta Platforms (Facebook·Instagram)</td><td>Ad conversion measurement, personalized advertising</td><td>facebook.com/about/ads · Opt out in account ad settings</td></tr>
                <tr><td>Kakao</td><td>Ad conversion measurement, personalized advertising</td><td>Opt out in Kakao account &gt; Ad settings</td></tr>
                <tr><td>NAVER</td><td>Ad conversion measurement</td><td>NAVER account &gt; Ad settings and browser cookie blocking</td></tr>
                <tr><td>Across (ADN)</td><td>Cross-media ad conversion measurement</td><td>Block browser cookies or opt out via the methods in Article 5 below</td></tr>
            </tbody></table></div>
            <p><strong>Notice on collection of behavioral information</strong> — The behavioral information the Company processes for personalized advertising is as follows.</p>
            <div class="doc-scroll"><table><thead><tr><th>Category</th><th>Details</th></tr></thead><tbody>
                <tr><td>Items collected</td><td>Website visit history, click/page-view records, conversion events such as registration/payment, cookie/advertising identifiers, device/browser information, IP address</td></tr>
                <tr><td>Method of collection</td><td>Automatically collected via cookies/pixels/scripts when a user visits the website or uses pages</td></tr>
                <tr><td>Purpose of collection</td><td>Measuring ad performance (conversion), providing interest-based personalized advertising, managing frequency of exposure</td></tr>
                <tr><td>Retention/use period</td><td>Up to 1 year from the date of collection (subject to each advertising business's policy). Destroyed without delay upon achievement of purpose or opt-out</td></tr>
                <tr><td>Opt-out method</td><td>Blocking browser cookies (Article 5 below), each advertising business's ad settings, withdrawing consent to receive marketing information at registration</td></tr>
            </tbody></table></div>

            <h3>Article 5 (How to Set and Refuse Cookies)</h3>
            <p>Users may refuse the storage of cookies or delete already-stored cookies through their browser or device settings. However, <strong>if essential/functional cookies are blocked, the use of some functions such as keeping login state, filling out application forms, and payment may be restricted.</strong></p>
            <div class="doc-scroll"><table><thead><tr><th>Browser</th><th>Setting</th></tr></thead><tbody>
                <tr><td>Chrome</td><td>Settings &gt; Privacy and security &gt; Third-party cookies or site data settings</td></tr>
                <tr><td>Edge</td><td>Settings &gt; Cookies and site permissions &gt; Manage and delete cookies and site data</td></tr>
                <tr><td>Safari</td><td>Settings (Preferences) &gt; Privacy &gt; Cookies and website data</td></tr>
                <tr><td>Firefox</td><td>Settings &gt; Privacy &amp; Security &gt; Cookies and Site Data</td></tr>
                <tr><td>Mobile browsers</td><td>Each app's Settings &gt; Privacy or Site settings menu</td></tr>
            </tbody></table></div>

            <h3>Article 6 (Changes to the Cookie Policy)</h3>
            <p>This Cookie Policy may be revised in accordance with changes in laws, guidelines, the Services, or the tools used. In the event of a material change, the change and its effective date will be announced in advance through the website.</p>

            <h3>Article 7 (Contact)</h3>
            <p>Inquiries regarding cookies and the processing of personal information may be submitted below.</p>
            <ul>
                <li>Personal Information Protection Officer: Seunghoon Oh, General Manager</li>
                <li>Contact: 02-326-3701, info@griff.co.kr</li>
            </ul>

        </div><!-- /.v4-detail-content lang-en -->

        <!-- 목록으로 (메인으로) -->
        <div class="v4-detail-nav">
            <a href="/v3/index.php" class="v4-detail-nav__button--list">
                <span class="v4-detail-nav__label"><span class="lang-ko">메인으로</span><span class="lang-en">Back to main</span></span>
            </a>
        </div>

    </div><!-- /.v4-detail-wrapper -->
</div><!-- /.v4-detail-container -->

<?php include G5_PATH.'/inc/common_footer.php'; ?>

<script src="/v3/resource/js/v4.app.js"></script>
<script>
(function(){
  var KEY='ufsLegalLang';
  function apply(l){
    document.documentElement.classList.toggle('is-en', l==='en');
    document.documentElement.setAttribute('lang', l);
    var b=document.querySelectorAll('.lang-switch button');
    for(var i=0;i<b.length;i++){ b[i].classList.toggle('is-active', b[i].getAttribute('data-lang')===l); }
  }
  var q=(location.search.match(/[?&]lang=(en|ko)/)||[])[1], cur;
  try{ cur=q||localStorage.getItem(KEY)||'ko'; }catch(e){ cur=q||'ko'; }
  document.addEventListener('DOMContentLoaded',function(){
    apply(cur);
    var s=document.querySelector('.lang-switch');
    if(s){ s.addEventListener('click',function(e){
      var t=e.target; while(t&&t.tagName!=='BUTTON') t=t.parentNode;
      if(!t||!t.getAttribute('data-lang')) return;
      var l=t.getAttribute('data-lang');
      try{ localStorage.setItem(KEY,l); }catch(e){}
      apply(l);
    }); }
  });
})();
</script>
</body>
</html>
