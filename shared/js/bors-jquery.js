$(function(){
	$('.bors_lazy_load').each(function(){
		el = $(this)
		html = ''+el.html()
		if(g = html.match(/^<!--([^\0]+)-->$/m))
			el.html(g[1])
	})

	jQuery('.bors_lazy_ad').lazyLoadAd({
		threshold: 200,
		timeout: 2000,
	})
/*
	$("div.box img").lazyload({
		placeholder : "img/grey.gif",
		effect      : "fadeIn"
	});
*/
})
