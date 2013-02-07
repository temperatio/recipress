jQuery(function($) {
	
	var hasRecipe = $( '#hasRecipe' );
	$(hasRecipe).change(function(){
		$( '#recipress_table' ).slideToggle( 'slow' );
	});
	if($(hasRecipe).is( ':checked' ) ) $( '#recipress_table' ).show();
	
	// the upload image button, saves the id and outputs a preview of the image
	var imageFrame;
	$( '.recipress_upload_image_button' ).click( function(e) {
		e.preventDefault();
		
		var options, attachment;
		
		$self = $(event.target);
		$div = $self.closest('div.recipress_image');
		
		// if the frame already exists, open it
		if ( imageFrame ) {
			imageFrame.open();
			return;
		}
		
		// set our settings
		imageFrame = wp.media({
			title: 'Choose Image',
			multiple: false,
			library: {
		 		type: 'image'
			},
			button: {
		  		text: 'Use This Image'
			}
		});
		
		// set up our select handler
		imageFrame.on( 'select', function() {
			selection = imageFrame.state().get('selection');
			
			if ( ! selection )
			return;
			
			// loop through the selected files
			selection.each( function( attachment ) {
				console.log(attachment);
				var src = attachment.attributes.sizes.full.url;
				var id = attachment.id;
				
				$div.find('.recipress_preview_image').attr('src', src);
				$div.find('.recipress_upload_image').val(id);
			} );
		});
		
		// open the frame
		imageFrame.open();
	});
	
	$( '.recipress_clear_image_button' ).click(function() {
		$(this).parent().siblings( '.recipress_upload_image' ).val( '' );
		$(this).parent().siblings( '.recipress_preview_image' ).attr( 'src', pluginDir + 'img/image.png' );
		return false;
	});
	
	// repeatable fields	
	$('.ingredient_add, .instruction_add').live('click', function() {
		// clone
		var row = $(this).parentsUntil( 'td' ).find('.tbody>.tr:last');
		var clone = row.clone();
		// remove chosen
		clone.find('.chosen').removeAttr('style', '').removeClass('chzn-done').data('chosen', null).next().remove();
		row.after(clone);
		// clear values
		clone.find('input[type=text], textarea, select').val('')
		// increment numbers
		clone.find('input[type=text], textarea, select')
			.attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
			})
			.attr('id', function(index, id) {
				return id.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1;
				});
			});
		// clear the image
		clone.find('.recipress_preview_image').attr('src', pluginDir + 'img/image.png')
		// put chosen back
		clone.find('.chosen').chosen({
			create_option: true,
			persistent_create_option: true,
			allow_single_deselect: true
		});
		//
		return false;
	});
	
	$( '.ingredient_remove, .instruction_remove' ).live('click', function() {
		$(this).parentsUntil( '.tr' ).parent().remove();
		return false;
	});
	
	$( '#ingredients_table .tbody, #instructions_table .tbody' ).sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.sort'
	});
	
	//On Click Event
	$( '.image_radio label' ).click(function() {
		$( '.image_radio label' ).removeClass( 'active' ); // Remove any 'active' class
		$(this).addClass( 'active' ); // Add 'active' class to selected tab
	});
	
	// chosen
	$('.chosen').chosen({
    	create_option: true,
    	persistent_create_option: true,
		allow_single_deselect: true
	});

});