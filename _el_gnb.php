<?php
/* 에픽라운지 공통 GNB (v4) — 다크 고정 자체 구현(Tailwind 충돌 없이 #el-gnb 스코프).
 * 2단 헤더 상단 띠: 페이지 최상단에서만 노출, 스크롤하면 위로 숨고 자체 GNB(#site-header)만 남음.
 * 외부 CSS/JS(common26/jquery/reset) 미사용 → 전역 충돌 0. 링크/에셋은 epiclounge 절대경로. */
$EL = 'https://epiclounge.co.kr';
?>
<style>
/* ===== 에픽라운지 GNB (다크 고정, 스코프) ===== */
#el-gnb{position:fixed;top:0;left:0;right:0;z-index:60;height:90px;background:#0a0a0e;border-bottom:1px solid rgba(255,255,255,.08);color:#fff;font-family:'Inter Tight','Noto Sans KR',Arial,sans-serif;transition:transform .35s cubic-bezier(.4,0,.2,1);}
html.el-scrolled #el-gnb{transform:translateY(-100%);pointer-events:none;}
#el-gnb .el-inner{max-width:80rem;margin:0 auto;height:90px;padding:0 1.5rem;display:flex;align-items:center;gap:1.25rem;}
#el-gnb a{text-decoration:none;}
#el-gnb .el-logo img{width:190px;height:auto;display:block;}
#el-gnb .el-nav{display:flex;align-items:center;height:90px;}
#el-gnb .el-item{height:90px;}
#el-gnb .el-item>a{display:flex;align-items:center;height:90px;padding:0 18px;font-size:15px;font-weight:700;color:#e5e7eb;letter-spacing:-.01em;transition:color .2s;}
#el-gnb .el-item>a:hover{color:#33aeec;}
#el-gnb .el-item-key>a{color:#00C1D5;font-weight:800;}
#el-gnb .el-item-key>a:hover{color:#5fe0ee;}
/* 메가 드롭다운 (호버) — 띠 전체폭 */
#el-gnb .el-drop{position:fixed;left:0;right:0;top:90px;background:#0e0e12;border-top:1px solid rgba(255,255,255,.08);box-shadow:0 24px 48px rgba(0,0,0,.45);opacity:0;visibility:hidden;transform:translateY(-8px);transition:opacity .2s ease,transform .2s ease,visibility .2s;}
#el-gnb .el-item:hover .el-drop{opacity:1;visibility:visible;transform:translateY(0);}
#el-gnb .el-drop-inner{max-width:80rem;margin:0 auto;padding:30px 1.5rem;display:flex;align-items:flex-start;gap:44px;}
#el-gnb .el-dtitle{width:300px;flex-shrink:0;}
#el-gnb .el-dtitle b{display:block;font-size:20px;font-weight:800;margin-bottom:10px;color:#fff;}
#el-gnb .el-dtitle span{font-size:14px;color:#9ca3af;line-height:1.55;}
#el-gnb .el-dlinks{flex:1;display:flex;flex-direction:column;gap:2px;border-left:1px solid rgba(255,255,255,.1);padding-left:44px;}
#el-gnb .el-dlinks a{font-size:16px;font-weight:600;color:#cbd5e1;padding:8px 0;transition:color .2s;display:inline-flex;align-items:center;gap:4px;}
#el-gnb .el-dlinks a:hover{color:#33aeec;}
#el-gnb .el-banner{flex-shrink:0;}
#el-gnb .el-banner img{display:block;border-radius:10px;width:300px;height:auto;}
#el-gnb .el-right{margin-left:auto;display:flex;align-items:center;gap:14px;}
#el-gnb .el-search{display:flex;align-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:20px;height:36px;padding:0 6px 0 14px;width:190px;}
#el-gnb .el-search input{flex:1;background:transparent;border:0;outline:none;color:#fff;font-size:13px;min-width:0;}
#el-gnb .el-search input::placeholder{color:rgba(255,255,255,.4);}
#el-gnb .el-search button{background:0;border:0;cursor:pointer;padding:6px;display:flex;}
#el-gnb .el-search button img{width:17px;height:17px;filter:invert(1) brightness(2);}
#el-gnb .el-ue5{background:linear-gradient(90deg,#2cdcbc 0%,#33aeec 100%);color:#000;font-weight:700;font-size:13px;height:36px;line-height:36px;padding:0 18px;white-space:nowrap;transition:box-shadow .25s;}
#el-gnb .el-ue5:hover{box-shadow:0 0 12px rgba(0,181,255,.7);}
#el-gnb .el-ham{display:none;background:0;border:0;cursor:pointer;width:40px;height:40px;align-items:center;justify-content:center;flex-direction:column;gap:5px;}
#el-gnb .el-ham span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;}
#el-gnb .el-drawer{display:none;}
/* ===== 자체 GNB(#site-header) 2단 오프셋 — 최상단일 때만 라운지바 높이만큼 내림(비홈) ===== */
html:not(.el-scrolled) #site-header:not([data-floatnav]){top:90px;}
/* ===== 모바일 (≤1023px, 우리 GNB 모바일 전환점과 동일) ===== */
@media(max-width:1023px){
  #el-gnb .el-nav,#el-gnb .el-search,#el-gnb .el-ue5{display:none;}
  #el-gnb .el-ham{display:flex;}
  #el-gnb .el-drawer{position:fixed;inset:0;top:0;background:#0a0a0e;z-index:70;transform:translateX(100%);transition:transform .35s cubic-bezier(.4,0,.2,1);overflow-y:auto;padding:18px 24px 48px;display:flex;flex-direction:column;}
  #el-gnb.el-open .el-drawer{transform:translateX(0);}
  #el-gnb .el-dr-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
  #el-gnb .el-dr-top img{height:26px;}
  #el-gnb .el-dr-close{background:0;border:0;color:#fff;font-size:30px;line-height:1;cursor:pointer;padding:4px;}
  #el-gnb .el-dr-search{display:flex;align-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 16px;margin-bottom:8px;}
  #el-gnb .el-dr-search input{flex:1;background:transparent;border:0;outline:none;color:#fff;font-size:15px;min-width:0;}
  #el-gnb .el-dr-search input::placeholder{color:rgba(255,255,255,.4);}
  #el-gnb .el-dr-search button{background:0;border:0;padding:0;display:flex;}
  #el-gnb .el-dr-search button img{width:20px;height:20px;filter:invert(1) brightness(2);}
  #el-gnb .el-dr-key{display:block;color:#00C1D5;font-size:22px;font-weight:800;letter-spacing:-.02em;padding:18px 0;border-bottom:1px solid rgba(255,255,255,.1);}
  #el-gnb .el-macc{border-bottom:1px solid rgba(255,255,255,.1);}
  #el-gnb .el-macc>button{width:100%;display:flex;align-items:center;justify-content:space-between;background:0;border:0;color:#fff;font-size:22px;font-weight:700;letter-spacing:-.02em;padding:18px 0;cursor:pointer;}
  #el-gnb .el-macc>button .el-arr{width:10px;height:10px;border-right:2px solid rgba(255,255,255,.5);border-bottom:2px solid rgba(255,255,255,.5);transform:rotate(45deg);transition:transform .25s;}
  #el-gnb .el-macc.open>button .el-arr{transform:rotate(-135deg);}
  #el-gnb .el-msub{display:none;flex-direction:column;padding:0 0 14px 4px;}
  #el-gnb .el-macc.open .el-msub{display:flex;}
  #el-gnb .el-msub a{color:rgba(255,255,255,.65);font-size:16px;padding:10px 0;}
  #el-gnb .el-msub a:hover{color:#33aeec;}
  #el-gnb .el-dr-ue5{margin-top:24px;background:linear-gradient(90deg,#2cdcbc 0%,#33aeec 100%);color:#000;font-weight:700;font-size:15px;text-align:center;padding:14px;border-radius:12px;}
}
</style>

<div id="el-gnb">
  <div class="el-inner">
    <a class="el-logo" href="<?= $EL ?>/index.php" aria-label="EPIC LOUNGE"><img src="<?= $EL ?>/resource/images/common/logo_dark.svg" alt="EPIC LOUNGE"></a>

    <nav class="el-nav">
      <div class="el-item el-item-key">
        <a href="<?= $EL ?>/unrealfest2026/index.php">언리얼 페스트 서울 2026</a>
      </div>
      <div class="el-item">
        <a href="<?= $EL ?>/v3/contents/v4/news_list.php">새소식</a>
        <div class="el-drop"><div class="el-drop-inner">
          <div class="el-dtitle"><b>새소식</b><span>언리얼 엔진 뉴스, 이벤트<br>그리고 영감을 주는 사례를 확인해 보세요.</span></div>
          <div class="el-dlinks">
            <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=뉴스">뉴스</a>
            <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=업데이트/출시">출시 &amp; 업데이트</a>
            <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=블로그">블로그</a>
          </div>
          <div class="el-banner"><a href="<?= $EL ?>/v3/contents/v4/news_list.php"><img src="<?= $EL ?>/v3/data/banner/26" alt=""></a></div>
        </div></div>
      </div>
      <div class="el-item">
        <a href="<?= $EL ?>/v3/contents/v4/event_list.php?category=커뮤니티 이벤트">이벤트</a>
        <div class="el-drop"><div class="el-drop-inner">
          <div class="el-dtitle"><b>이벤트</b><span>웨비나, 테크토크, 챌린지와 같은<br>온*오프라인 이벤트를 모두 만나보세요.</span></div>
          <div class="el-dlinks">
            <a href="<?= $EL ?>/v3/contents/v4/event_list.php?category=커뮤니티 이벤트">커뮤니티 이벤트</a>
            <a href="<?= $EL ?>/v3/contents/v4/event_list.php?type=global">글로벌 이벤트</a>
          </div>
          <div class="el-banner"><a href="<?= $EL ?>/v3/contents/v4/event_list.php?category=커뮤니티 이벤트"><img src="<?= $EL ?>/v3/data/banner/27" alt=""></a></div>
        </div></div>
      </div>
      <div class="el-item">
        <a href="<?= $EL ?>/v3/contents/v4/replay_list.php">리소스</a>
        <div class="el-drop"><div class="el-drop-inner">
          <div class="el-dtitle"><b>리소스</b><span>언리얼 페스트, 시작해요 언리얼, 무료 콘텐츠 등<br>다양한 리소스를 활용해 보세요.</span></div>
          <div class="el-dlinks">
            <a href="<?= $EL ?>/v3/contents/v4/replay_list.php">다시보기</a>
            <a href="<?= $EL ?>/v3/contents/v4/free_list.php">무료 콘텐츠</a>
            <a href="<?= $EL ?>/v3/contents/v4/book_list.php">백서</a>
            <a href="https://www.unrealengine.com/ko/onlinelearning-courses" target="_blank" rel="noopener">에픽 디벨로퍼 커뮤니티 ↗</a>
          </div>
        </div></div>
      </div>
    </nav>

    <div class="el-right">
      <form class="el-search" action="<?= $EL ?>/v3/contents/v4/total_search.php" method="get">
        <input type="text" name="keyword" placeholder="검색" aria-label="통합검색">
        <button type="submit" aria-label="검색"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
      </form>
      <a class="el-ue5" href="<?= $EL ?>/start_unrealengine.php">시작해요 UE5</a>
      <button type="button" class="el-ham" id="el-ham" aria-label="메뉴 열기"><span></span><span></span><span></span></button>
    </div>
  </div>

  <!-- 모바일 드로어 -->
  <div class="el-drawer" id="el-drawer">
    <div class="el-dr-top">
      <img src="<?= $EL ?>/resource/images/common/foot_logo.svg" alt="EPIC LOUNGE">
      <button type="button" class="el-dr-close" id="el-dr-close" aria-label="메뉴 닫기">&times;</button>
    </div>
    <form class="el-dr-search" action="<?= $EL ?>/v3/contents/v4/total_search.php" method="get">
      <input type="text" name="keyword" placeholder="검색" aria-label="통합검색">
      <button type="submit" aria-label="검색"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
    </form>
    <a class="el-dr-key" href="<?= $EL ?>/unrealfest2026/index.php">언리얼 페스트 서울 2026</a>
    <div class="el-macc">
      <button type="button">새소식 <i class="el-arr"></i></button>
      <div class="el-msub">
        <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=뉴스">뉴스</a>
        <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=업데이트/출시">출시 &amp; 업데이트</a>
        <a href="<?= $EL ?>/v3/contents/v4/news_list.php?category=블로그">블로그</a>
      </div>
    </div>
    <div class="el-macc">
      <button type="button">이벤트 <i class="el-arr"></i></button>
      <div class="el-msub">
        <a href="<?= $EL ?>/v3/contents/v4/event_list.php?category=커뮤니티 이벤트">커뮤니티 이벤트</a>
        <a href="<?= $EL ?>/v3/contents/v4/event_list.php?type=global">글로벌 이벤트</a>
      </div>
    </div>
    <div class="el-macc">
      <button type="button">리소스 <i class="el-arr"></i></button>
      <div class="el-msub">
        <a href="<?= $EL ?>/v3/contents/v4/replay_list.php">다시보기</a>
        <a href="<?= $EL ?>/v3/contents/v4/free_list.php">무료 콘텐츠</a>
        <a href="<?= $EL ?>/v3/contents/v4/book_list.php">백서</a>
        <a href="https://www.unrealengine.com/ko/onlinelearning-courses" target="_blank" rel="noopener">에픽 디벨로퍼 커뮤니티 ↗</a>
      </div>
    </div>
    <a class="el-dr-ue5" href="<?= $EL ?>/start_unrealengine.php">시작해요 UE5</a>
  </div>
</div>

<script>
(function(){
  var root=document.documentElement, gnb=document.getElementById('el-gnb');
  /* 스크롤 시 라운지 띠 숨김 → 자체 GNB만 남음 */
  var prev=null;
  function onScroll(){ var s=(window.pageYOffset||document.documentElement.scrollTop)>8; if(s!==prev){ root.classList.toggle('el-scrolled', s); prev=s; } }
  onScroll(); window.addEventListener('scroll', onScroll, {passive:true});
  /* 모바일 드로어 */
  var ham=document.getElementById('el-ham'), close=document.getElementById('el-dr-close');
  if(ham) ham.addEventListener('click', function(){ gnb.classList.add('el-open'); document.body.style.overflow='hidden'; });
  if(close) close.addEventListener('click', function(){ gnb.classList.remove('el-open'); document.body.style.overflow=''; });
  /* 모바일 아코디언 */
  Array.prototype.forEach.call(gnb.querySelectorAll('.el-macc>button'), function(b){
    b.addEventListener('click', function(){ b.parentNode.classList.toggle('open'); });
  });
})();
</script>
