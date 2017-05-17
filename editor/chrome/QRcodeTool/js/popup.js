/*chrome.runtime.sendMessage('Hello', function(response){
    document.write(response);
});*/

onload=function(){
	// var qrcode = document.getElementById('qrcode');

	chrome.tabs.getSelected(function(tab){
		var qrcode = new QRCode('qrcode-bg', {
              text: tab.url,
              width: 128,
              height: 128,
              /*colorDark : '#000000',
              colorLight : '#ffffff',*/
              correctLevel : QRCode.CorrectLevel.H
            });
		console.log(qrcode);
	});


}


