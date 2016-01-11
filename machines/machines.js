jQuery(document).ready(function($){
	$("input:checkbox.user-to-machine-checkbox").change(function() { 
		if($(this).is(":checked")) { 
			$.ajax({
				url: '../api/machine/add_user_to_machine',
				type: 'POST',
				data: { "user_id":$(this).attr("data-user-id"), "machine_id":$(this).attr("data-machine-id")}
			});
		} else {
			$.ajax({
				url: '../api/machine/remove_user_from_machine',
				type: 'POST',
				data: { "user_id":$(this).attr("data-user-id"), "machine_id":$(this).attr("data-machine-id")}
			});
		}
	}); 
  $("select.user-to-role-select").change(function() { 
	$.ajax({
		url: '../api/user/set_role_for_user_id',
		type: 'POST',
		data: { "user_id":$(this).attr("data-user-id"), "role":$(this).val()}
	});
  }); 
  $("input:text.user-to-rfid-textbox").change(function() { 
	$.ajax({
		url: '../api/user/set_rfid_for_user_id',
		type: 'POST',
		data: { "user_id":$(this).attr("data-user-id"), "rfid":$(this).val()}
	});
  }); 
  $("#add-machine-dialog").dialog({                   
        'dialogClass'   : 'wp-dialog',           
        'modal'         : true,
        'autoOpen'      : false, 
        'closeOnEscape' : true,      
        'buttons'       : {
            "Cancel": function() {
               $("#add-machine-dialog").dialog('close');
            },
		  "Add": function() {
			$.ajax({
				url: '../api/machine/add_machine',
				type: 'POST',
				data: $("#add-machine-form").serialize(),
			  success: function(){
				$("#add-machine-dialog").dialog('close');
				window.location = window.location;
			  }
			});
		  }
        }
    });
    $("#add-machine-button").click(function(event) {
        event.preventDefault();
        $("#add-machine-dialog").dialog('open');
    });
  $("#add-machine-rate-dialog").dialog({                   
        'dialogClass'   : 'wp-dialog',           
        'modal'         : true,
        'autoOpen'      : false, 
        'closeOnEscape' : true,      
        'buttons'       : {
            "Cancel": function() {
               $("#add-machine-rate-dialog").dialog('close');
            },
		  "Add": function() {
			$.ajax({
				url: '../api/machine/add_machine_rate',
				type: 'POST',
				data: $("#add-machine-rate-form").serialize(),
			  success: function(){
				$("#add-machine-rate-dialog").dialog('close');
				window.location = window.location;
			  }
			});
		  }
        }
    });
    $("#add-machine-rate-button").click(function(event) {
        event.preventDefault();
        $("#add-machine-rate-dialog").dialog('open');
    });
	$("#machine-query-submit").click(function(event) {
		var user_id = $("#user_id").val();
		var machine_id = $("#machine_id").val();
		var url = window.location.href;
		url = url+"&user_id="+user_id+"&machine_id="+machine_id;
		window.location.href = url;
	});
});
