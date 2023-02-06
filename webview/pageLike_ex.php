<!DOCTYPE html>
<html>
<head>
<title>배그봇 페이지 좋아요❤︎</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width" />
</head>
<body>
	<script>	
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.com/ko_KR/messenger.Extensions.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'Messenger'));
	
	window.extAsyncInit = function() {
		MessengerExtensions.getContext('1897365380315817', 
		function success(thread_context){
			document.getElementById('psid').value = thread_context.psid;

			document.forms["pl"].action = "https://bhandy.kr/pbg/webview/pageLike.php";
			document.forms["pl"].target = "_Self";
			document.forms["pl"].method = "post";
			document.forms["pl"].submit();
		});
	};		
	</script>
	<form name="pl" id="pl">
		<input type="hidden" name="psid" id="psid">
	</form>
</body>
</html>

