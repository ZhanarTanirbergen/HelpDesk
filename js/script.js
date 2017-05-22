$(function(){

	$('body').on('click', '[data-action]', function(e){
		
		$.ajax({
			dataType: 'json'
			,url: $(this).attr('data-action')
			,success: function(data){

				if(typeof data.delete !== 'undefined'){

					location.reload();
					return;
				}

				bootbox.dialog({
					title: data.title
					,message: data.content
				});
			}
		});
	});
});
