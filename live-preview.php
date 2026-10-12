<?php
/* [디자인 프리뷰] 2025 live_channel 복제 — 접근제어·실DB·인증 제거. 실제 스트리밍/등록자검증 미연동. */
$obj = array('apply_user_name'=>'관리자','apply_user_email'=>'','apply_user_phone'=>'');
$track = 1; $track1 = 1; $track2 = 0;
$chat_url = '';
class _NullRes { function fetch_array(){ return array(); } }
class _NullDB  { function query($q){ return new _NullRes(); } }
$mysqli = new _NullDB();
?>
   <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
   <title>언리얼 페스트 서울 2025 | 온라인</title>

   <!-- CSS -->
   <link rel="stylesheet" href="../assets/css/swiper.min.css">
   <link rel="stylesheet" href="../assets/css/nice-select.css">
   <link rel="stylesheet" href="../assets/css/live.css?ver=1.1">

   <!-- JS -->
   <script src="../assets/js/jquery-1.7.2.min.js"></script>
   <script src="../assets/js/jquery.nice-select.min.js"></script>
   <script src="../assets/js/swiper.min.js"></script>

   <!--[if lt IE 9]>
  <script src="../assets/js/html5shiv.js"></script>
  <![endif]-->

   <!--[if IE 9]>
  <link rel="stylesheet" href="../assets/js/ie9.css">
  <![endif]-->

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

 </head>

 <body>
   <?php
if ($admin == 'admin') {
} else {
    $sql2 = "select * from cb_unreal_2025_event2_apply_live where apply_user_email = '".$obj['apply_user_email']."' and apply_reg_datetime like '".date("Y-m-d")."%'";
    $result2 = $mysqli -> query($sql2);
    $obj2 = $result2 -> fetch_array();
    if(count($obj2[0])){
        if($track1){
            $sql3 = " update cb_unreal_2025_event2_apply_live set track1 = '".$track1."',apply_reg_datetime2 = now() WHERE apply_no = '".$obj2['apply_no']."'";
            $result3 = $mysqli -> query($sql3);
        } elseif ($track2) {
            $sql3 = " update cb_unreal_2025_event2_apply_live set track2 = '".$track2."',apply_reg_datetime2 = now() WHERE apply_no = '".$obj2['apply_no']."'";
            $result3 = $mysqli -> query($sql3);
        }
    }else{
        $sql3 = " insert cb_unreal_2025_event2_apply_live set apply_user_email = '".$obj['apply_user_email']."', track1 = '".$track1."', track2 = '".$track2."', apply_user_name = '".$obj[apply_user_name]."', apply_user_phone= '".$obj[apply_user_phone]."'";
        $result3 = $mysqli -> query($sql3);
    }
}
echo '<script type="text/javascript">';
echo '$(document).ready(function() {';
echo "$('.tab-menu li').eq($track-1).find('a').addClass('active');";
echo "$('.tab-content-' + $track).addClass('active');";
echo '})';
echo '</script>';
?>


   <!-- <header class="header">
    <div class="inner-wrap">
      <div class="logo">
<a href="https://epiclounge.co.kr/"><span class="blind">로고</span></a>
      </div>
  <div class="confirm">
      <p>질문을 남겨주시면 Q&amp;A 시간에 답변해 드립니다.</p>
        <div class="btn-group">
          <a href="javascript:;" class="header_btn">질문하기</a>
        </div>
      </div>
    </div> 
  </header>-->
   <script>
     var set_pop = false;

     function toggleChat() {
       if ($(".chatting").css("display") != "none") {
         $(".video").width("69%");
         $(".chatting").animate({
           width: "toggle"
         }, 300, "linear");

         $(".video").animate({
           width: "100%"
         }, 400, "linear");
         $(".btn-volume").addClass("on");
         $(".btn-volume").text("채팅창보이기");

       } else {
         $(".video").width("97%");
         $(".video").animate({
           width: "71.5%"
         }, 300, "linear");
         $(".chatting").animate({
           width: "toggle"
         }, 400, "linear");
         $(".btn-volume").removeClass("on");
         $(".btn-volume").text("채팅창숨기기");
       }
     }




     function toggleFullscreen() {
       let iframe = document.getElementById('sample');

       // Check if the video is in fullscreen
       if (document.fullscreenElement) {
         document.exitFullscreen();
       } else {
         iframe.requestFullscreen();
       }
     }

     $(document).ready(function() {
       $(".pop_header_btn").click(function() {
         $(".pop_header").slideUp();
         set_pop = true;
       });
     });
   </script>
   <div class="container" style="padding-top:15px">
     <div class="live-channel">
       <?
     $track = $_REQUEST["apply_user_track"];
     $vod_url = "https://www.youtube.com/embed/HAuR40fw5pg";
     switch ($track) {
         case "DAY1_TR1":
             $vod_url = "https://www.youtube.com/embed/UgU0UfB6vD8";
             break;
         case "DAY1_TR2":
             $vod_url = "https://www.youtube.com/embed/kt6LHmVWiig";
             break;
         case "DAY1_TR3":
             $vod_url = "https://www.youtube.com/embed/MkXqJjkj_4I";
             break;
         case "DAY2_TR1":
             $vod_url = "https://www.youtube.com/embed/tu-4UXdBF0w";
             break;
         case "DAY2_TR2":
             $vod_url = "https://www.youtube.com/embed/hVLYrgNB-uw";
             break;
         case "DAY2_TR3":
             $vod_url = "https://www.youtube.com/embed/4g4YF6CTaO8";
             break;
         default:
             $vod_url = "https://www.youtube.com/embed/UgU0UfB6vD8";
             break;

     }
 ?>
       <!--
          IWW6KB7kxqQ
        _-0uUWv1KjE
        w3rlJqt1NvY
        37sVyNyYtko-->
       <div class="inner-wrap">
         <div class="video">
           <!--본방 Video -->


           <iframe id="sample" src="<?php echo $vod_url; ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


           <div class="video_bar">
             <a href="#n" class="btn-fullscreen" onclick="toggleFullscreen()">전체화면</a>
             <a href="#n" class="btn-volume" onclick="toggleChat()">채팅창 숨기기</a>
             <!-- 누르면 텍스트 바뀌면서 채팅창 보이기 + 클래스 on -->
           </div>
         </div>
         <div class="chatting">
           <?
           $track = $_REQUEST["apply_user_track"];
           $chat_url = "https://griff.cgchat.kr/chat?sk=griff&no=griffroom2025_1";
           switch ($track) {
               case "DAY1_TR1":
                   $chat_url = "https://griff.cgchat.kr/chat?sk=griff&no=griffroom2025_1";
                   break;
               case "DAY1_TR2":
                   $chat_url = "https://griff2.cgchat.kr/chat?sk=griff&no=griffroom2025_2";
                   break;
               case "DAY1_TR3":
                   $chat_url = "https://griff3.cgchat.kr/chat?sk=griff&no=griffroom2025_3";
                   break;
               case "DAY2_TR1":
                   $chat_url = "https://griff.cgchat.kr/chat?sk=griff&no=griffroom2025_4";
                   break;
               case "DAY2_TR2":
                   $chat_url = "https://griff2.cgchat.kr/chat?sk=griff&no=griffroom2025_5";
                   break;
               case "DAY2_TR3":
                   $chat_url = "https://griff3.cgchat.kr/chat?sk=griff&no=griffroom2025_6";
                   break;
               default:
                   $chat_url = "https://griff1.cgchat.kr/chat?sk=griff&no=griffroom2025_1";
                   break;

           }
           ?>


           <?php if ($admin == 'admin') { ?>
           <script language="javascript">
             var iframe = '<?=$chat_url?>';
             iframe += '&ui=' + encodeURIComponent('{"btnReconnect":"0","btnPopupChat":"0","btnEmoji":"1"}');
             iframe += '&view=' + encodeURIComponent('{"sendBtn":"333333","bgColor":"333333","msgViewType":"0","chatOneLine":"0","isChatHistory":"1","sysMsgColor":"7e7e7e","chatTime":"1"}');
             iframe += '&my=' + encodeURIComponent('{"nkColor":"7e7e7e","msgColor":"000000"}');
             iframe += '&control=' + encodeURIComponent('{"banWord":"***나쁜말***","autoLink":"1","maxLength":"0","msgInterval":"0","mobileFocus":"0"}');
             iframe += '&tg=https://unrealsummit16.cafe24.com/og/tg.svg&an=1&lv=3&id=언리얼페스트';
             iframe += '&nk=admin';
             iframe = '<iframe id="cgchat" name="cgchat" src="' + iframe + '" style="margin:0;padding:0;border:solid 1px #000000;"></iframe>';
             document.write(iframe);
           </script>
           <?php } else { ?>
           <script language="javascript">
             var iframe = '<?=$chat_url?>';
             iframe += '&ui=' + encodeURIComponent('{"btnPopupChat":"0","btnEmoji":"1"}');
             iframe += '&view=' + encodeURIComponent('{"sendBtn":"333333","bgColor":"333333","msgViewType":"0","chatOneLine":"0","isChatHistory":"1","sysMsgColor":"7e7e7e","chatTime":"1"}');
             iframe += '&my=' + encodeURIComponent('{"nkColor":"7e7e7e","msgColor":"000000"}');
             iframe += '&control=' + encodeURIComponent('{"banWord":"***나쁜말***","autoLink":"1","maxLength":"0","msgInterval":"0","mobileFocus":"0"}');
             iframe += '&tg=https://unrealsummit16.cafe24.com/og/tg.svg&lv=2&id=' + encodeURIComponent('<?=$obj["apply_user_email"]?>');
             iframe += '&nk=' + encodeURIComponent('<?=mb_substr ($obj["apply_user_name"],0,12)?>(<?=substr($obj["apply_user_phone"],7,4)?>)');

             iframe = '<iframe id="cgchat" name="cgchat" src="' + iframe + '" style="margin:0;padding:0;border:solid 1px #000000;"></iframe>';
             document.write(iframe);
           </script>
           <?php } ?>
           <?

           ?>
           <script>
             function chat_reload() {
               //location.href = "live_channel.html?apply_user_track=" + $("#apply_user_track").val()+"<?=$admin == "admin" ? "&admin=admin":""?>";
               $("#frm_reload").submit();
             }
           </script>
         </div>
         <form action="" name="frm_reload" id="frm_reload" method="post">
           <input type="hidden" id="inputEmail" name="inputEmail" value="<?=$obj['apply_user_email']?>">
           <?if($admin == "admin"){
                  ?>
           <input type="hidden" id="admin" name="admin" value="<?=$admin?>">
           <?
              }
              ?>
           <input type="hidden" name="live" value="1" />
           <div class="schedule_title"><select id="apply_user_track" name="apply_user_track" onchange="chat_reload()">
               <!--  <option value="DAY1_TR1" <?= $track == "DAY1_TR1" ? "selected='selected'" : "" ?>>8월 25일 게임:
                 프로그래밍
               </option>
               <option value="DAY1_TR2" <?= $track == "DAY1_TR2" ? "selected='selected'" : "" ?>>8월 25일 미디어 &amp; 엔터테인먼트
               </option>
               <option value="DAY1_TR3" <?= $track == "DAY1_TR3" ? "selected='selected'" : "" ?>>8월 25일 게임: 아트
               </option> -->
              <option value="DAY2_TR1" <?= $track == "DAY2_TR1" ? "selected='selected'" : "" ?>>8월 26일 게임
               </option>
               <option value="DAY2_TR2" <?= $track == "DAY2_TR2" ? "selected='selected'" : "" ?>>8월 26일 미디어 &amp; 엔터테인먼트
               </option>
               <option value="DAY2_TR3" <?= $track == "DAY2_TR3" ? "selected='selected'" : "" ?>>8월 26일 제조 및
                 시뮬레이션
               </option>

             </select></div>
         </form>
         <div class="schedule">
           <?php
              $track = $_REQUEST["apply_user_track"];
          $session_url = "live_session1.php";  // 기본값 설정
          switch ($track) {
              case "DAY1_TR1":
                  $session_url = "live_session11.php";
                  break;
              case "DAY1_TR2":
                  $session_url = "live_session22.php";
                  break;
              case "DAY1_TR3":
                  $session_url = "live_session33.php";
                  break;
              case "DAY2_TR1":
                  $session_url = "live_session44.php";
                  break;
              case "DAY2_TR2":
                  $session_url = "live_session55.php";
                  break;
              case "DAY2_TR3":
                  $session_url = "live_session66.php";
                  break;
              default:
                  $session_url = "live_session11.php";
                  break;
          }
              ?>
           <?php include $session_url; ?>


           <button type="button" class="swiper-next"></button>
           <button type="button" class="swiper-prev"></button>

         </div>
       </div>
     </div>
   </div>

   <div id="popup_question" class="popup">
     <div class="popup-wrap">
       <!--<div class="top">
        <h2>질문하기</h2>
        <p>이름과 질문을 작성해주세요<br>강연 후반 Q&amp;A시간에 발표자가 답변해드립니다</p>        
        <a href="javascript:;" type="button" class="close"><span class="blind">닫기</span></a>
      </div>
      <div class="content">
        <input type="text" placeholder="이름을 입력해주세요">
        <textarea placeholder="질문을 입력해주세요"></textarea>
        <div class="btn-group">
          <a href="javascript:;" class="md sky">질문하기</a>
        </div>
      </div>-->
     </div>
   </div>

   <div id="popup_inquiry" class="popup">
     <div class="popup-wrap lg">
       <div class="top">
         <h2>문의하기</h2>
         <p>내용을 입력해 주세요.</p>
         <a href="javascript:;" type="button" class="close"><span class="blind">닫기</span></a>
       </div>
       <form id="login-form" name="login-form" action="findApplyByEmail.php" method="post" enctype="multipart/form-data">
         <input type="hidden" name="mode" value="inquire" />
         <div class="content">
           <ul>
             <li>
               <h3>이름</h3>
               <input type="text" name="apply_user_name" placeholder="이름을 입력해 주세요">
             </li>
             <li>
               <h3>이메일</h3>
               <input type="text" id="inputEmail" name="inputEmail" placeholder="이메일을 입력해 주세요">
             </li>
             <li>
               <h3>휴대폰</h3>
               <input type="text" name="apply_user_phone" numberOnly placeholder="휴대폰 번호를 입력해 주세요">
             </li>
             <li>
               <h3>회사명</h3>
               <input type="text" name="apply_user_company" placeholder="회사명을 입력해 주세요">
             </li>
             <li>
               <h3>부서</h3>
               <input type="text" name="apply_user_depart" placeholder="부서명을 입력해 주세요">
             </li>
             <li>
               <h3>분류</h3>
               <select name="apply_user_grade">
                 <option value="">-선택하세요-</option>
                 <option value="라이선스">라이선스</option>
                 <option value="제휴문의">제휴 문의</option>
                 <option value="기타">기타</option>
               </select>
             </li>
             <li>
               <h3>내용</h3>
               <textarea name="apply_content" placeholder="내용을 입력해주세요"></textarea>
             </li>
           </ul>
           <div class="btn-group">
             <a id="btnLoginAction" href="javascript:;" class="sky">문의하기</a>
           </div>
         </div>
       </form>
     </div>
   </div>
   <form class="form-inline" name="fsearch2" action="https://epiclounge.co.kr/v2/live_channel.html" method="get">
     <input type="hidden" name="admin" value="admin">
     <input type="hidden" name="track_select" value="">
     <input type="hidden" name="track" value="">
   </form>
   <footer class="footer">
     <div class="inner-wrap">
       <div class="left">
         <div class="logo"><span class="blind">로고</span></div>
         <div class="copyright">Copyright @ EPIC GAMES KOREA All Right Reserved</div>
       </div>
       <!--
      <div class="inquiry">
<a href="javascript:;" style="color: #BDFF00; font-weight:bold;">라이선스 및 제휴 문의 &#8594</a>
      </div>
	  -->
     </div>
   </footer>

   <script>
     $(".header .btn-group").on("click", "a", function() {
       //$("#popup_question").addClass("active");
       var today3 = new Date();
       var option2 = "width = 960, height = 1000, top = 0, left = 0, location = no";
       var msg2 = '';
       msg2 += today3.getFullYear() + "/",
         msg2 += today3.getMonth() + 1 + "/",
         msg2 += today3.getDate() + " ";
       msg2 += today3.getHours() + ":";
       msg2 += today3.getMinutes() + ":";
       msg2 += today3.getSeconds();
       var clock = document.getElementById("clock");

       if (new Date('2023/09/01 10:30:00').getTime() <= new Date(msg2).getTime() &&
         new Date('2023/09/01 18:00:00').getTime() > new Date(msg2).getTime()) {
         window.open('https://app.sli.do/event/cSimTWoDDTr5CTYXCMx6Um', 'live', option2);
       } else if (new Date('2022/09/28 14:00:00').getTime() <= new Date(msg2).getTime() &&
         new Date('2022/09/28 14:50:00').getTime() > new Date(msg2).getTime()) {
         window.open('https://app.sli.do/event/dy9a3cGzvPmboA5x5AC2qR', 'live', option2);
       } else if (new Date('2022/09/28 15:00:00').getTime() <= new Date(msg2).getTime() &&
         new Date('2022/09/28 15:50:00').getTime() > new Date(msg2).getTime()) {
         window.open('https://app.sli.do/event/wHESSqs6VdQ2SgPsvbEZPp', 'live', option2);
       } else if (new Date('2022/09/28 16:00:00').getTime() <= new Date(msg2).getTime() &&
         new Date('2022/09/28 17:00:00').getTime() > new Date(msg2).getTime()) {
         window.open('https://app.sli.do/event/m6aBwRS6Cv1pR2e128WqYZ', 'live', option2);
       } else if (new Date('2022/09/28 17:00:00').getTime() <= new Date(msg2).getTime() &&
         new Date('2022/09/28 17:50:00').getTime() > new Date(msg2).getTime()) {
         window.open('https://app.sli.do/event/iA2jKLAL3DmWTS87D1bVdf', 'live', option2);
         //} else if (new Date('2022/09/27 00:12:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 15:00:00').getTime() > new Date(msg2).getTime()) {
         //window.open('https://youtu.be/Mo-1lHz7_HA', 'live', option2);
         //      if (new Date('2022/09/23 00:04:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:07:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
         //} else if (new Date('2022/09/23 00:07:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:09:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
         //} else if (new Date('2022/09/23 00:07:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:09:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
         //} else if (new Date('2022/09/23 00:09:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:11:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
         //} else if (new Date('2022/09/23 00:11:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:12:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
         //} else if (new Date('2022/09/23 00:12:00').getTime() <= new Date(msg2).getTime() && new Date('2022/09/23 00:13:00').getTime() > new Date(msg2).getTime()) {
         //window.open('#', 'live', option2);
       } else {
         alert('질문 가능 시간이 아닙니다.');
       }
     });

     $("#popup_question").on("click", ".close", function() {
       $("#popup_question").removeClass("active");
     });

     $(".inquiry a").on("click", function() {
       $("#popup_inquiry").addClass("active");
     });

     $("#popup_inquiry").on("click", ".close", function() {
       $("#popup_inquiry").removeClass("active");
     });

     $(".tab-menu").on("click", "a", function() {
       $(this).addClass("active");
       // $(this).parent().siblings("li").find("a").removeClass("active");
       // $(".tab-content").removeClass("active");
       // $(this).parent().siblings("li").find("a").removeClass("active");
       // $(".tab-content").removeClass("active");
       //  $(".tab-content-" + $(this).data("tab")).addClass("active");
       if ($(this).data("tab") == 1) {
         var f = document.fsearch2;
         f.track_select.value = 1;
         f.track.value = 1;
         f.submit();
       } else {
         var f = document.fsearch2;
         f.track_select.value = "2";
         f.track.value = "2";
         f.submit();
       }
     });


     var swiper = new Swiper(".schedule .list", {
       slidesPerView: "auto",
       navigation: {
         nextEl: '.swiper-next',
         prevEl: '.swiper-prev',
       },
     });

     // 시간 체크
     function timeControl() {
       var today = new Date(),
         nowTime = today.getTime(),
         year = today.getFullYear(),
         month = today.getMonth() + 1,
         date = today.getDate();


       //네이버팝업 컨트롤
       var naver_time = "05:30-18:00",
         naver_times = naver_time.split("-"),
         naver_time1 = new Date(year + "/" + month + "/" + date + " " + naver_times[0]),
         naver_time2 = new Date(year + "/" + month + "/" + date + " " + naver_times[1]),
         naver_timeStamp1 = naver_time1.getTime(),
         naver_timeStamp2 = naver_time2.getTime();

       if (nowTime > naver_timeStamp1 && nowTime < naver_timeStamp2) {

         if ($(".pop_header").css("display") == "none") {
           if (set_pop != true) {
             $(".pop_header").slideDown();
           }
         }
       } else {
         if ($(".pop_header").css("display") != "none") {
           $(".pop_header").slideUp();
         }

       }
       //네이버팝업 컨트롤

       $(".list ul li").each(function(idx) {
         var time = $(this).find(".info_time").text(),
           times = time.split("~"),
           time1 = new Date(year + "/" + month + "/" + date + " " + times[0]),
           time2 = new Date(year + "/" + month + "/" + date + " " + times[1]),
           timeStamp1 = time1.getTime(),
           timeStamp2 = time2.getTime();
         console.log(timeStamp1, timeStamp2, nowTime);

         if (nowTime < timeStamp1 && nowTime < timeStamp2) {
           $(this).addClass("expected").find(".status").text("예정");
         } else if (nowTime > timeStamp1 && nowTime < timeStamp2) {
           $(this).removeClass("expected").addClass("ing").find(".status").text("NOW");

           swiper.slideTo(idx, 200, false);

         } else {
           $(this).removeClass("ing").addClass("end").find(".status").text(" ");
         }

       });


     }

     timeControl();

     setInterval(timeControl, 10000);

     //$('select').niceSelect();
   </script>
   <script>
     $(function() {
       setLogin();
       // 전화번호 숫자만 입력
       $("input:text[numberOnly]").on("keyup", function() {
         $(this).val($(this).val().replace(/[^0-9]/g, ""));
       });
     });

     function setLogin() {
       $.validator.addMethod("userEmail", function(value, element) {
         return this.optional(element) || /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i.test(value);
       }, "이메일을 형식에 맞게 입력하세요.");

       $('#login-form').validate({
         debug: true,
         onfocusout: function(element) {
           $(element).valid();
         },
         errorPlacement: function(error, element) {
           element.parent().next('.warning').show();
         },
         rules: {
           inputEmail: {
             required: true,
             maxlength: 50,
             userEmail: true,
             email: true
           },
         },
         highlight: function(element) {},
         unhighlight: function(element) {
           $(element).parent().next('.warning').hide();
         },
         messages: {
           inputEmail: {
             required: "이메일을 입력하세요.",
             maxlength: $.validator.format('이메일은 {0}자 내로 입력하세요.'),
             email: "이메일을 형식에 맞게 입력하세요."
           },
         },
         invalidHandler: function(form, validator) {
           var errors = validator.numberOfInvalids();
           if (errors) {
             alert(validator.errorList[0].message);
             validator.errorList[0].element.focus();
           }
         }
       });


       $("#btnLoginAction").on("click", function(e) {
         var form = document.forms['login-form'];
         if ($('#login-form').valid()) { // test for validity
           if (form.apply_user_name.value == "") {
             alert("이름을 입력해 주세요.");
             form.apply_user_name.focus();
             return false;
           } else if (form.apply_user_phone.value == "") {
             alert("연락처를 입력해 주세요.");
             form.apply_user_phone.focus();
             return false;
           } else if (form.apply_user_company.value == "") {
             alert("회사명을 입력해 주세요.");
             form.apply_user_company.focus();
             return false;
           } else if (form.apply_user_depart.value == "") {
             alert("부서를 입력해 주세요.");
             form.apply_user_depart.focus();
             return false;
           } else if (form.apply_user_grade.value == "") {
             alert("분류를 선택해 주세요.");
             form.apply_user_grade.focus();
             return false;
           } else if (form.apply_content.value == "") {
             alert("내용을 입력해 주세요.");
             form.apply_content.focus();
             return false;
           }

           var gsWin = window.open("about:blank", "winName");
           form.target = "winName";
           form.submit();

           form.apply_user_name.value = '';
           form.inputEmail.value = '';
           form.apply_user_phone.value = '';
           form.apply_user_company.value = '';
           form.apply_user_depart.value = '';
           form.apply_user_grade.value = '';
           form.apply_content.value = '';
         } else {
           // do stuff if form is not valid
         }
       });
     }
   </script>
 </body>

 </html>