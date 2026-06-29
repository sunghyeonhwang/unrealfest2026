# 라운지(v3) 오버라이드 — SFTP 배포 전용 (이 repo 밖 파일의 추적 사본)

UFS26 작업으로 수정한 **에픽라운지 공통 코드**의 추적용 사본입니다.
실제 배포 위치는 서버의 `/unrealengine/www/v3/` 하위이며, **git 자동 배포 대상이 아니라 SFTP로만 반영**됩니다.

| 사본 | 실제 배포 경로 | 변경 내용 |
|---|---|---|
| `common_header26.php` | `/v3/inc/common_header26.php` | GNB에 "언리얼 페스트 서울 2026"(키컬러 시안) 메뉴 추가, 메뉴 폰트 20px·서브 16px, 검색창 190×40, (백업: `common_header26.php.bak-ufs`) |
| `index.php` | `/v3/index.php` | 언리얼 페스트 기간(2026-06-23~08-21) 한정: 루트(`/`) 접속만 `/unrealfest2026/index.php`로 302 리다이렉트. `/index.php`·`/v3/index.php`는 평소 홈 유지. (백업: `index.php.bak-ufs`) |

⚠️ 수정 시 이 사본과 서버 파일을 함께 업데이트할 것.

## `adm/` — 관리자(별도 `epiclounge_admin` private repo) 추적 사본

아래는 **라운지 공통 코드가 아니라** 별도 관리자 repo(`epiclounge_admin`) 소속 파일의 추적 사본입니다.
이 로컬 repo에는 해당 repo가 체크아웃돼 있지 않아 SFTP 라이브에만 반영되어 있으므로, 여기 사본을 보관합니다.
실제 배포 위치는 서버의 `/unrealengine/www/v3/adm/` 하위입니다.

| 사본 | 실제 배포 경로 | 변경 내용 |
|---|---|---|
| `adm/2026_event2_trend.php` | `/v3/adm/2026_event2_trend.php` | **신규.** 일자별 등록 추세 페이지(목록에서 분리). 막대 차트 + 표(전체누적/취소/전체등록/오프누적/양일권/일일권/온누적/온라인), 날짜 오름차순, 행별 "복사" + "표 전체 복사" 버튼. 접근은 페이지 자체 `is_admin()` |
| `adm/2026_event2_list.php` | `/v3/adm/2026_event2_list.php` | 등록 현황 목록에서 추세 탭·집계쿼리 제거(목록 트래픽 절감), 탭 줄에 "일자별 추세 ↗" 링크 추가. (백업: `2026_event2_list.php.bak-ufs2`) |
| `adm/admin.menu700.php` | `/v3/adm/admin.menu700.php` | 왼쪽 메뉴 "등록 현황"(700320) 아래 "일자별 추세"(700321→`2026_event2_trend.php`) 항목 추가. (백업: `admin.menu700.php.bak-ufs`) |

⚠️ `epiclounge_admin` repo가 있는 환경에서 위 3개 파일을 정식 반영할 것(여기 사본은 임시 추적용).
