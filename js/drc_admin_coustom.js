jQuery(document).ready(function( $ ){
	
	

	$( 'a#add_slider' ).on('click', function() {
		
		var rowCount = $('#drc_slider_data').find('.single-slider-row').not(':last-child').size(); 
		var newRowCount = rowCount + 1;
	
		var row = $( '.empty-row.screen-reader-text' ).clone(true);
		alert(newRowCount);
		row.find('input[type="radio"]').each(function(){ 
			if ( !! $(this).attr('name') )
				$(this).attr('name',  $(this).attr('name').replace('[%s]', '[' + newRowCount + ']') ); 
		});
		
		
		row.removeClass( 'empty-row screen-reader-text' );
		row.insertBefore( '#drc_slider_data tbody>tr:last' );
		return false;
	});

	$( '.remove-row' ).on('click', function() {
		$(this).parents('tr').remove();
		return false;
	});
	
	
	var sliderImage;
	sliderImage = wp.media.frames.mysite_gallery_frame = wp.media({
		title: 'Select Image',
		button: {
			text: 'Insert Image'
		},
		library: {
			type: 'image'
		},
		multiple: false
	});
	
	// Add
	$('.upload_slider_img_button').click(function(e) {
		
		
		$(this).parent().find(".hidden_slider_image_div").addClass('hidden_image_tag');
		$(this).parent().find(".hidden_slider_image").addClass('hidden_image_val');
		e.preventDefault();
		sliderImage.open();
	});
	
	// Open
	sliderImage.on('open', function() {
		sliderSelection = sliderImage.state().get('selection');

		var attachment = wp.media.attachment(i);
		sliderSelection.add(wp.media.attachment);
			
	});
	
	// Select
	sliderImage.on('select', function() {
		sliderSelection = sliderImage.state().get('selection');

		//$("#gallery_images ul.slideshow_images").empty();
		sliderImg = '';

		sliderSelection.map(function(attachment) {
			console.log(attachment);
			//alert(attachment.attributes.url);
			$(".hidden_image_val").val(attachment.id);
			$(".hidden_image_tag" ).html("<img width='150' height='150' src="+attachment.attributes.url+"  loading='lazy'>");
			$(".hidden_slider_image").removeClass('hidden_image_val');
			$(".hidden_slider_image_div").removeClass('hidden_image_tag');
			
			//$("#gallery_images ul.slideshow_images").append('<div class="mysite-gallery-image"><input name="slideshow_images[]" value="'+id+'" type="hidden"><img src="'+url+'"><div class="mysite-gallery-remove"></div></div>');
		});
		//alert($this.html());
	});
	
	
});