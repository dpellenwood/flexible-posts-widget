/*
 * Flexible Posts Widget
 * Admin Scripts
 * Author: dpe415
 * URI: http://wordpress.org/extend/plugins/flexible-posts-widget/
 */

jQuery(function($) {
	
	// Setup the show/hide thumbnails box
	jQuery('input.dpe-fp-thumbnail').each( function() {
		if( this.checked ) {
			jQuery(this).parent().next().slideDown('fast');
		} else {
			jQuery(this).parent().next().slideUp('fast');
		}
	});
	
	// Enable the Get Em By tabs
	jQuery('.getembytabs').tabs({
		// Set the active tab to a widget option
		activate: function( event, ui ) {
			jQuery(this).find('.cur_tab').val( jQuery( this ).tabs( "option", "active" ) );
		},
		// retrieve the saved active tab and set it for the UI
		create: function( event, ui ) {
			jQuery( this ).tabs( "option", "active", jQuery(this).find('.cur_tab').val() );
		}
	});
	
});

// Add the tabs functionality AJAX returns
jQuery(document).ajaxComplete(function() {
	jQuery('.getembytabs').tabs({
		// Set the active tab to a widget option
		activate: function( event, ui ) {
			jQuery(this).find('.cur_tab').val( jQuery(this).tabs( "option", "active" ) );
		},
		// retrieve the saved active tab and set it for the UIß
		create: function( event, ui ) {
			jQuery(this).tabs( "option", "active", jQuery(this).find('.cur_tab').val() );
		}
	});
});

// Add event triggers to the show/hide thumbnails box
jQuery('#widgets-right').on("change", 'input.dpe-fp-thumbnail', function(event) {
	if( this.checked ) {
		jQuery(this).parent().next().slideDown('fast');
	} else {
		jQuery(this).parent().next().slideUp('fast');
	}
});

// Setup the get_terms callback
jQuery('#widgets-right').on("change", 'select.dpe-fp-taxonomy', function(event) {
	
	var terms_div	= jQuery(this).parent().nextAll('div.terms');
	var terms_label	= jQuery(this).parent().next('label');
	
	// If we're not ignoring Taxonomy & Term...
	if( jQuery(this).val() != 'none' ) {
		
		
		terms_label.html(objectL10n.gettingTerms).show();
		
		var selected_terms = [];
		terms_div.find("input:checked").each(function () {
		    selected_terms.push( jQuery(this).val() );
		});
		
		var data = {
			action:		'dpe_fp_get_terms',
			taxonomy:	jQuery(this).val(),
			term:		selected_terms,
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			terms_div.html(response);
			terms_label.html(objectL10n.selectTerms).show();
			terms_div.slideDown();
		}).error( function() {
			terms_label.html(objectL10n.noTermsFound).show();		
		});
	
	} else {
		terms_div.slideUp().html('');
		terms_label.hide();
	}
	
});