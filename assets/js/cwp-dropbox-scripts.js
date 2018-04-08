jQuery(function($){
	var table = $('#dropbox-table');

	if(table.length) {
		$(document).ready(function() {
	        //$('#dropbox-table').DataTable();
	        if(table.find('.one-file').length == 0) {
				table.find('.empty-table').show();
			}
		});

		$(document).on('click', '.upload-file', function(){
			$('#select-file-upload').trigger('click');
		});

		$(document).on('change', '#select-file-upload', function(e){
			$('.upload-file').text('Uploading...');
			$('.upload-file').css({'pointer-events': 'none', 'box-shadow': 'none'});
			$('.upload-file').blur();
			$('#dropbox_upfile_form').submit();
		});

		$(document).on('click', '.db-trash', function(){
			$(this).hide();
			$(this).closest('td').find('.confirmation-delete').fadeIn();
		});

		$(document).on('click', '.delete-no', function(){
			$(this).closest('.confirmation-delete').hide();
			$(this).closest('td').find('.db-trash').fadeIn();
		});

		$(document).on('click', '.delete-yes', function(){
			var token = table.attr('data-key');
			var path = $(this).closest('td').find('.db-trash').attr('data-path');
			var thisTR = $(this).closest('tr');

			var request_settings = {
	            path: path
	        }

	    	$.ajax({
	            url: 'https://api.dropboxapi.com/2/files/delete_v2',
	            type: 'POST',
	            dataType: 'JSON',
	            contentType: "application/json",
	            data: JSON.stringify(request_settings),
		    	beforeSend: function (xhr) {
				    xhr.setRequestHeader('Authorization', 'Bearer '+token);
				},
		        success: function (result) {
		        	thisTR.fadeOut(function(){
			    		if(table.find('.one-file').length == 1) {
			    			table.find('.empty-table').fadeIn();
			    		}
			    		thisTR.remove();
			    	});
		        },
		        error: function (error) {
		            console.log(error);
		        }
	    	});
		});
	}
});