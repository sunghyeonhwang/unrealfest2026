<?php
/* 토스 결제 데모 — 1일권 (paments/ticket-day.php) · PG 심사용 */
$CK = array(
    'title' => '1일권 등록',
    'products' => array(
        array('code' => 'NORMAL_20', 'label' => '오프라인 1일권 · Day 1',
              'name' => '언리얼 페스트 서울 2026 1일권(12월 3일)'),
        array('code' => 'NORMAL_21', 'label' => '오프라인 1일권 · Day 2',
              'name' => '언리얼 페스트 서울 2026 1일권(12월 4일)'),
    ),
);
require __DIR__ . '/_checkout.php';
