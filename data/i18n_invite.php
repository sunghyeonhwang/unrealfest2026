<?php
/* Unreal Fest Seoul 2026 — 초청 등록 i18n (KO/EN) [M4]
 * 스코프 변경: 4개국어 → KO + EN 만(사용자 지시 "영어만"). ZH/JA 미포함.
 * 셀렉트 옵션 텍스트(직업/직무/산업)는 언어별 문자열로 저장됨(외국인 페이지 ticket-en.php와 동일 정책).
 * 트랙/티켓 코드는 키(DAY1_TR1/NORMAL_ALL) → 언어 무관 저장. PHP 7.0 호환.
 */

if (!function_exists('ufs_inv_lang')) {
function ufs_inv_lang($raw) {
    $l = strtolower(trim((string)$raw));
    return ($l === 'en') ? 'en' : 'ko';   // ko/en 만 허용; 그 외(zh/ja/'') → ko
}
}

if (!function_exists('ufs_inv_dict')) {
function ufs_inv_dict($lang) {
  $D = array();
  $D['ko'] = array(
    'html_lang'=>'ko', 'home'=>'홈으로',
    'title'=>'초청 등록',
    'gate_desc'=>'초청 이메일에 포함된 <b class="text-white">초청 코드</b>를 입력해 주세요.',
    'code_label'=>'초청 코드', 'code_ph'=>'예: UFS-XXXX-XXXX', 'confirm'=>'확인',
    'invited_by'=>'<b class="text-white">%s</b>의 초청으로 등록합니다. 등록 정보를 입력해 주세요.',
    'free_badge'=>'무료 초청 (100%)', 'disc_badge'=>'초청 할인 %d%%', 'remain'=>'등록 가능 %d명',
    'agree_title'=>'약관 동의', 'agree_all'=>'전체 동의',
    'agree_req_a'=>'이용약관', 'agree_req_b'=>'개인정보처리방침', 'agree_req_join'=>' 및 ', 'agree_req_tail'=>'에 동의합니다', 'req'=>'(필수)',
    'agree_mkt'=>'마케팅 정보 수신에 동의합니다', 'opt'=>'(선택)',
    'reg_info'=>'등록자 정보',
    'f_name'=>'이름', 'f_email'=>'이메일', 'f_phone'=>'연락처', 'f_job'=>'직업', 'f_company'=>'회사명/소속',
    'f_depart'=>'부서', 'f_grade'=>'직무', 'f_ex1'=>'산업/관심 분야',
    'ph_name'=>'이름', 'ph_email'=>'email@example.com', 'ph_phone'=>'01012345678', 'ph_company'=>'에픽게임즈', 'ph_depart'=>'개발팀',
    'sel'=>'선택해 주세요', 'sel_short'=>'선택',
    'attend'=>'1. 등록자 참석 선택',
    'companion_note'=>'※ 동반자의 <b class="text-[#a1a1aa]">직업·회사명</b>은 등록자와 동일하게 자동 등록됩니다. 동반 없이 1인만 등록하려면 비워 두세요.',
    'companion'=>'동반자',
    'l_ticket'=>'티켓', 'l_day1'=>'Day1 트랙', 'l_day2'=>'Day2 트랙', 'l_tshirt'=>'티셔츠',
    'ticket_ph'=>'티켓 선택', 'day1_ph'=>'Day1 트랙', 'day2_ph'=>'Day2 트랙', 'closed'=>'마감',
    'summary'=>'등록 요약', 'orig_sum'=>'정상가 합계', 'disc_line'=>'초청 할인 %d%%', 'total'=>'총 결제 금액', 'free_word'=>'무료',
    'btn_free'=>'등록 완료', 'btn_pay'=>'결제하기',
    'note_free'=>'등록 완료 후 QR과 조회 링크가 제공됩니다.', 'note_paid'=>'결제 완료 후 QR과 조회 링크가 제공됩니다. 무통장 입금은 준비 중입니다(카드 결제).',
    'note_tail'=>' 이미 등록된 이메일은 재등록할 수 없습니다.',
    // 오류
    'e_code'=>'초청 코드를 확인해 주세요.', 'e_invalid'=>'유효하지 않은 초청 코드입니다.', 'e_inactive'=>'사용이 중지된 초청 코드입니다.', 'e_soldout'=>'이미 모두 등록된 초청 코드입니다.', 'e_expired'=>'사용기간이 만료된 초청 코드입니다.', 'e_notyet'=>'아직 사용할 수 없는 초청 코드입니다.',
    'e_agree'=>'필수 약관에 동의해 주세요.', 'e_rep'=>'대표자(초청 당사자) 정보를 모두 입력해 주세요.',
    'e_over'=>'이 코드로 등록 가능한 인원(%d명)을 초과했습니다.',
    'e_ticket'=>'%s의 티켓을 선택해 주세요.', 'e_day1'=>'%s의 Day1 트랙을 선택해 주세요.', 'e_day2'=>'%s의 Day2 트랙을 선택해 주세요.',
    'e_tshirt'=>'%s의 티셔츠를 선택해 주세요.', 'e_contact'=>'%s의 이메일/연락처를 입력해 주세요.',
    'e_dup_in'=>'동일한 이메일이 중복되었습니다: %s', 'e_dup'=>'%s 은(는) 이미 등록된 이메일입니다.',
    'e_soldout2'=>'초청 잔여 매수가 부족합니다. 새로고침 후 다시 시도해 주세요.', 'e_insert'=>'등록 처리 중 오류가 발생했습니다. 다시 시도해 주세요.',
    'rep_label'=>'대표자', 'companion_n'=>'%d번 동반자',
    // 완료 페이지
    'c_title'=>'초청 등록이 완료되었습니다', 'c_invited'=>'<b class="text-white">%s</b>의 초청으로 등록되었습니다.',
    'c_qr_title'=>'입장 QR 보관 안내', 'c_qr_body'=>'위 QR을 캡처해 두시거나, <a href="myticket.php" class="underline text-[#00C1D5]">티켓 조회</a>에서 등록하신 <b>이메일 + 연락처</b>로 언제든 다시 확인할 수 있습니다.',
    'c_track'=>'트랙', 'c_qr_alt'=>'입장 QR', 'bad'=>'잘못된 접근입니다.',
  );
  $D['en'] = array(
    'html_lang'=>'en', 'home'=>'Home',
    'title'=>'Invitation Registration',
    'gate_desc'=>'Enter the <b class="text-white">invitation code</b> included in your invitation email.',
    'code_label'=>'Invitation code', 'code_ph'=>'e.g. UFS-XXXX-XXXX', 'confirm'=>'Confirm',
    'invited_by'=>'You are registering at the invitation of <b class="text-white">%s</b>. Please fill in your details.',
    'free_badge'=>'Free invitation (100%)', 'disc_badge'=>'Invitation discount %d%%', 'remain'=>'%d seat(s) available',
    'agree_title'=>'Agreement', 'agree_all'=>'Agree to all',
    'agree_req_a'=>'Terms of Service', 'agree_req_b'=>'Privacy Policy', 'agree_req_join'=>' and ', 'agree_req_tail'=>'', 'req'=>'(required)',
    'agree_mkt'=>'I agree to receive marketing communications', 'opt'=>'(optional)',
    'reg_info'=>'Registrant Information',
    'f_name'=>'Full name', 'f_email'=>'Email', 'f_phone'=>'Phone', 'f_job'=>'Occupation', 'f_company'=>'Company / Organization',
    'f_depart'=>'Department', 'f_grade'=>'Role', 'f_ex1'=>'Industry',
    'ph_name'=>'Full name', 'ph_email'=>'email@example.com', 'ph_phone'=>'+1 234 567 8900', 'ph_company'=>'Epic Games', 'ph_depart'=>'Dev Team',
    'sel'=>'Select', 'sel_short'=>'Select',
    'attend'=>'1. Registrant — session selection',
    'companion_note'=>'※ A companion’s <b class="text-[#a1a1aa]">occupation & company</b> are auto-filled from the registrant. Leave blank to register alone.',
    'companion'=>'Companion',
    'l_ticket'=>'Ticket', 'l_day1'=>'Day 1 track', 'l_day2'=>'Day 2 track', 'l_tshirt'=>'T-shirt',
    'ticket_ph'=>'Select a ticket', 'day1_ph'=>'Day 1 track', 'day2_ph'=>'Day 2 track', 'closed'=>'Full',
    'summary'=>'Registration Summary', 'orig_sum'=>'Subtotal (regular)', 'disc_line'=>'Invitation discount %d%%', 'total'=>'Total', 'free_word'=>'Free',
    'btn_free'=>'Complete registration', 'btn_pay'=>'Proceed to payment',
    'note_free'=>'A QR code and lookup link will be provided after registration.', 'note_paid'=>'A QR code and lookup link will be provided after payment. Bank transfer is coming soon (card only).',
    'note_tail'=>' An email that is already registered cannot be used again.',
    'e_code'=>'Please check your invitation code.', 'e_invalid'=>'Invalid invitation code.', 'e_inactive'=>'This invitation code has been deactivated.', 'e_soldout'=>'This invitation code is fully used.', 'e_expired'=>'This invitation code has expired.', 'e_notyet'=>'This invitation code is not active yet.',
    'e_agree'=>'Please agree to the required terms.', 'e_rep'=>'Please complete all registrant information.',
    'e_over'=>'You exceeded the number of seats available for this code (%d).',
    'e_ticket'=>'Please select a ticket for %s.', 'e_day1'=>'Please select a Day 1 track for %s.', 'e_day2'=>'Please select a Day 2 track for %s.',
    'e_tshirt'=>'Please select a T-shirt size for %s.', 'e_contact'=>'Please enter the email/phone for %s.',
    'e_dup_in'=>'Duplicate email: %s', 'e_dup'=>'%s is already registered.',
    'e_soldout2'=>'Not enough seats remaining for this invitation. Please refresh and try again.', 'e_insert'=>'An error occurred during registration. Please try again.',
    'rep_label'=>'Registrant', 'companion_n'=>'Companion #%d',
    'c_title'=>'Your invitation registration is complete', 'c_invited'=>'Registered at the invitation of <b class="text-white">%s</b>.',
    'c_qr_title'=>'Entry QR', 'c_qr_body'=>'Save the QR above, or look it up anytime with your registered <b>email + phone</b> at <a href="myticket.php" class="underline text-[#00C1D5]">Ticket Lookup</a>.',
    'c_track'=>'Track', 'c_qr_alt'=>'Entry QR', 'bad'=>'Invalid access.',
  );
  return isset($D[$lang]) ? $D[$lang] : $D['ko'];
}
}

if (!function_exists('ufs_inv_jobs')) {
function ufs_inv_jobs($lang){ return $lang==='en'
  ? array('Professional','Student','Educator / Institution','Indie developer','Freelancer')
  : array('직장인','학생','교육자/교육기관','인디 개발자','프리랜서'); }
}
if (!function_exists('ufs_inv_grades')) {
function ufs_inv_grades($lang){ return $lang==='en'
  ? array('Visual Art','Programming','Production','Engineering','Design','Planning','R&D','IT','Director / PD','Business / Marketing','C-level','Other')
  : array('비주얼 아트','프로그래밍','프로덕션','엔지니어링','설계','기획','R&D','IT','감독/PD','비즈니스/마케팅','C-level','기타'); }
}
if (!function_exists('ufs_inv_ex1s')) {
function ufs_inv_ex1s($lang){ return $lang==='en'
  ? array('Games','Film & TV','Broadcast & Live Events','Animation','Architecture','Automotive','Manufacturing / Simulation','Software & Tools','VR / AR','Education','Other')
  : array('게임','영화 & TV','방송 & 라이브 이벤트','애니메이션','건축','자동차','제조/시뮬레이션','소프트웨어 & 툴 개발','VR·AR','교육','기타'); }
}
if (!function_exists('ufs_inv_ticket_label')) {
function ufs_inv_ticket_label($code,$lang){
  $m = ($lang==='en')
    ? array('NORMAL_ALL'=>'2-Day Pass (Aug 20–21)','NORMAL_20'=>'1-Day Pass · Day 1','NORMAL_21'=>'1-Day Pass · Day 2')
    : array('NORMAL_ALL'=>'양일권 (8.20~21)','NORMAL_20'=>'1일권 · Day 1','NORMAL_21'=>'1일권 · Day 2');
  return isset($m[$code]) ? $m[$code] : $code;
}
}
if (!function_exists('ufs_inv_track_label')) {
function ufs_inv_track_label($v, $lang, $koLabel = ''){
  if ($lang !== 'en') {
    if ($koLabel !== '') return $koLabel;   // 폼: 실제 $UFS_TRACKS DB 라벨 우선
    $ko = array('DAY1_TR1'=>'게임: 프로그래밍','DAY1_TR2'=>'게임: 아트','DAY1_TR3'=>'미디어 & 엔터테인먼트','DAY1_TR4'=>'공통',
                'DAY2_TR1'=>'게임: 프로그래밍','DAY2_TR2'=>'게임: 아트','DAY2_TR3'=>'미디어 & 엔터테인먼트','DAY2_TR4'=>'제조 및 시뮬레이션');
    return isset($ko[$v]) ? $ko[$v] : $v;
  }
  $m = array('DAY1_TR1'=>'Game: Programming','DAY1_TR2'=>'Game: Art','DAY1_TR3'=>'Media & Entertainment','DAY1_TR4'=>'Common',
             'DAY2_TR1'=>'Game: Programming','DAY2_TR2'=>'Game: Art','DAY2_TR3'=>'Media & Entertainment','DAY2_TR4'=>'Manufacturing & Simulation');
  return isset($m[$v]) ? $m[$v] : $v;
}
}
