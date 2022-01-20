jQuery(".lazy").slick({
	lazyLoad: 'ondemand', // ondemand progressive anticipated
	infinite: true,
	dots: false,
	autoplay: true,
	autoplaySpeed: 3000,
	speed: 300,
	slidesToScroll: 1,
	arrows: true,
	prevArrow: '<div class="slick-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
	nextArrow: '<div class="slick-next"><i class="fa fa-angle-right" aria-hidden="true"></i></div>'
  });