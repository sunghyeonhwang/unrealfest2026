# UFS 2026 관리자 파일 (버전관리용 복사본)

이 폴더의 파일들은 **버전관리/백업 목적의 복사본**입니다.
실제 배포 위치는 Gnuboard 관리자 디렉터리 **`www/v3/adm/`** 입니다 (이 repo 밖).

| 파일 | 역할 | 비고 |
|------|------|------|
| `2026_event2_list.php` | 등록 현황(통계·트랙 그래픽·CSV) | 신규 |
| `2026_event2_remain.php` | 트랙 정원 설정 | 신규 |
| `2026_event2_remain_proc.php` | 정원 저장 처리 | 신규 |
| `admin.menu700.php` | 관리자 좌측 메뉴(이벤트 700) | **기존 공유 파일 수정** — UE FEST 26 그룹 추가/최상단 이동 |
| `2026_event_speaker.php` | 스피커 신청 목록 | **기존 파일 수정** — 카운트 NULL 제외 |

## 배포 방법
```
sftp → www/v3/adm/<파일명>
```
- `admin.menu700.php`, `2026_event_speaker.php`는 기존 공유 파일이므로 배포 전 서버 백업 필수
  (서버 백업: `*.bak20260608`).
- Gnuboard 관리자 로그인(`is_admin`)으로 보호됨.
- 인증/스타일: `_common.php`(인증) + `admin.head/tail`(좌측 메뉴) + 자체 scoped CSS.
