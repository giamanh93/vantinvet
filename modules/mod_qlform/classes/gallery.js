$(document).ready(function () 
{
	var browserWidth = $(window).width();
	if (browserWidth<1000)browserWidth=1000;
	var browserHeight = $(window).height()-182;
	var browserRelation=browserWidth/browserHeight;
	var imageWidth=1920;
	var imageHeight=1280;
	var imageRelation=imageWidth/imageHeight;
	
	/*alert(browserRelation+"\n"+imageRelation);*/
	$('div#content').attr("height",browserHeight);
	$('div#gallery').attr("height",browserHeight);
	$('div#content').css("height",browserHeight);
	$('div#gallery').css("height",browserHeight);
	if (browserRelation<=imageRelation)
	{
		/*cut left 'n right*/
		imageWidth=browserHeight*imageRelation;
		/*browserHeight=632*/ 
		imageHeight=browserHeight;
		var marginLeft=(imageWidth-browserWidth)/2;
		marginLeft=marginLeft.round();
		/*if (marginTop<0)marginTop=-marginTop;
		else marginTop=marginTop;*/
		$('div#gallery img#first').attr("width",imageWidth);
		$('div#gallery img#first').attr("height",browserHeight);
		$('div#gallery img#first').css("margin-left","-"+marginLeft+"px");
		$('div#gallery img#first').css("margin-right","-"+marginLeft+"px");
	}
	else
	{
		/*cut above and below*/
		imageWidth=browserWidth;
		imageHeight=browserHeight*imageRelation;
		$('div#gallery img#first').attr("width",imageWidth);
		marginTop=(imageHeight-browserHeight)/2;
		marginTop=marginTop.round();
		$('div#gallery img#first').css("margin-top","-"+marginTop+"px");
	}
	/*if (startseite==true) {$('div#gallery img#first').css("margin-top","0");}*/
	$('body.mediathek div#gallery img#first').css("margin-top","0px");
	$('body.home div#gallery img#first').css("margin-top","0px");	
	var i=0;
	$('#imgarrowleft').click
	(
		function()
		{
			i++;
			if (i==arr_images_length) i=0;
			$('div#gallery img#first').attr("src",arr_images[i]);
		}
	);
	$('#imgarrowright').click
	(
		function () 
		{
			i--;
			if (i==-1) i=arr_images_length-1;
			$('div#gallery img#first').attr("src",arr_images[i]);
			}
	);
});
