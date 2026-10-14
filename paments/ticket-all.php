<?php
/* 토스 결제 데모 — 양일권 (paments/ticket-all.php) · PG 심사용 */
$CK = array(
    'title' => '양일권 등록',
    'products' => array(
        array('code' => 'NORMAL_ALL', 'label' => '오프라인 양일권',
              'name' => '언리얼 페스트 서울 2026 양일권(12월 3일~4일)'),
    ),
);
require __DIR__ . '/_checkout.php';
