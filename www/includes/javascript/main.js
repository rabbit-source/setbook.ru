$(document).ready(function(){
	var MaxBannerShow = 7; //ћаксимально число показов подсказки
	$('#subscribe-link #action_links a.subscribe').click(function() {
		if(!$("#subscribe-link span.message").hasClass("error"))
		{
			$.ajax({ url: "/loader.php?action=subscribe&cid="+$(this).attr('cid')+"&type="+$(this).attr('tid')+"&sub=1", success: function(){
				$("#subscribe-link a.subscribe").addClass("hide");
				$("#subscribe-link span.message").removeClass("hide");
			  }
			});
		}
		else
		{
			$("#subscribe-link a.subscribe").addClass("hide");
			$("#subscribe-link span.message").removeClass("hide");
		}
		return false;
	});
		
	$('#subscribe-link #action_links a.subscribe').qtip({
      content: $('#subscribe-link #action_links a.subscribe').attr('alt'), // Give it some content, in this case a simple string
      position: {
          corner: {
             tooltip: 'bottomMiddle', // Use the corner...
             target: 'topMiddle' // ...and opposite corner
          }
     },
     show: {
	      delay: 	100,
	      ready: (parseInt($.cookie("qtipBannerCount")) < MaxBannerShow || isNaN(parseInt($.cookie("qtipBannerCount"))) || isNaN($.cookie("qtipBannerCount"))) // Show the tooltip when ready
	 },
     style: {
          border: {
             width: 5,
             radius: 5
          },
          'font-size': 12, 
          padding: 7, 
          textAlign: 'center',
          tip: true, // Give it a speech bubble tip with automatic corner detection
          name: 'cream' // Style it according to the preset 'cream' style
       },
     api: {
     	onShow: function(){ 
     		var count = parseInt($.cookie("qtipBannerCount"));
     		if(count == null || isNaN(count))
     			$.cookie("qtipBannerCount", 0);
     		else
     			$.cookie("qtipBannerCount", (count + 1));
     		setTimeout('$("#subscribe-link #action_links a.subscribe").qtip("hide");', 15000);

     	}
     }

   });

});