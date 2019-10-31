jQuery(function($){
    $('#thememaster .panel').hide(); 
    $('#configuration_fieldset_0').show();
    $('.nav-tabs li:first').addClass('active');
    $('.nav-tabs a').click(function(){
        $(this).parent().addClass('active').siblings().removeClass('active');
        var fieldsetID = $(this).attr('data-fieldset').split(',');	
	$('#thememaster .panel').hide();
        $.each(fieldsetID,function(i,n){
            $('#configuration_fieldset_' + n).show();
        });
    });
});
