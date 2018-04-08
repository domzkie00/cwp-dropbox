jQuery(function($){
    var selected;

	$(document).on('change', '#integration-select-type', function(){
        if($(this).val() == 'dropbox') {
        	var app_token = $(this).find(':selected').attr('data-key');
            var select_album = $('#integration-select-folder');
            select_album.html('<option selected="true" disabled="disabled">Select Folder</option>');
            select_album.attr('disabled', 'disabled');

        	var request_settings = {
	            path: "",
			    recursive: false,
			    include_media_info: false,
			    include_deleted: false,
			    include_has_explicit_shared_members: false,
			    include_mounted_folders: true,
	        }

        	$.ajax({
	            url: 'https://api.dropboxapi.com/2/files/list_folder',
	            type: 'POST',
	            dataType: 'JSON',
	            contentType: "application/json",
	            data: JSON.stringify(request_settings),
        	beforeSend: function (xhr) {
			    xhr.setRequestHeader('Authorization', 'Bearer '+app_token);
			},
            success: function (result) {
            	if(result.entries) {
            		var select = $('#integration-select-folder');
            		select.find('.root-folder').remove();

            		$.each(result.entries, function(){
            			if(this['.tag'] == 'folder') {
        					var option = '<option class="root-folder" value="'+this['path_display']+'">'+this['name']+'</option>';
        					select.append(option);
            			}
            		});
            	}
            },
            error: function (error) {
                console.log(error);
            },
            complete: function () {
                $('#integration-select-folder').find('option').each(function(){
                    if($(this).text() === '') {
                        $(this).remove();
                    }
                });

                if(selected) {
                    $('#integration-select-folder option[value="'+selected+'"]').attr('selected','selected');
                }

                select_album.removeAttr('disabled');
            }
        	});
        }
    });

    $(document).ready(function() {
        if($('#integration-select-type').val() == 'dropbox') {
    		selected = $('#integration-select-folder').val();
            $('#integration-select-type').trigger('change');
        }
	});
});