$(document).ready(function(){

	footer_position();
	window_position();
	//bacground_position()
	//pop_up_position();
	
	$(".use_file").click(function(){
		$(this).toggleClass("use_click")
	});

	$(".use_file1").click(function(){
		$(this).toggleClass("use_click")
	});


	$(".chack").click(function(){
		$(".chack_img").removeClass("chack_block")
		$(".chack_img2").removeClass("chack_block2");
		$(".added_contacts_right").slideDown();
		//$(".added_contacts_right_popup_container").slideDown();
		$(".archive_open").slideUp();
		$('#phone_no').hide();
		numberData = [];
	});

	$(".chack2").click(function(){
		$(".chack_img2").addClass("chack_block2")
		$(".archive_open").slideDown();
		$(".chack_img").addClass("chack_block");
		$(".added_contacts_right").slideUp();
		//$(".added_contacts_right_popup_container").slideUp()
	});

	$(".slot_day img").click(function(){
		$(this).toggleClass("slot_chack_img_add_class");

	});


	$(".slot_chack_img").click(function(){
		if($(this).hasClass("slot_chack_img_add_class")){
			$(this).attr("name",1);
		}
		else{
			$(this).attr("name",0);
		}
	});




	$(window).resize(function(){
		footer_position();
		window_position();
		pop_up_position()
		//bacground_position()
	});


	var height1 = ($(".left_menu_content").height() - $("#first").height())/2;

	var height2 = ($(".left_menu_content").height() - $("#second").height())/2;

	var height3 = ($(".left_menu_content").height() - $("#third").height())/2;

	$("#first").css({
		top: height1, 
	});

	$("#second").css({
		top: height2, 
	});

	$("#third").css({
		top: height3, 
	});







/* --------------------------------- Btns hover --------------------------------- */

// $(".account_btns").hover(function(){
// 	$(this).transition({
// 		'margin-top': '4px';
// 	});
// })








/* --------------------------------- /Btns hover --------------------------------- */





	/* --------------------------- Footer select open ----------------------------*/
		/*$(".footer_select").click(function(event){
			event.stopPropagation();
			$(".language_open").toggle();
		});

		$('html').click(function(){
			$(".language_open").hide();
		});

		$(".language_open ul li").click(function(){
			var html = $(this).html();
			$(".footer_select_div ul li").html(html);
		});*/

	/* --------------------------- Footer select open ----------------------------*/

	/*------------------error--------*/

	$(window).click(function(){
		$('.error_container').slideUp();
	})




});



function footer_position(){
	var h = $(window).height() - $("#footer").height();
	$("#footer").css({
		top:h,
	});
}


function window_position(){
	var h = $(document).height();
	var w = $(window).width();
	$(".window").css({
		width: w,
		height: h,
	});
}



function pop_up_position(){
	var h = ($(window).height() - $(".pop_up").height())/2;
	$(".pop_up").css({
		top: h,
	});
}


// function bacground_position(){
// 	var h = $(document).outerHeight() - $("#footer").outerHeight();
// 	$(".background").css({
// 		height: h,
// 	});
// }

