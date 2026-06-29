<?php
$menu['menu700'] = array(
    array('700000', '이벤트 관리', G5_URL . '/contents/event_list.php?category=%EC%BB%A4%EB%AE%A4%EB%8B%88%ED%8B%B0%20%EC%9D%B2%EB%B2%A4%ED%8A%B8', 'rsc_event_list', 1),

    // UE FEST 26 (등록/결제)
    array('700300', 'UE FEST 26', '' . G5_ADMIN_URL . '/2026_event2_list.php', '2026_event2_list', 1),
    array('700320', '등록 현황', '' . G5_ADMIN_URL . '/2026_event2_list.php', '2026_event2_list'),
    array('700321', '일자별 추세', '' . G5_ADMIN_URL . '/2026_event2_trend.php', '2026_event2_list'),
    array('700330', '트랙 정원', '' . G5_ADMIN_URL . '/2026_event2_remain.php', '2026_event2_remain'),
    array('700310', '스피커 신청', '' . G5_ADMIN_URL . '/2026_event_speaker.php', '2026_event_speaker'),
    array('700340', '아젠다 관리', '' . G5_ADMIN_URL . '/2026_agenda_list.php', '2026_agenda_list'),
    array('700350', '스케줄 CSV 업로드', '' . G5_ADMIN_URL . '/2026_agenda_import.php', '2026_agenda_import'),
    array('700360', '결제 로그', '' . G5_ADMIN_URL . '/2026_event2_log.php', '2026_event2_log'),
    array('700365', '단체 할인 설정', '' . G5_ADMIN_URL . '/2026_group_config.php', '2026_group_config'),
    array('700366', '단체 쿠폰 관리', '' . G5_ADMIN_URL . '/2026_coupon.php', '2026_coupon'),
    array('700367', '단체 등록 현황', '' . G5_ADMIN_URL . '/2026_group_list.php', '2026_group_list'),

    // 시작해요 언리얼 26
    array('700400', '시작해요 언리얼 26', '' . G5_ADMIN_URL . '/2026_start_unreal_register_list.php', '2026_start_unreal_list', 1),
    array('700410', '등록 리스트', '' . G5_ADMIN_URL . '/2026_start_unreal_register_list.php', '2026_start_unreal_list'),
    // array('700420', '시청 리스트', '' . G5_ADMIN_URL . '/2026_start_unreal_live_list.php', '2026_start_unreal_live'), // D1로 대체
    array('700425', '시청 리스트', '' . G5_ADMIN_URL . '/2026_start_unreal_live_list_d1.php', '2026_start_unreal_live_d1'),
    array('700430', '다시보기 현황', '' . G5_ADMIN_URL . '/2026_start_unreal_replay_list.php', '2026_start_unreal_replay'),
    array('700440', '라이브 현황', '' . G5_ADMIN_URL . '/2026_start_unreal_live_status.php', '2026_start_unreal_status'),
    array('700450', '다시보기 콘텐츠', '' . G5_ADMIN_URL . '/2026_start_unreal_replay_content.php', '2026_start_unreal_replay_content'),
    array('700460', '다시보기 통계', '' . G5_ADMIN_URL . '/2026_start_unreal_replay_status.php', '2026_start_unreal_replay_status'),
    array('700470', '라이브 설정', '' . G5_ADMIN_URL . '/2026_start_unreal_live_config.php', '2026_start_unreal_live_config'),

    // 시작해요 25
    array('700200', '시작해요 25', '' . G5_ADMIN_URL . '/rsc_event_list.php?sub_menu=700200', 'rsc_event_list', 1),
    array('700210', '신청 목록', '' . G5_ADMIN_URL . '/2025tw_event_list.php', 'rsc_event_list'),
    array('700220', '라이브 접속 리스트', '' . G5_ADMIN_URL . '/2025tw_live_list60.php', '2023_live_list'),
    array('700230', '문의내역', '' . G5_ADMIN_URL . '/2025tw_event_inquery_list.php', '2023_event_inquery_list'),

    // UE FEST 25
    array('700700', 'UE FEST 25', '' . G5_ADMIN_URL . '/2024_event2_list.php', '2024_event2_list', 1),
    array('700710', '신청목록', '' . G5_ADMIN_URL . '/2025_event2_list.php', '2025_event2_list'),
    array('700720', '이벤트 잔여석', '' . G5_ADMIN_URL . '/2025_event2_remain.php', '2025_event2_remain'),
    array('700730', '쿠폰목록', '' . G5_ADMIN_URL . '/2025_event2_coupon_list.php', '2025_event2_coupon_list'),
    array('700740', '단체신청', '' . G5_ADMIN_URL . '/2025_event2_coupon_team_list.php', '2025_event2_coupon_team_list'),
    array('700750', '스폰서쿠폰목록', '' . G5_ADMIN_URL . '/2025_event2_coupon_sp_list.php', '2025_event2_coupon_sp_list'),
    array('700760', '라이브접속목록', '' . G5_ADMIN_URL . '/2025_live_list.php', '2025_live_list'),
    array('700707', '스피커', '' . G5_ADMIN_URL . '/2025_event_speaker.php', '2025_event_speaker'),

    // UE FEST 24
    array('700600', 'UE FEST 24', '' . G5_ADMIN_URL . '/2024_event2_list.php', '2024_event2_list', 1),
    array('700610', '이벤트 목록', '' . G5_ADMIN_URL . '/2024_event2_list.php', '2024_event2_list'),
    array('700620', '이벤트 잔여석', '' . G5_ADMIN_URL . '/2024_event2_remain.php', '2024_event2_remain'),
    array('700630', '쿠폰목록', '' . G5_ADMIN_URL . '/2024_event2_coupon_list.php', '2024_event2_coupon_list'),

    // 언리얼챌린지
    array('700800', '언리얼챌린지', '' . G5_ADMIN_URL . '/event_list2.php', 'event_list2', 1),
    array('700820', '25 언리얼챌린지', '' . G5_ADMIN_URL . '/event_list3.php', 'event_list3'),
    array('700810', '24 언리얼챌린지', '' . G5_ADMIN_URL . '/event_list2.php', 'event_list2'),
);
?>
