<?php
$g5_path = '../..';
chdir($g5_path);
include_once('./_common.php');

// ----- SEO -----
$v3_seo = sql_fetch("SELECT * FROM v3_seo_config WHERE seo_page = 'personal'");
if (empty($v3_seo['seo_title'])) {
    $v3_seo = sql_fetch("SELECT * FROM v3_seo_config WHERE seo_page = 'default'");
}
$seo_title = '개인정보처리방침 | 에픽 라운지';

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
    <meta property="og:title" content="개인정보처리방침 | 에픽 라운지">
    <meta property="og:url" content="https://epiclounge.co.kr/contents/v4/personal.php">

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
    <h2><span class="lang-ko">개인정보처리방침</span><span class="lang-en">Privacy Policy</span></h2>
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
            <h1 class="v4-detail-header__title"><span class="lang-ko">개인정보처리방침</span><span class="lang-en">Privacy Policy</span></h1>
            <div class="v4-detail-header__meta">
                <span class="v4-detail-header__meta-item lang-ko">최종 업데이트: 2026년 2월 12일</span>
                <span class="v4-detail-header__meta-item lang-en">Last updated: February 12, 2026</span>
            </div>
        </div>

        <div class="v4-detail-content lang-ko">

            <h3>제1조(총칙)</h3>
            <p>에픽 라운지(이하 "회사")는 「개인정보 보호법」 등 관계 법령을 준수하며, 정보주체의 개인정보를 보호하고 이와 관련한 고충을 신속하고 원활하게 처리하기 위하여 다음과 같이 개인정보 처리방침을 수립·공개합니다.</p>

            <h3>제2조(개인정보의 처리 목적)</h3>
            <p>회사는 다음의 목적을 위하여 개인정보를 처리하며, 목적 변경이 필요한 경우 「개인정보 보호법」 제18조에 따라 필요한 조치를 이행합니다.</p>
            <ol>
                <li>회원가입 및 회원관리 : 가입의사 확인, 본인확인, 회원자격 유지·관리, 서비스 부정이용 방지, 고지·통지, 민원처리</li>
                <li>이벤트 운영 및 참가자 관리 : 신청 접수, 당첨자 선정, 본인확인, 경품/서비스 제공, 참가 이력 관리</li>
                <li>재화 또는 서비스 제공 : 서비스 제공, 대금결제 및 정산, 환불 처리, 계약 이행</li>
                <li>고객문의 및 분쟁처리 : 문의 응대, 사실확인, 처리결과 통보, 분쟁 조정</li>
            </ol>

            <h3>제3조(처리하는 개인정보 항목 및 법적 근거)</h3>
            <p>회사는 서비스 제공을 위하여 필요한 최소한의 개인정보를 처리합니다.</p>
            <ol>
                <li>정보주체의 동의를 받아 처리하는 항목
                    <ul>
                        <li>회원가입/이벤트 신청 : 성명, 생년월일, 아이디, 비밀번호, 주소, 전화번호, 이메일주소, 성별, 직무, 직책, 학교, 학과, 관심분야</li>
                        <li>마케팅 수신 동의 시 : 이메일 수신 동의 여부, 문자 수신 동의 여부</li>
                    </ul>
                </li>
                <li>계약 이행 및 법령상 의무 이행을 위하여 처리하는 항목
                    <ul>
                        <li>결제·정산 : 성명, 연락처, 이메일주소, 결제수단 정보(카드결제 관련 승인정보 등), 환불계좌 정보(환불 시)</li>
                        <li>법정 보관정보 : 거래기록, 계약/청약철회 기록, 소비자 불만 또는 분쟁처리 기록</li>
                    </ul>
                </li>
                <li>서비스 이용 과정에서 자동 생성·수집되는 항목
                    <ul>
                        <li>IP주소, 쿠키, 서비스 이용기록, 접속 로그, 방문기록, 기기정보, 불량 이용기록</li>
                    </ul>
                </li>
            </ol>

            <h3>제4조(개인정보의 처리 및 보유기간)</h3>
            <ol>
                <li>회사는 법령에 따른 보유·이용기간 또는 정보주체로부터 동의받은 보유·이용기간 내에서 개인정보를 처리·보유합니다.</li>
                <li>주요 개인정보 보유기간
                    <ul>
                        <li>회원정보 : 회원 탈퇴 시까지</li>
                        <li>이벤트 참가 정보 : 행사 종료 후 3년</li>
                        <li>재화/서비스 제공 관련 정보 : 공급 완료 및 요금결제·정산 완료 시까지</li>
                    </ul>
                </li>
            </ol>
            <p>다만, 관계 법령에 따라 다음과 같이 일정 기간 보관합니다.</p>
            <ul>
                <li>「전자상거래 등에서의 소비자보호에 관한 법률」
                    <ul>
                        <li>표시·광고 기록 6개월</li>
                        <li>계약/청약철회·대금결제·재화공급 기록 5년</li>
                        <li>소비자 불만 또는 분쟁처리 기록 3년</li>
                    </ul>
                    </li>
                <li>「통신비밀보호법」
                    <ul>
                        <li>인터넷 로그기록자료·접속지 추적자료 3개월</li>
                    </ul>
                    </li>
            </ul>

            <h3>제5조(개인정보의 제3자 제공)</h3>
            <p>회사는 원칙적으로 정보주체의 개인정보를 제2조의 처리 목적 범위 내에서만 처리하며, 정보주체의 동의 또는 법령에 근거가 있는 경우에 한하여 제3자에게 제공합니다.</p>
            <ul>
                <li><strong>에픽게임즈 코리아</strong>
                    <ul>
                        <li>제공목적: 이벤트 주관 및 기획</li>
                        <li>제공항목: 성명, 전화번호, 이메일주소, 직무, 학교, 학과, 직책, 이메일수신동의여부, 참가내역, 시청정보, 참여내역</li>
                        <li>보유·이용기간: 행사 종료 후 1년</li>
                    </ul>
                    </li>
                <li><strong>에픽게임즈</strong>
                    <ul>
                        <li>제공목적: 이벤트 주관 및 기획, 이메일을 통한 정보 제공 및 이용 안내</li>
                        <li>제공항목: 성명, 전화번호, 이메일주소, 직무, 학교, 학과, 직책, 이메일수신동의여부, 참가내역, 시청정보, 참여내역</li>
                        <li>보유·이용기간: 행사 종료 후 1년</li>
                    </ul>
                    </li>
            </ul>

            <h3>제6조(개인정보 처리의 위탁)</h3>
            <p>회사는 원활한 업무 처리를 위하여 다음과 같이 개인정보 처리업무를 위탁합니다.</p>
            <ul>
                <li><strong>수탁자: (주) 그리프</strong>
                    <ul>
                        <li>위탁업무: 고객 전화상담 응대, 이벤트 안내, 사전등록 안내, 결제 및 회원등록 안내</li>
                    </ul>
                    </li>
            </ul>
            <p>회사는 위탁계약 체결 시 「개인정보 보호법」 제26조에 따라 위탁업무 수행 목적 외 처리 금지, 안전성 확보조치, 재위탁 제한, 관리·감독, 손해배상 등 책임사항을 문서에 명시하고 수탁자를 감독합니다.</p>

            <h3>제7조(개인정보의 국외 이전)</h3>
            <p>회사는 국외 사업자와의 이벤트 운영 등으로 개인정보가 국외로 이전되는 경우 「개인정보 보호법」 제28조의8에 따라 이전받는 자, 이전 국가, 이전 일시·방법, 이전 항목, 보유·이용기간, 거부 방법 등을 사전에 공개하거나 별도 고지·동의 절차를 거쳐 처리합니다.</p>

            <h3>제8조(개인정보의 파기절차 및 파기방법)</h3>
            <ol>
                <li>회사는 개인정보 보유기간의 경과, 처리목적 달성 등 파기사유가 발생한 경우 지체없이 해당 개인정보를 파기합니다.</li>
                <li>다른 법령에 따라 보존이 필요한 경우에는 해당 개인정보를 별도의 DB로 분리 보관합니다.</li>
                <li>파기절차 및 방법
                    <ul>
                        <li>전자적 파일: 복구 또는 재생되지 않도록 안전하게 삭제</li>
                        <li>종이 문서: 분쇄 또는 소각</li>
                    </ul>
                </li>
            </ol>

            <h3>제9조(개인정보의 안전성 확보조치)</h3>
            <p>회사는 개인정보의 안전성 확보를 위하여 다음과 같은 조치를 시행합니다.</p>
            <ol>
                <li>관리적 조치: 내부관리계획 수립·시행, 임직원 교육, 접근권한 최소화</li>
                <li>기술적 조치: 접근통제시스템 운영, 암호화, 접속기록 보관·점검, 보안프로그램 설치</li>
                <li>물리적 조치: 전산실·자료보관실 등의 출입통제</li>
            </ol>

            <h3>제10조(개인정보 자동 수집 장치의 설치·운영 및 거부)</h3>
            <ol>
                <li>회사는 이용자 맞춤서비스 제공을 위해 쿠키를 사용할 수 있습니다.</li>
                <li>이용자는 웹브라우저 설정을 통해 쿠키 저장을 거부할 수 있습니다.
                    <ul>
                        <li>예: 브라우저 설정 &gt; 개인정보/보안 &gt; 쿠키 차단</li>
                    </ul>
                </li>
                <li>쿠키 저장 거부 시 일부 서비스 이용에 제한이 발생할 수 있습니다.</li>
            </ol>

            <h3>제11조(정보주체와 법정대리인의 권리·의무 및 행사방법)</h3>
            <ol>
                <li>정보주체는 회사에 대해 언제든지 개인정보 열람, 정정·삭제, 처리정지, 동의 철회 등을 요구할 수 있습니다.</li>
                <li>권리 행사는 서면, 전자우편, FAX 등을 통해 할 수 있으며 회사는 지체없이 조치합니다.</li>
                <li>권리 행사는 법정대리인 또는 위임을 받은 대리인을 통해 할 수 있으며, 이 경우 관련 위임서류 제출이 필요합니다.</li>
                <li>개인정보 열람 및 처리정지 요구는 법 제35조 제5항, 제37조 제2항에 따라 제한될 수 있습니다.</li>
                <li>정보주체는 개인정보 보호법 제37조의2에 따라 완전히 자동화된 결정에 대해 거부 또는 설명 요구를 할 수 있습니다. 회사는 관련 절차가 적용되는 경우 법령에 따라 필요한 조치를 시행합니다.</li>
            </ol>

            <h3>제12조(개인정보 보호책임자 및 담당부서)</h3>
            <p>회사는 개인정보 처리에 관한 업무를 총괄하여 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제를 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.</p>
            <ul>
                <li>개인정보 보호책임자: 오승훈 실장</li>
                <li>연락처: 02-326-3701, info@griff.co.kr</li>
            </ul>
            <p>정보주체는 회사의 서비스 이용 중 발생한 모든 개인정보보호 관련 문의를 위 연락처로 할 수 있으며, 회사는 지체없이 답변 및 처리하겠습니다.</p>

            <h3>제13조(개인정보 열람청구 접수·처리부서)</h3>
            <p>정보주체는 「개인정보 보호법」 제35조에 따른 개인정보 열람청구를 아래 부서에 할 수 있습니다.</p>
            <ul>
                <li>부서명: 개인정보보호 담당부서</li>
                <li>연락처: 02-326-3701, info@griff.co.kr</li>
            </ul>
            <p>또한 개인정보보호 포털(<a href="https://www.privacy.go.kr" target="_blank" rel="noopener noreferrer">www.privacy.go.kr</a>)을 통해서도 개인정보 열람 등 권리행사를 신청할 수 있습니다.</p>

            <h3>제14조(권익침해 구제방법)</h3>
            <p>정보주체는 개인정보 침해로 인한 구제를 위하여 아래 기관에 상담 또는 분쟁조정을 신청할 수 있습니다.</p>
            <ul>
                <li>개인정보분쟁조정위원회: (국번없이) 1833-6972, <a href="https://www.kopico.go.kr" target="_blank" rel="noopener noreferrer">www.kopico.go.kr</a></li>
                <li>개인정보침해신고센터(한국인터넷진흥원): (국번없이) 118, <a href="https://privacy.kisa.or.kr" target="_blank" rel="noopener noreferrer">privacy.kisa.or.kr</a></li>
                <li>대검찰청: (국번없이) 1301, <a href="https://www.spo.go.kr" target="_blank" rel="noopener noreferrer">www.spo.go.kr</a></li>
                <li>경찰청 사이버범죄 신고시스템: (국번없이) 182, <a href="https://ecrm.police.go.kr" target="_blank" rel="noopener noreferrer">ecrm.police.go.kr</a></li>
            </ul>

            <h3>제15조(개인정보 처리방침의 변경)</h3>
            <p>이 개인정보 처리방침은 2026년 2월 12일부터 적용됩니다. 관련 법령 또는 서비스 변경사항을 반영하기 위해 처리방침을 수정할 수 있으며, 중요한 변경사항은 홈페이지를 통해 사전에 안내합니다.</p>

        </div><!-- /.v4-detail-content lang-ko -->

        <div class="v4-detail-content lang-en">

            <h3>Article 1 (General Provisions)</h3>
            <p>Epic Lounge (the "Company") complies with the Personal Information Protection Act and other applicable laws, and establishes and discloses the following privacy policy in order to protect the personal information of data subjects and to handle related grievances promptly and smoothly.</p>

            <h3>Article 2 (Purposes of Processing Personal Information)</h3>
            <p>The Company processes personal information for the following purposes. Where a change of purpose is necessary, it implements the measures required under Article 18 of the Personal Information Protection Act.</p>
            <ol>
                <li>Membership registration and management: confirmation of intent to join, identity verification, maintenance and management of membership, prevention of service misuse, notices/notifications, complaint handling</li>
                <li>Event operation and participant management: application intake, selection of winners, identity verification, provision of prizes/services, management of participation records</li>
                <li>Provision of goods or services: service provision, payment and settlement, refund processing, performance of contracts</li>
                <li>Customer inquiries and dispute handling: responding to inquiries, fact-checking, notification of results, dispute mediation</li>
            </ol>

            <h3>Article 3 (Items of Personal Information Processed and Legal Basis)</h3>
            <p>The Company processes the minimum personal information necessary to provide the Services.</p>
            <ol>
                <li>Items processed with the data subject's consent
                    <ul>
                        <li>Membership registration/event application: name, date of birth, ID, password, address, phone number, email address, gender, job function, position, school, department, area of interest</li>
                        <li>Upon consent to marketing: whether consent is given to receive email, whether consent is given to receive SMS</li>
                    </ul>
                </li>
                <li>Items processed to perform a contract and fulfill legal obligations
                    <ul>
                        <li>Payment/settlement: name, contact, email address, payment method information (approval information related to card payment, etc.), refund account information (upon refund)</li>
                        <li>Legally retained information: transaction records, contract/withdrawal-of-subscription records, records of consumer complaints or dispute handling</li>
                    </ul>
                </li>
                <li>Items automatically generated/collected during service use
                    <ul>
                        <li>IP address, cookies, service usage records, access logs, visit records, device information, records of improper use</li>
                    </ul>
                </li>
            </ol>

            <h3>Article 4 (Processing and Retention Period of Personal Information)</h3>
            <ol>
                <li>The Company processes and retains personal information within the retention/use period stipulated by law or the retention/use period consented to by the data subject.</li>
                <li>Main retention periods
                    <ul>
                        <li>Member information: until membership withdrawal</li>
                        <li>Event participation information: 3 years after the end of the event</li>
                        <li>Information related to the provision of goods/services: until completion of supply and payment/settlement</li>
                    </ul>
                </li>
            </ol>
            <p>However, in accordance with applicable laws, information is retained for certain periods as follows.</p>
            <ul>
                <li>Act on the Consumer Protection in Electronic Commerce, etc.
                    <ul>
                        <li>Records on labeling/advertising: 6 months</li>
                        <li>Records on contracts/withdrawal of subscription, payment, and supply of goods: 5 years</li>
                        <li>Records on consumer complaints or dispute handling: 3 years</li>
                    </ul>
                    </li>
                <li>Protection of Communications Secrets Act
                    <ul>
                        <li>Internet log records and access-location tracking data: 3 months</li>
                    </ul>
                    </li>
            </ul>

            <h3>Article 5 (Provision of Personal Information to Third Parties)</h3>
            <p>In principle, the Company processes the data subject's personal information only within the scope of the purposes under Article 2, and provides it to third parties only with the data subject's consent or where there is a legal basis.</p>
            <ul>
                <li><strong>Epic Games Korea</strong>
                    <ul>
                        <li>Purpose of provision: event hosting and planning</li>
                        <li>Items provided: name, phone number, email address, job function, school, department, position, whether consent to receive email is given, participation records, viewing information, participation details</li>
                        <li>Retention/use period: 1 year after the end of the event</li>
                    </ul>
                    </li>
                <li><strong>Epic Games</strong>
                    <ul>
                        <li>Purpose of provision: event hosting and planning, provision of information and use guidance via email</li>
                        <li>Items provided: name, phone number, email address, job function, school, department, position, whether consent to receive email is given, participation records, viewing information, participation details</li>
                        <li>Retention/use period: 1 year after the end of the event</li>
                    </ul>
                    </li>
            </ul>

            <h3>Article 6 (Outsourcing of Personal Information Processing)</h3>
            <p>The Company outsources personal information processing tasks as follows for smooth business handling.</p>
            <ul>
                <li><strong>Trustee: Griff Co., Ltd.</strong>
                    <ul>
                        <li>Outsourced tasks: customer telephone consultation, event guidance, pre-registration guidance, payment and membership registration guidance</li>
                    </ul>
                    </li>
            </ul>
            <p>Upon concluding an outsourcing contract, the Company specifies in a document the matters concerning the prohibition of processing beyond the purpose, technical/managerial protective measures, restriction on re-outsourcing, management/supervision, and liability for damages in accordance with Article 26 of the Personal Information Protection Act, and supervises the trustee.</p>

            <h3>Article 7 (Overseas Transfer of Personal Information)</h3>
            <p>Where personal information is transferred overseas due to event operation with overseas businesses, etc., the Company processes it after disclosing in advance, or undergoing a separate notice/consent procedure, the recipient, the country of transfer, the date/method of transfer, the items transferred, the retention/use period, and the method of refusal, in accordance with Article 28-8 of the Personal Information Protection Act.</p>

            <h3>Article 8 (Procedures and Methods of Destroying Personal Information)</h3>
            <ol>
                <li>When a reason for destruction arises, such as the lapse of the retention period or achievement of the processing purpose, the Company destroys the relevant personal information without delay.</li>
                <li>Where retention is required under other laws, the relevant personal information is stored separately in a separate database.</li>
                <li>Procedures and methods of destruction
                    <ul>
                        <li>Electronic files: safely deleted so that they cannot be recovered or reproduced</li>
                        <li>Paper documents: shredded or incinerated</li>
                    </ul>
                </li>
            </ol>

            <h3>Article 9 (Measures to Ensure the Security of Personal Information)</h3>
            <p>The Company implements the following measures to ensure the security of personal information.</p>
            <ol>
                <li>Managerial measures: establishment and implementation of an internal management plan, employee training, minimization of access authority</li>
                <li>Technical measures: operation of an access control system, encryption, retention and inspection of access records, installation of security programs</li>
                <li>Physical measures: access control to computer rooms, data storage rooms, etc.</li>
            </ol>

            <h3>Article 10 (Installation/Operation of Automatic Personal Information Collection Devices and Refusal Thereof)</h3>
            <ol>
                <li>The Company may use cookies to provide customized services to users.</li>
                <li>Users may refuse the storage of cookies through their web browser settings.
                    <ul>
                        <li>e.g., Browser settings &gt; Privacy/Security &gt; Block cookies</li>
                    </ul>
                </li>
                <li>If the storage of cookies is refused, there may be restrictions on the use of some services.</li>
            </ol>

            <h3>Article 11 (Rights and Obligations of Data Subjects and Legal Representatives and Methods of Exercise)</h3>
            <ol>
                <li>Data subjects may, at any time, request the Company to access, correct/delete, suspend the processing of, or withdraw consent regarding their personal information.</li>
                <li>The exercise of rights may be made in writing, by email, by fax, etc., and the Company acts without delay.</li>
                <li>The exercise of rights may be made through a legal representative or a duly authorized agent, in which case the submission of relevant power-of-attorney documents is required.</li>
                <li>Requests for access to and suspension of the processing of personal information may be restricted under Article 35(5) and Article 37(2) of the Act.</li>
                <li>Pursuant to Article 37-2 of the Personal Information Protection Act, data subjects may refuse or request an explanation regarding fully automated decisions. Where the relevant procedure applies, the Company implements the measures required by law.</li>
            </ol>

            <h3>Article 12 (Personal Information Protection Officer and Responsible Department)</h3>
            <p>The Company designates a Personal Information Protection Officer as below, who oversees and is responsible for personal-information-processing tasks and handles data subjects' complaints and remedies related to personal information processing.</p>
            <ul>
                <li>Personal Information Protection Officer: Seunghoon Oh, General Manager</li>
                <li>Contact: 02-326-3701, info@griff.co.kr</li>
            </ul>
            <p>Data subjects may direct any personal-information-protection inquiries arising during the use of the Company's Services to the above contact, and the Company will respond and handle them without delay.</p>

            <h3>Article 13 (Department Receiving and Handling Requests for Access to Personal Information)</h3>
            <p>Data subjects may file requests for access to personal information under Article 35 of the Personal Information Protection Act to the department below.</p>
            <ul>
                <li>Department: Personal Information Protection Department</li>
                <li>Contact: 02-326-3701, info@griff.co.kr</li>
            </ul>
            <p>Requests to exercise rights, such as access to personal information, may also be made through the Personal Information Portal (<a href="https://www.privacy.go.kr" target="_blank" rel="noopener noreferrer">www.privacy.go.kr</a>).</p>

            <h3>Article 14 (Methods of Remedy for Infringement of Rights)</h3>
            <p>Data subjects may apply for consultation or dispute mediation to the following organizations for remedy against personal information infringement.</p>
            <ul>
                <li>Personal Information Dispute Mediation Committee: (without area code) 1833-6972, <a href="https://www.kopico.go.kr" target="_blank" rel="noopener noreferrer">www.kopico.go.kr</a></li>
                <li>Personal Information Infringement Report Center (KISA): (without area code) 118, <a href="https://privacy.kisa.or.kr" target="_blank" rel="noopener noreferrer">privacy.kisa.or.kr</a></li>
                <li>Supreme Prosecutors' Office: (without area code) 1301, <a href="https://www.spo.go.kr" target="_blank" rel="noopener noreferrer">www.spo.go.kr</a></li>
                <li>National Police Agency Cybercrime Reporting System: (without area code) 182, <a href="https://ecrm.police.go.kr" target="_blank" rel="noopener noreferrer">ecrm.police.go.kr</a></li>
            </ul>

            <h3>Article 15 (Changes to the Privacy Policy)</h3>
            <p>This Privacy Policy takes effect on February 12, 2026. The Company may revise the policy to reflect applicable laws or changes to the Services, and will provide advance notice of any material changes through the website.</p>

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
<?php /* Cache buster: 2026-02-12 00:34:00 */ ?>
