( function ( $ ) {
	$( function() {
		var slidingButton = $("<div id='htmlscreenshot'>Send Screenshot</div>");
		$( "body" ).append(slidingButton);
		slidingButton.click(function(){
			slidingButton.unbind("click");
			slidingButton.html("Saving...");
			html2canvas(document.body).then(function(canvas) {
				var dataUrl = canvas.toDataURL("image/jpeg", 0.7);
				jQuery.post(ajax_object.ajax_url, {action: ajax_object.action, base64Data: dataUrl}, function(response) {
					slidingButton.addClass("sent");
					slidingButton.html("Thanks!");
				});		
			});
			
		});
		slidingButton.mouseenter(function(){
			slidingButton.stop().animate({bottom:0},200);
		});
		slidingButton.mouseleave(function(){
			slidingButton.stop().animate({bottom:-40},200);
		});
		//ajax_object.ajax_url

	} );
}( jQuery ) );