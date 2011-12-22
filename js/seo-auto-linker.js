jQuery(document).ready(function(){
	var field = jQuery('tr.new-link').html();
	jQuery('a#pmg-add-more-rows').click(function(){
		jQuery( 'table#links-definitions tbody').append('<tr class="links-entry">' + field + '</tr>' );
	});
	jQuery('a.pmg-delete-row').click(function(){
		jQuery(this).parent('td').parent('tr').remove();
	});
});