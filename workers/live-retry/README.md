# ufs2026-live-retry — 라이브 재시도 프록시 (Cloudflare Worker) 추적 사본

**실제 작업/배포 경로는 이 repo 밖의 `live-retry-worker/`** 입니다(그 폴더는 git 추적이 안 됩니다).
운영 중인 엣지 코드가 버전 관리 밖에 있으면 잃어버리기 쉬워 여기에 사본을 둡니다.

| 사본 | 실제 경로 | 배포 |
|---|---|---|
| `index.js` | `live-retry-worker/src/index.js` | `cd live-retry-worker && CLOUDFLARE_API_TOKEN=$CF_API_TOKEN npx wrangler deploy` |
| `wrangler.toml` | `live-retry-worker/wrangler.toml` | 라우트: `epiclounge.co.kr/unrealfest2026/*`, `/v3/unrealfest2026/*` |

⚠️ 수정 시 **양쪽을 함께 갱신**하고 배포까지 해야 합니다. 사본만 고치면 아무 일도 일어나지 않습니다.

## 하는 일

원본(cafe24)이 503 을 뱉으면 사용자 대신 자동 재시도해 오류 화면을 감춥니다.
원본 대기 상한 8초, 최대 3회 시도.

## ⚠️ NO_RETRY 목록을 반드시 확인할 것

결제·콜백과 **대량 발송 스크립트**는 재시도 대상에서 빼야 합니다.
2026-08-26 설문 LMS 발송(53초 소요)이 8초 타임아웃에 걸려 Worker 가 같은 GET 을
두 번 더 던졌고, 1,667명에게 평균 2.6통(약 4,300통)이 나갔습니다 — 회수 불가.

**오래 걸리는 GET 은 재시도 대상이 아니라, 그 자체로 부작용이 있는 요청입니다.**
새 발송·정산·집계 엔드포인트를 만들면 이름이 `NO_RETRY` 정규식에 걸리는지 먼저 확인하세요.

동작 확인: 응답에 `x-ufs-proxy` 헤더가 있으면 재시도 대상, 없으면 NO_RETRY 통과입니다.

```
curl -sD- -o /dev/null https://epiclounge.co.kr/unrealfest2026/live.php | grep -i x-ufs-proxy
```
