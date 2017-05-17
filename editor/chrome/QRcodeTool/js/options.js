
onload=function(){
	var qrcode = new QRCode('qrcode-bg', {
		text: "欢迎使用QRcode tool !",
		width: 330,
		height: 330,
		//colorDark : '#000000',
		//colorLight : '#F1F1F1',
		correctLevel : QRCode.CorrectLevel.H
	});
	var text = document.getElementById('text');
	document.getElementById('exe').onclick = function(){
		if(text.value.length <= 0){
			alert("请填写内容");
			return false;
		}

		qrcode.makeCode(text.value);
	};
}