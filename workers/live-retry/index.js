/**
 * Unreal Fest Seoul 2026 — 라이브 입장 재시도 프록시 (Cloudflare Worker)
 *
 * 배경: 원본(cafe24 웹호스팅)이 간헐적으로 503 을 뱉는다. 평시 2~3%, 부하가 몰리면 크게 오른다
 *       — 8/11 얼리버드 마감일 피크(19시)에는 순방문자 1,061명·요청 38,449건에 503 이 10.57% 였고
 *       그 수준이 5시간 지속됐다. 행사일 예상 부하는 그보다 약 3배다.
 *       캐시 MISS 인 요청은 사용자가 오류를 그대로 본다.
 *
 * 하는 일: 원본이 5xx 를 주면 짧은 간격으로 자동 재시도(최대 2회). 버스트가 간헐적이라
 *          대부분 재시도에서 성공한다 → 사용자에게는 503 이 보이지 않는다.
 *          끝내 실패하면 자동 새로고침되는 대기 안내 페이지를 보여준다.
 *
 * 원칙
 *  - 기본은 완전 통과(pass-through). 쿠키·헤더·본문·리다이렉트를 그대로 전달한다.
 *    (PHP 세션 로그인·게이트 POST 가 깨지면 안 된다)
 *  - 503 은 웹서버가 앱 실행 전에 거절한 것이므로 POST 재시도도 안전하다(중복 처리 없음).
 *  - Worker 내부에서 예외가 나면 원본으로 직결 전달 — 어떤 경우에도 서비스가 죽지 않게.
 */

const RETRY_STATUS = new Set([500, 502, 503, 504, 520, 521, 522, 523, 524]);

// 본문이 있는 요청(POST 등)은 503 일 때만 재시도한다.
// 503 = 웹서버가 앱을 실행하기 전에 거절 → 아무 처리도 일어나지 않았음이 보장된다.
// 500/504 는 앱이 이미 실행됐을 수 있어 재시도하면 중복 처리(이중 결제·이중 등록) 위험이 있다.
const RETRY_STATUS_BODY = new Set([503]);

// 결제·콜백·대량발송은 아예 재시도하지 않고 통과시킨다(GET 이어도).
// 결제창 복귀·PG 콜백은 한 번의 왕복이 곧 거래라 어떤 재시도도 위험하다.
//
// ⚠️ 발송 스크립트를 여기 넣은 이유 (2026-08-26 사고):
//    설문 안내 LMS 를 GET 으로 돌렸는데 53초가 걸려 8초 타임아웃에 두 번 걸렸고,
//    Worker 가 같은 요청을 2회 더 던져 원본에서 3개 프로세스가 동시에 돌았다.
//    1,667명에게 평균 2.6통(약 4,300통)이 나갔다 — 회수 불가.
//    "오래 걸리는 GET"은 재시도 대상이 아니라 그 자체로 부작용이 있는 요청이다.
//    알림톡 경로가 무사했던 것은 UNIQUE(apply_no, ln_day) 선점이 막아 준 덕이고,
//    Worker 가 안 던진 덕이 아니다 — 방어는 양쪽 모두에 있어야 한다.
const NO_RETRY = /(apply_pay|booth-pay|ticket-group-pay|ticket-invite-pay|paypal|_refund|setup_db|_live_notify|_svsms|_win_sms|_chat_collect|_sms|_send|_notify|_batch)/i;
const MAX_TRIES = 3;              // 최초 1회 + 재시도 2회
const DELAYS_MS = [400, 1200];    // 재시도 간격

// 원본 응답 대기 상한. 이걸 안 걸면 Cloudflare 기본값(최대 100초)까지 기다린 뒤에야
// 다음 시도로 넘어가, 3회 시도에 수 분이 걸린다. 원본이 정상일 때 P99 가 1초 미만이므로
// 8초를 넘겼다면 그 연결은 사실상 죽은 것으로 보고 빨리 포기하는 편이 낫다.
//   → 최악 지연: 100초×3 = 5분  ⇒  8초×3 + 대기 1.6초 ≈ 26초
const ORIGIN_TIMEOUT_MS = 8000;

export default {
  async fetch(request, env, ctx) {
    try {
      return await proxy(request);
    } catch (e) {
      // 무슨 일이 있어도 서비스가 죽지 않게 폴백.
      // ⚠️ GET/HEAD 만 원본 직결로 넘긴다 — 본문 있는 요청은 위에서 이미 body 를 읽었으므로
      //    같은 request 로 다시 fetch 하면 'body already used' 로 실패한다.
      const m = request.method.toUpperCase();
      if (m === 'GET' || m === 'HEAD') {
        try { return await fetch(request); } catch (_) {}
      }
      return new Response('일시적인 오류입니다. 잠시 후 다시 시도해 주세요.', {
        status: 503, headers: { 'content-type': 'text/plain; charset=utf-8' },
      });
    }
  },
};

async function proxy(request) {
  // 재귀 방지(같은 존 서브요청은 보통 Worker 를 다시 타지 않지만 안전장치)
  if (request.headers.get('x-ufs-retry') === '1') return fetch(request);

  const method = request.method.toUpperCase();
  const hasBody = method !== 'GET' && method !== 'HEAD';

  // 결제·콜백 경로는 손대지 않고 그대로 통과
  if (NO_RETRY.test(new URL(request.url).pathname)) return fetch(request);

  // POST 본문은 재시도를 위해 버퍼링
  const body = hasBody ? await request.arrayBuffer() : undefined;
  const retryable = hasBody ? RETRY_STATUS_BODY : RETRY_STATUS;

  const headers = new Headers(request.headers);
  headers.set('x-ufs-retry', '1');

  let last = 0;
  for (let i = 0; i < MAX_TRIES; i++) {
    if (i > 0) await sleep(DELAYS_MS[i - 1] ?? 1200);
    let res;
    try {
      res = await fetch(new Request(request.url, {
        method,
        headers,
        body,
        redirect: 'manual',           // 302 는 브라우저에 그대로 넘겨 URL 이 정상 갱신되게
        signal: AbortSignal.timeout(ORIGIN_TIMEOUT_MS),
      }));
      // ⚠️ cf:{cacheTtl:...} 를 주면 CF 가 이 응답을 캐시 대상으로 취급해 Set-Cookie 를 제거하고
      //    개인화 페이지가 공유 캐시에 올라갈 수 있다. live.php 는 원본이 no-cache 를 보내므로
      //    아무 옵션 없이 그대로 전달하는 것이 안전하다.
    } catch (e) {
      // 타임아웃(AbortError) 과 연결 실패를 같이 받는다
      last = 599;
      if (hasBody) break;              // 본문 요청은 재시도하지 않는다(중복 처리 위험) → 대기 안내로
      continue;                        // GET 은 타임아웃·연결 실패도 재시도 대상
    }
    last = res.status;
    if (!retryable.has(res.status)) {
      // 정상·리다이렉트·4xx 는 그대로 전달(쿠키/헤더 보존)
      const out = new Response(res.body, res);
      out.headers.set('x-ufs-proxy', i === 0 ? 'ok' : `ok-retry${i}`);
      return out;
    }
  }
  return waitingPage(last);
}

function sleep(ms) { return new Promise((r) => setTimeout(r, ms)); }

function waitingPage(status) {
  const html = `<!doctype html><html lang="ko"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="4">
<title>잠시만 기다려 주세요 — Unreal Fest Seoul 2026</title>
<style>
:root{color-scheme:dark}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:radial-gradient(900px 500px at 78% -8%,rgba(0,193,213,.12),transparent 60%),#08080a;
  color:#eaeaef;font-family:system-ui,-apple-system,'Apple SD Gothic Neo','Malgun Gothic',sans-serif;
  word-break:keep-all;padding:24px}
.box{max-width:440px;text-align:center}
h1{font-size:22px;font-weight:900;margin:0 0 12px;color:#fff}
p{font-size:14.5px;line-height:1.8;color:#9a9aa6;margin:0 0 20px}
.sp{width:44px;height:44px;margin:0 auto 22px;border:3px solid rgba(255,255,255,.15);
  border-top-color:#00C1D5;border-radius:50%;animation:sp 1s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
.btn{display:inline-block;padding:13px 30px;background:#00C1D5;color:#00232a;font-weight:900;
  font-size:15px;text-decoration:none}
.dim{font-size:11.5px;color:#5f5f6b;margin-top:18px}
</style></head><body><div class="box">
<div class="sp"></div>
<h1>접속이 잠시 지연되고 있습니다</h1>
<p>잠시 후 <b style="color:#cfd0d6">자동으로 다시 연결</b>됩니다.<br>이 화면이 계속 보이면 아래 버튼을 눌러 주세요.</p>
<a class="btn" href="/unrealfest2026/live.php">다시 시도</a>
<div class="dim">Unreal Fest Seoul 2026 · 온라인 라이브 (${status})</div>
</div></body></html>`;
  return new Response(html, {
    status: 503,
    headers: {
      'content-type': 'text/html; charset=utf-8',
      'cache-control': 'no-store',
      'retry-after': '4',
      'x-ufs-proxy': 'waiting',
    },
  });
}
