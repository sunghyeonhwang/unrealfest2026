# 라운지(v3) 오버라이드 — SFTP 배포 전용 (이 repo 밖 파일의 추적 사본)

UFS26 작업으로 수정한 **에픽라운지 공통 코드**의 추적용 사본입니다.
실제 배포 위치는 서버의 `/unrealengine/www/v3/` 하위이며, **git 자동 배포 대상이 아니라 SFTP로만 반영**됩니다.

| 사본 | 실제 배포 경로 | 변경 내용 |
|---|---|---|
| `common_header26.php` | `/v3/inc/common_header26.php` | GNB에 "언리얼 페스트 서울 2026"(키컬러 시안) 메뉴 추가, 메뉴 폰트 20px·서브 16px, 검색창 190×40, (백업: `common_header26.php.bak-ufs`) |
| `index.php` | `/v3/index.php` | 언리얼 페스트 기간(2026-06-23~08-21) 한정: 루트(`/`) 접속만 `/unrealfest2026/index.php`로 302 리다이렉트. `/index.php`·`/v3/index.php`는 평소 홈 유지. (백업: `index.php.bak-ufs`) |

⚠️ 수정 시 이 사본과 서버 파일을 함께 업데이트할 것.
