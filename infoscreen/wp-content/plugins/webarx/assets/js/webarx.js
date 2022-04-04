/**
 * Patchstack
 * https://patchstack.com
 */

window.WebARX = window.WebARX || {};

( function( window, document, $, plugin ) {
	var $c = {};

	plugin.init = function() {
		plugin.cache();
		plugin.bindEvents();
	};

	plugin.cache = function() {
		$c.window = $( window );
		$c.body = $( document.body );
	};

	plugin.bindEvents = function() {
		$('#webarx-activate-license').on( 'click', function() {
			var clientid = $( '#webarx_api_client_id' ).val();
			var secretkey = $( '#webarx_api_client_secret_key' ).val();
			var postData = {
				action: 'activate_license',
				clientid: clientid,
				secretkey: secretkey,
				WebarxNonce: WebarxVars.nonce
			};
			if ( 0 == $.trim( clientid ).length ) {
				alert( 'Please enter the API Client ID!' );
				return false;
			}
			if ( 0 == $.trim( secretkey ).length ) {
				alert( 'Please enter the API Client Secret Key!' );
				return false;
			}
			$.post( WebarxVars.ajaxurl, postData, function( response ) {
				var jsonobj = $.parseJSON( response );
				if ( 'error' == jsonobj.result ) {
					alert( WebarxVars.error_message );
				} else {
					$( '#hiddenstatusbox' ).show();
					$( '#webarx_license_key_result' ).text( jsonobj.message );
					$( '#webarx-activate-license' ).hide();
					$( '#webarx-deactivate-license' ).show();
					$( '#webarx-test-api' ).show();
				}
			});
		});

		$('#webarx-deactivate-license').on( 'click', function() {
			var clientid = $( '#webarx_api_client_id' ).val();
			var secretkey = $( '#webarx_api_client_secret_key' ).val();
			var postData = {
				action: 'deactivate_license',
				clientid: clientid,
				secretkey: secretkey,
				WebarxNonce: WebarxVars.nonce
			};
			if ( 0 == $.trim( clientid ).length ) {
				alert( 'Please enter the API Client ID!' );
				return false;
			}
			if ( 0 == $.trim( secretkey ).length ) {
				alert( 'Please enter the API Client Secret Key!' );
				return false;
			}
			$.post( WebarxVars.ajaxurl, postData, function( response ) {
				var jsonobj = $.parseJSON( response );
				if ( 'error' == jsonobj.result ) {
					alert( WebarxVars.error_message );
				} else {
					$( '#hiddenstatusbox' ).show();
					$( '#webarx_license_key_result' ).text( jsonobj.message );
					$( '#webarx-activate-license' ).show();
					$( '#webarx-deactivate-license' ).hide();
					$( '#webarx-test-api' ).hide();
				}
			});
		});

		$('#webarx-test-api').on( 'click', function() {
			var postData = {
				action: 'test_api',
				WebarxNonce: WebarxVars.nonce
			};
			$.post( WebarxVars.ajaxurl, postData, function( response ) {
				var jsonobj = $.parseJSON( response );
				$( '#hiddenstatusbox' ).show();
				if ( 'success' == jsonobj.result ) {
					$( '#webarx_license_key_result' ).text( 'API connected successfully!' );
				} else {
					$( '#webarx_license_key_result' ).text( jsonobj.message );
				}
				$( '#webarx-activate-license' ).hide();
				$( '#webarx-deactivate-license' ).show();
				$( '#webarx-test-api' ).show();
			});
		});

		$('#webarx_send_mail_url').on( 'click', function() {
			var postData = {
				action: 'send_new_url_email',
				WebarxNonce: WebarxVars.nonce
			};
			$.post( WebarxVars.ajaxurl, postData, function( response ) {
				if ( 'fail' == response ) {
					alert( WebarxVars.error_message );
				} else {
					alert( 'Email Sent!' );
				}
			});
		});

		// Don't load this part if DataTables is not loaded.
		if(typeof jQuery.fn.dataTable !== 'undefined' && window.location.href.indexOf('webarx') !== -1){

			// Initialize datatables.
			$('.table-firewall-log').DataTable({
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": WebarxVars.ajaxurl,
					"type": "POST",
					"data": function(d){
						d.action = 'firewall_log_table';
						d.WebarxNonce = $("meta[name=webarx_nonce]").attr("value");
					}
				},
				"responsive": true,
				"columns": [
					{ "data": "fid" },
					{ "data": "fid" },
					{ "data": "referer" },
					{ "data": "method" },
					{ "data": "ip", render: $.fn.dataTable.render.text() },
					{ "data": "log_date" }
				],
				"order": [[0, "desc"]],
				"searching": false,
				"ordering": false,
				"drawCallback": function( settings ) {
					$('.titletip').tooltip();
				},
				"columnDefs": [
					{
						"render": function ( data, type, row ) {

							var severity = "low";

							// Medium
							var med = ["xss", "csrf", "xsrf"];
							for(var i = 0; i < med.length; i++){
								if(String(row.fid).toLowerCase().indexOf(med[i]) !== -1){
									severity = "medium";
								}
							}

							// High
							var high = ["sqli", "lfi", "rfe", "id", "files"];
							for(var i = 0; i < high.length; i++){
								if(String(row.fid).toLowerCase().indexOf(high[i]) !== -1){
									severity = "high";
								}
							}
							var color = 'blue';
							if(severity === 'medium'){
								color = 'orange';
							}
                            if(severity === 'high'){
                                color = 'red';
                            }

							return '<small style="color: '+ color +'">' + severity.toUpperCase() + '</small>';
						},
						"targets": 0
					},
					{
						"render": function ( data, type, row ) {
							var title = (data ? data : 'Unknown');
							var description = (row.description ? row.description : 'No Explanation');

							return '<span class=" titletip" title="' + description + '">' + title + '</span>';
						},
						"targets": 1
					},
					{
						"render": function ( data, type, row ) {
							return decodeURIComponent(data).replace(/</g,"&lt;").replace(/>/g,"&gt;").replace('+', ' ');
						},
						"targets": [2]
					}
				]
			});

			$('.table-user-log').DataTable({
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": WebarxVars.ajaxurl,
					"type": "POST",
					"data": function(d){
						d.action = 'users_log_table';
						d.WebarxNonce = $("meta[name=webarx_nonce]").attr("value");
					}
				},
				"responsive": true,
				"columns": [
					{ "data": "author" },
					{ "data": "action", render: $.fn.dataTable.render.text() },
					{ "data": "object", render: $.fn.dataTable.render.text() },
					{ "data": "object_name", render: $.fn.dataTable.render.text() },
					{ "data": "ip", render: $.fn.dataTable.render.text() },
                    { "data": "date", render: $.fn.dataTable.render.text() }
				],
				"order": [[0, "desc"]],
				"searching": true,
				"ordering": false,
                "columnDefs": [
                    {
                        "render": function ( data, type, row ) {
                            if(row.ip == '18.220.89.14'){
                                return 'WebARX API';
							}
							
							return data;
                        },
                        "targets": [0, 4]
                    }
                ]
			});
		}
	};

	$( plugin.init );
}( window, document, jQuery, window.WebARX ) );