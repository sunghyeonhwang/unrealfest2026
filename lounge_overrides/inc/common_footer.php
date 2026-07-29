
<!-- //container -->
<!-- footer -->
    
<div id="footer">
	<div class="wrap">
		<div class="footer_top">
			<div class="footer_logo">
				<img src="/resource/images/common/logo_dark.svg" />
			</div>
			<div class="footer_link">
				<div class="footer_link_list">
					<h3>새소식</h3>
					<ul>
						<li><a href="/v3/contents/v4/news_list.php">전체보기</a></li>
						<li><a href="/v3/contents/v4/news_list.php?category=뉴스">뉴스</a></li>
						<li><a href="/v3/contents/v4/news_list.php?category=업데이트/출시">출시 &#38; 업데이트</a></li>
						<li><a href="/v3/contents/v4/news_list.php?category=블로그">블로그</a></li>
					</ul>
				</div>
				<div class="footer_link_list">
					<h3>이벤트</h3>
					<ul>
						<li><a href="/v3/contents/v4/event_list.php?category=커뮤니티 이벤트">커뮤니티 이벤트</a></li>
						<li><a href="/v3/contents/v4/event_list.php?type=global">글로벌 이벤트</a></li>
					</ul>
				</div>
				<div class="footer_link_list">
					<h3>리소스</h3>
					<ul>
						<li><a href="/v3/contents/v4/replay_list.php">다시보기</a></li>
						<li><a href="/v3/contents/v4/free_list.php">무료 콘텐츠</a></li>
						<li><a href="/v3/contents/v4/book_list.php">백서</a></li>

					</ul>
				</div>
				<div class="footer_link_list">
				<!--	<h3>링크</h3>
					<ul>
						<li><a href="https://www.unrealengine.com" title="새창" target="_blank">언리얼 엔진</a></li>
						<li><a href="https://www.unrealengine.com/ko/download" title="새창" target="_blank">시작해요 언리얼</a></li>
						<li><a href="https://cafe.naver.com/unrealenginekr" title="새창" target="_blank">네이버 카페</a></li>
						<li><a href="https://www.facebook.com/unrealenginekr/" title="새창" target="_blank">페이스북</a></li>
						<li><a href="https://www.youtube.com/channel/UCTQY8S4wYR93jkYC649_p0g" title="새창" target="_blank">유튜브 채널</a></li>
					</ul>-->
				</div>
			</div>
		</div>
		<div class="footer_bot2">
			<ul class="text_link">
				<li>법인명(상호): 주식회사 그리프</li>
				<li>사업자등록번호: 859-88-00263 <a href="https://www.ftc.go.kr/bizCommPop.do?wrkr_no=8598800263&apv_perm_no=" target="_blank" style="color: rgb(243, 140, 140)"> 확인</a></li>
				<li><a href="" target="_blank">통신판매업: 2018-서울송파-0571</a></li><br>
				<li>개인정보보호책임자: 오승훈</li>
				<li><a href="" target="_blank">주소: 경기도 하남시 미사대로 540, 에이동 7층 11호</a></li>
				<li><a href="" target="_blank">대표이사: 황성현</a></li>
				<li><a href="tel:0232637091" target="_blank">고객센터: 02-326-3701</a></li>
				<li><a href="mailto:info@epiclounge.co.kr" target="_blank">대표 이메일 : info@epiclounge.co.kr</a></li>
			</ul>
		
		
		</div>
		<div class="footer_bot">
			<ul class="text_link">
				<li><a href="/v3/contents/v4/personal.php">개인정보보호정책</a></li>
				<li><a href="/v3/contents/v4/ode.php">이용약관</a></li>
				<li><a href="/v3/contents/v4/cookie.php">쿠키 정책</a></li>
			</ul>

			<!--<ul class="img_link">
				<li><a href="https://www.facebook.com/unrealenginekr/" title="새창" target="_blank"><img src="/v3/resource/images/common/FB.svg" /></a></li>
				<li><a href="https://cafe.naver.com/unrealenginekr" title="새창" target="_blank"><img src="/v3/resource/images/common/Cafe.svg" /></a></li>
				<li><a href="https://pf.kakao.com/_xfdmNb" title="새창" target="_blank"><img src="https://epiclounge.co.kr/v3/resource/images/event/sns_link_1.png" /></a></li>
				<li><a href="https://www.youtube.com/channel/UCTQY8S4wYR93jkYC649_p0g" title="새창" target="_blank"><img src="/v3/resource/images/common/Youtube.svg" /></a></li>
		
			</ul>-->
			<p class="copy">Copyright ©EPIC LOUNGE. All rights reserved</p>
		</div>
		
	</div>
	<!-- f_info -->
</div>


<!-- //wrap -->
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		var trigger = new ScrollTrigger({

			offset: {
				x: 0,
				y: 50
			},
			addHeight: true
		}, document.body, window);
	});



	$(function(){
		/* KD.주석. 
		$("a").on("click", function(){
			var divName = $(this).attr("id"), 
				topPosition = $("."+ divName).offset().top;
			$('html, body').animate({
				scrollTop: topPosition - 0
			}, 500);
			return false; //리턴펄스로 스크롤이 최상위로 갔다가 돌아오는 현상 없어짐
		});*/
	});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Soldier-B/jquery.toast@master/jquery.toast/jquery.toast.min.css" />
<script src="https://cdn.jsdelivr.net/gh/Soldier-B/jquery.toast@master/jquery.toast/jquery.toast.min.js"></script>
<script>
function clip(val) {
  const t = document.createElement("textarea");
  document.body.appendChild(t);
  t.value = val;
  t.select();
  document.execCommand('copy');
  document.body.removeChild(t);
  
	$.toast('링크 주소가 클립보드에 복사되었습니다.', {
      duration: 2000,
	  type: 'info'
	});
}
function move_top(){
	window.scrollTo(0,0);
}
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-174668456-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-174668456-1');
</script>
<!-- Global site tag (gtag.js) - Google Ads: 760706945 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-760706945"></script>
<script>
   window.dataLayer = window.dataLayer || [];
   function gtag(){dataLayer.push(arguments);}
   gtag('js', new Date());
   gtag('config', 'AW-760706945');
</script>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '413080733349618');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=413080733349618&ev=PageView&noscript=1"
/></noscript>
<!— End Facebook Pixel Code —>