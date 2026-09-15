# 다시보기(Vimeo+PDF) — 구현 메모

기획서: [REPLAY_PLANNING.md](./REPLAY_PLANNING.md) · 구현일: 2026-09-15

## 파일 구성

| 파일 | 역할 |
|------|------|
| `replay/index.php` | 사용자 페이지 — 체크인 + 목록(Day/트랙/검색) + 상세 모달(Vimeo 재생·PDF) |
| `replay/pdf.php` | PDF 게이트 — 서버 인증 재확인 후 Cloudflare 링크로 리다이렉트 + 이용 로그 |
| `replay/_common_replay.php` | 공용 헬퍼(설정·로그·인증·Vimeo 임베드) |
| `adm/2026_replay_vod.php` | 관리자(버전관리 복사본) — 배포 위치는 **`www/v3/adm/`** |
| `adm/admin.menu700.php` | 메뉴 700378 추가(복사본 — **반드시 서버 최신본 기준으로 병합** 후 배포. 2026-09-15 구버전 복사본 배포로 메뉴 유실 사고 있었음) |
| `newsletter/replay-open.html` | 다시보기 오픈 안내 뉴스레터 본문(`nl_replay` 슬롯) |
| `_live_notify.php` | `nl_replay` 슬롯 추가(오프라인 등록자 전체 · Resend 분산 발송) |

## DB (관리자 첫 접속 시 자동 생성)

- `cb_unreal_2026_replay_vod` — 세션별: 노출 / Vimeo ID·해시 / 썸네일 / 영상 공개 / PDF URL·표시명·크기 / PDF 공개
- `cb_unreal_2026_replay_log` — gate_ok·gate_fail(마스킹 이메일)·view·play·pdf·pdf_fail (체크인 속도제한 판정에도 사용)
- 설정(`cb_unreal_2026_config`): `replay_enabled` `replay_start` `replay_end` `replay_notice` `replay_pdf_domain`

## 이용 자격 (체크인 통과 조건)

`apply_temp_yn='N'` AND `apply_pay_status<>0` AND `apply_product_code<>'ONLINE'`
— 이메일(대소문자 무시) + 연락처 뒤 8자리 일치. 관리자는 게이트 우회.

## 오픈 절차

1. 관리자 `2026_replay_vod.php` 배포(+메뉴 파일) → 접속 1회(테이블 생성)
2. **PDF 허용 도메인** 입력(Cloudflare 폴더 도메인 확정 후)
3. Vimeo 영상 등록: 비공개 링크 전체 URL 붙여넣기(ID·해시 자동 분리) → 재생 테스트 → 영상 공개 체크
   - Vimeo 쪽 설정: Embed only + Specific domains(`epiclounge.co.kr`) + 다운로드 비활성
4. PDF 링크 등록(https만 허용) → 링크 테스트 → PDF 공개 체크
5. 운영 설정에서 **서비스 활성화** + 제공 기간 입력
6. 뉴스레터: `newsletter/replay-open.html` 의 ★제공 기간 날짜 수정 → 관리자 라이브 안내 발송에서
   `뉴스레터 다시보기 오픈` 슬롯에 본문 URL·발송 창 설정 → 테스트 발송으로 수신 확인
   - 발송 창은 Cloudflare Worker(ufs2026-live-notify) 스케줄러가 그 시간대에 돌고 있어야 실제 발송됨

## 기존 replay.php (루트, YouTube)

신규 페이지 검수 완료 후 처리 결정(기획 5절): 리다이렉트 / 관리자용 유지 / 제거.
