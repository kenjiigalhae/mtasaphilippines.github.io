$(document).ready(function () {
    $('.nav_holder ul li a').append('<div class="hover"></div>');
    $('.nav_holder ul li a').hover( 
        function() {     
            $(this).children('div').stop(true, true).fadeIn('1500');    
        },
        function() {
            $(this).children('div').stop(true, true).fadeOut('1500');     
    })
});

//-------------------------------------------------------------------------------------------------------

$(document).ready(function(){
	$(".logo").fadeTo("slow", 0.5);
			$(".logo").hover(function(){
				$(this).fadeTo("slow", 1.0);
			},function(){
		$(this).fadeTo("slow", 0.5);
	});
});

//-------------------------------------------------------------------------------------------------------

$('a[href]$="#top"').click(function() {     
	$('html, body').animate({ scrollTop:0 }, '1000');
	return false;
});