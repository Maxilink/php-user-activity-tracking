(function($){
	$(document).ready(function(){

		var deactivation_started = false;
		var clear_all_log_started = false;

    $(document).on('click','.button-uat-deactivate-licence, .uat_deactivate_license_key',function(e){
      if ( ! deactivation_started ) {
        e.preventDefault();
        $('.uat-admin-popup.uat-admin-popup-deactivate').fadeIn(200);
      } else {
        $(this).closest('form').submit();
      }
    });

    function uat_check_permissions() {
    	var response = false;
    	if ( typeof moove_backend_activity_scripts.extras !== 'undefined' ) {
				response = 'usla' === moove_backend_activity_scripts.extras;
			}
    	return response;
    }

    function update_table_cols( table ) {
    	if ( table ) {
    		if ( typeof moove_backend_activity_scripts.tsop !== 'undefined' && typeof moove_backend_activity_scripts.tsop.cols === 'object' ) {
    			var lmt = parseInt( moove_backend_activity_scripts.tsop.tc );
					var col_s = Object.keys(moove_backend_activity_scripts.tsop.cols).map(function (key) { 
						return moove_backend_activity_scripts.tsop.cols[key]; 
					});
					for (var col_i = 0; col_i <= lmt; col_i++) {
					  var col_val = col_s.includes(col_i);
					  table.column(col_i).visible(col_val);
					}		
				}
    	}
    }

    function update_table_len( len ) {
    	if ( len ) {
    		if ( typeof moove_backend_activity_scripts.tsop !== 'undefined' && typeof moove_backend_activity_scripts.tsop.len === 'number' ) {
    			$.post(
						moove_backend_activity_scripts.ajaxurl,
						{
							action: "uat_manage_table_settings",
							dt_nonce: dt_nonce,
							type: 'update_len',
							len: len,
						},
						function( msg ) {							
							try {
								var obj = JSON.parse( msg );
								if ( typeof obj.len !== 'undefined' ) {
									moove_backend_activity_scripts.tsop.len = obj.len;
								}
							} catch(e) {
								console.warn(e);
							}
						}
					);

				}
    	}
    }

    function get_table_len() {
    	var len = 100;
    	if ( typeof moove_backend_activity_scripts.tsop.len === 'number' ) {
    		len = moove_backend_activity_scripts.tsop.len;
    	}
    }

    function uat_cpt_colvis_update( e, settings, column, state ) {
    	var state = (state ? 'visible' : 'hidden');
    	var	dt_nonce 	= jQuery('#moove_uat_dt_log_nonce').val();
    
    	if ( 'number' !== typeof moove_backend_activity_scripts.tsop.cols[column] ) {
  			$.post(
					moove_backend_activity_scripts.ajaxurl,
					{
						action: "uat_manage_table_settings",
						dt_nonce: dt_nonce,
						type: 'update_column',
						col: column,
						state: state
					},
					function( msg ) {
						try {
							var obj = JSON.parse( msg );
							if ( typeof obj.cols !== 'undefined' ) {
								moove_backend_activity_scripts.cols = obj.cols;
							}
						} catch(e) {
							console.warn(e);
						}
					}
				);
    	} else {
    		if ( 'hidden' === state ) {
    			$.post(
						moove_backend_activity_scripts.ajaxurl,
						{
							action: "uat_manage_table_settings",
							dt_nonce: dt_nonce,
							type: 'update_column',
							col: column,
							state: state
						},
						function( msg ) {
							try {
								var obj = JSON.parse( msg );
								if ( typeof obj.cols !== 'undefined' ) {
									moove_backend_activity_scripts.cols = obj.cols;
								}
							} catch(e) {
								console.warn(e);
							}
						}
					);
    		}
    	}
    }

    if ( $('.uat-dt-log-all').length > 0 ) {
    	var	dt_nonce 	= jQuery('#moove_uat_dt_log_nonce').val();

			var dt_svs_nf	= moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_get_dt_logs";
			jQuery('.uat_dt_log_table').attr('id', 'uat_cpt_datatable' );
			var responsive = typeof moove_backend_activity_scripts.rsp !== 'undefined' ? moove_backend_activity_scripts.rsp : true;
			var table 		= jQuery('.uat_dt_log_table').DataTable( {
				order: [[ 0, "desc" ]],
				responsive: responsive,
				scrollX: ! responsive,
				dom: 'lfrtiBp',
				lengthMenu: [10, 25, 50, 100, 200, 500, 1000],
				pageLength: get_table_len(),
				pagingType: "input",
				orderCellsTop: true,
				initComplete: function(settings, json) {
					
					if ( typeof json.date_filter !== 'undefined' && json.date_filter.length > 0 ) {
	        	jQuery(document).find('#dt-date-filter').each(function(){
	        		var select = jQuery(this);
	        		for (var property in json.date_filter ) {	        			
							  var mh 			= json.date_filter[property].ym;
							  var _year 	= mh.slice(0, 4);
							  var _month 	= mh.slice(4);
							  var _date 	= new Date(_year + '-' + _month + '-01');
							  var label 	= _date.toLocaleString('default', { month: 'long' }) + ', ' + _date.toLocaleString('default', { year: 'numeric' });
							  var option 	= '<option value="'+mh+'">'+label+'</option>';
							  select.append(option);
							}
	        	});
	        }

	        if ( typeof json.users_filter !== 'undefined' && json.users_filter.length > 0 ) {
	        	jQuery(document).find('#dt-user-filter').each(function(){
	        		var select = jQuery(this);
	        		for (var property in json.users_filter ) {
							  var uid 			= json.users_filter[property].user_id;
							  if ( parseInt(uid) ) {
							  	var user_label = json.users_filter[property].display_name ? json.users_filter[property].display_name : json.users_filter[property].username;
							  	if ( user_label ) {
								  	var option 	= '<option value="'+parseInt(uid)+'">'+user_label+'</option>';
								  }
								  select.append(option);
							  }
							}
	        	});
	        }

	        if ( typeof json.users_role_filter !== 'undefined' ) {
	        	jQuery(document).find('#dt-user_role-filter').each(function(){
	        		var select = jQuery(this);
	        		for (var role in json.users_role_filter ) {
							  var uids 			= json.users_role_filter[role];
							  if ( role && uids ) {
								  var option 	= '<option value="'+uids+'">'+role.charAt(0).toUpperCase()+role.slice(1)+'</option>';
								  select.append(option);
							  }
							}
	        	});
	        }
				},
				language: {
			    "paginate": {
		        "first":      "<span aria-hidden='true'>«</span>",
		        "last":       "<span aria-hidden='true'>»</span>",
		        "next":       "<span aria-hidden='true'>›</span>",
		        "previous":   "<span aria-hidden='true'>‹</span>",
		    	},
		    	"emptyTable": "No logs were found."
			  },
				buttons: [
					{
						extend: 'colvis',
						text: 'Adjust columns',
						available: function ( dt, config ) {
              return uat_check_permissions();
            },
						columnText: function ( dt, idx, title ) {
							return (idx+1)+': '+title;
						}
					},
					{
						extend: 'csv',
						text: 'Export All Logs',
						filename: 'tracking-log-' + Date.now(),
						available: function ( dt, config ) {
              return uat_check_permissions();
            },
						action: function (e, dt, node, config) {
							var button 		= jQuery(e.target).closest('button');
							var button_s 	= jQuery(e.target);
							var button_dt = button_s.text();
							button_s.text('Exporting...');
							button.prop('disabled', true).addClass('dt-disabled');
	            jQuery.ajax({
	                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_export_dt_logs&type=all",
	                "data": dt.ajax.params(),
	                "success": function(res, status, xhr) {
	                	var filename = 'tracking-log-' + Date.now() + '-all.csv';
	                	var response = JSON.parse( res );
	                	var headers = response.headers;
	                	var rows = response.data;
										var processRow = function (row) {
							        var finalVal = '';

							        for (var j = 0; j < row.length; j++) {
						            var innerValue = row[j] === null ? '' : row[j].toString();
						            if (row[j] instanceof Date) {
						              innerValue = row[j].toLocaleString();
						            };
						            var result = innerValue.replace(/"/g, '""');
						            if (result.search(/("|,|\n)/g) >= 0) {
						              result = '"' + result + '"';
						            }
						            if (j > 0) {
						              finalVal += ',';
						            }
						            finalVal += result;
							        }
							        return finalVal + '\n';
										};

								    var csvFile = '';
								    for (var z = 0; z < headers.length; z++) {
								      csvFile += processRow(headers[z]);
								    }

								    for (var i = 0; i < rows.length; i++) {
								      csvFile += processRow(rows[i]);
								    }

								    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
								    if (navigator.msSaveBlob) { // IE 10+
								      navigator.msSaveBlob(blob, filename);
								    } else {
							        var link = document.createElement("a");
							        if (link.download !== undefined) { // feature detection
						            // Browsers that support HTML5 download attribute
						            var url = URL.createObjectURL(blob);
						            link.setAttribute("href", url);
						            link.setAttribute("download", filename);
						            link.style.visibility = 'hidden';
						            document.body.appendChild(link);
						            link.click();
						            document.body.removeChild(link);
							        }
								    }

								    button.prop('disabled', false).removeClass('dt-disabled');
								    button_s.text(button_dt);
	                }
	            });
	        	}
					},
					{
						extend: 'csv',
						text: 'Export Filtered Logs',
						attr: { 
							id: 'dt-export-filtered-all-logs',
							class: 'dt-button buttons-csv buttons-csv-filtered uat-hidden-btn'
						},
						available: function ( dt, config ) {
              return uat_check_permissions();
            },
						filename: 'tracking-log-' + Date.now(),
						action: function (e, dt, node, config) {
							var button 		= jQuery(e.target).closest('button');
							var button_s 	= jQuery(e.target);
							var button_dt = button_s.text();
							button_s.text('Exporting...');
							button.prop('disabled', true).addClass('dt-disabled');
	            jQuery.ajax({
	                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce="+dt_nonce+"&action=uat_activity_export_dt_logs&type=filtered",
	                "data": dt.ajax.params(),
	                "success": function(res, status, xhr) {
	                	var filename = 'tracking-log-' + Date.now() + '-filtered.csv';
	                	var response = JSON.parse( res );
	                	var headers = response.headers;
	                	var rows = response.data;
										var processRow = function (row) {
							        var finalVal = '';

							        for (var j = 0; j < row.length; j++) {
						            var innerValue = row[j] === null ? '' : row[j].toString();
						            if (row[j] instanceof Date) {
						              innerValue = row[j].toLocaleString();
						            };
						            var result = innerValue.replace(/"/g, '""');
						            if (result.search(/("|,|\n)/g) >= 0) {
						              result = '"' + result + '"';
						            }
						            if (j > 0) {
						              finalVal += ',';
						            }
						            finalVal += result;
							        }
							        return finalVal + '\n';
										};

								    var csvFile = '';
								    for (var z = 0; z < headers.length; z++) {
								      csvFile += processRow(headers[z]);
								    }

								    for (var i = 0; i < rows.length; i++) {
								      csvFile += processRow(rows[i]);
								    }

								    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
								    if (navigator.msSaveBlob) { // IE 10+
								      navigator.msSaveBlob(blob, filename);
								    } else {
							        var link = document.createElement("a");
							        if (link.download !== undefined) { // feature detection
						            // Browsers that support HTML5 download attribute
						            var url = URL.createObjectURL(blob);
						            link.setAttribute("href", url);
						            link.setAttribute("download", filename);
						            link.style.visibility = 'hidden';
						            document.body.appendChild(link);
						            link.click();
						            document.body.removeChild(link);
							        }
								    }

								    button.prop('disabled', false).removeClass('dt-disabled');
								    button_s.text(button_dt);
	                }
	            });
	        	}
					},
					{
						extend: 'copy',
						attr: { 
							id: 'dt-clear-filtered-logs',
							class: 'dt-button buttons-delete buttons-delete-filtered uat-hidden-btn'
						},
						text: '<span class="dashicons dashicons-trash"></span> Delete Filtered Logs',
						action: function (e, dt, node, config) {
							e.preventDefault();
							jQuery(document).on('click','.uat-admin-popup-clear-filtered-log-confirm .clear-filtered-logs',function(e) {
								$('.uat-admin-popup-clear-filtered-log-confirm').fadeOut(200);
								jQuery.ajax({
	                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_delete_dt_logs&type=filtered",
	                "data": dt.ajax.params(),
	                "success": function(res, status, xhr) {
	                	table.draw();
	                }
	               });
							});
						}
					},
					{
						extend: 'copy',
						attr: { 
							id: 'dt-clear-all-logs',
							class: 'dt-button buttons-delete'
						},
						text: '<span class="dashicons dashicons-trash"></span> Delete All Logs',
						action: function (e, dt, node, config) {
							e.preventDefault();
							jQuery(document).on('click','.uat-admin-popup-clear-log-confirm .clear-all-logs',function(e) {
								jQuery.ajax({
	                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_delete_dt_logs&type=all",
	                "data": dt.ajax.params(),
	                "success": function(res, status, xhr) {
	                	var response = JSON.parse( res );
	                	if ( response.success ) {
	                		table.draw();
	                		console.warn('logs-deleted');
	                	} else {
	                		console.warn('error');
	                	}
	                }
	               });
							});
						}
					},
				],
				"processing": true,
				"serverSide": true,
				"stateSave": uat_check_permissions(),
				"ajax": {
			    "url": dt_svs_nf,
			    "data": function( d ) {
			    	var filters = {};
			      jQuery('.uat-dt-top-filters').find('select').each(function(){
							var name = jQuery(this).attr('name');
							var value = jQuery(this).val();
							if ( parseInt(value) !== -1 ) {
								filters[name] = value;
							}
						});
						d.top_filters = filters;

						if ( Object.keys(filters).length > 0 || $('#post-search-input').closest('.moove-activity-log-report').find('.dataTables_filter').find('input').val() !== '' ) {
							$('.buttons-csv-filtered').removeClass('uat-hidden-btn');
							$('.buttons-delete-filtered').removeClass('uat-hidden-btn');
							console.warn('has-filters');
						} else {
							$('.buttons-csv-filtered').addClass('uat-hidden-btn');
							$('.buttons-delete-filtered').addClass('uat-hidden-btn');
							console.warn('no-filters');
						}
			    }
			  },
			} );
	
			table.on( 'column-visibility.dt', function ( e, settings, column, state ) {
		    uat_cpt_colvis_update( e, settings, column, state );
			});

			table.on( 'length.dt', function ( e, settings, len ) {
		    update_table_len( len );
			});

			jQuery(document).on('change', '.uat-dt-top-filters select', function() {				
				table.draw();
			});

			jQuery(document).on('keyup change click', '#post-search-input', function(){
				var val = jQuery(this).val();
				jQuery(this).closest('.moove-activity-log-report').find('.dataTables_filter').find('input').val(val).trigger('keyup');
			});

			jQuery('#post-search-input').each(function(){
				jQuery(this).val(jQuery(this).closest('.moove-activity-log-report').find('.dataTables_filter').find('input').val());
			});

			update_table_cols( table );
    }

    function uat_cpt_get_log_table( log_id, element, action ) {
				if ( ! element.hasClass('uat_cpt-log-loaded') ) {
					element.addClass('uat_cpt-log-loaded')
		    	var wrap = $('#moove-accordion-' + log_id);
		    	if ( wrap.length > 0 ) {
		    		wrap.html($("#uat-table-section-html").html());
		    		wrap.prepend( '<p style="margin: 0 0 20px;border-bottom:1px solid #eaeaea;padding-bottom:20px;">&#128279; <strong><a href="'+wrap.attr('data-permalink')+'" class="uat_admin_link">'+wrap.attr('data-permalink')+'</a></strong></p>' );

		    		var	dt_nonce 	= jQuery('#moove_uat_dt_log_nonce').val();

						var dt_svs_nf	= moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_get_dt_logs";
						wrap.find('.uat_dt_log_table').attr( 'id', 'uat_cpt_datatable' );
						var responsive = typeof moove_backend_activity_scripts.rsp !== 'undefined' ? moove_backend_activity_scripts.rsp : true;
		    		var table 		= wrap.find('.uat_dt_log_table').DataTable( {
							order: [[ 0, "desc" ]],
							responsive: responsive,
							scrollX: ! responsive,
							dom: 'lfrtiBp',
							lengthMenu: [10, 25, 50, 100, 200, 500, 1000],
							pageLength: get_table_len(),
							pagingType: "input",
							orderCellsTop: true,
							language: {
						    "paginate": {
					        "first":      "<span aria-hidden='true'>«</span>",
					        "last":       "<span aria-hidden='true'>»</span>",
					        "next":       "<span aria-hidden='true'>›</span>",
					        "previous":   "<span aria-hidden='true'>‹</span>",
					    	},
					    	"emptyTable": "No logs were found.",
					    	"search": "_INPUT_",
					    	"searchPlaceholder": "Search Logs...",
						  },
						  initComplete: function(settings, json) {
						  	$('body,html').animate({
				          scrollTop: element.offset().top - 50
				        }, 300);
						  },
							buttons: [
								{
									extend: 'colvis',
									text: 'Adjust columns',
									available: function ( dt, config ) {
			              return uat_check_permissions();
			            },
									columnText: function ( dt, idx, title ) {
										return (idx+1)+': '+title;
									}
								},
								{
									extend: 'csv',
									text: 'Export All Logs',
									available: function ( dt, config ) {
			              return uat_check_permissions();
			            },
									filename: 'tracking-log-' + Date.now(),
									action: function (e, dt, node, config) {
										var button 		= jQuery(e.target).closest('button');
										var button_s 	= jQuery(e.target);
										var button_dt = button_s.text();
										button_s.text('Exporting...');
										button.prop('disabled', true).addClass('dt-disabled');
				            jQuery.ajax({
				                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_export_dt_logs&type=cpt&log_id=" + log_id,
				                "data": dt.ajax.params(),
				                "success": function(res, status, xhr) {
				                	var filename = 'tracking-log-' + Date.now() + '-all.csv';
				                	var response = JSON.parse( res );
				                	var headers = response.headers;
				                	var rows = response.data;
													var processRow = function (row) {
										        var finalVal = '';

										        for (var j = 0; j < row.length; j++) {
									            var innerValue = row[j] === null ? '' : row[j].toString();
									            if (row[j] instanceof Date) {
									              innerValue = row[j].toLocaleString();
									            };
									            var result = innerValue.replace(/"/g, '""');
									            if (result.search(/("|,|\n)/g) >= 0) {
									              result = '"' + result + '"';
									            }
									            if (j > 0) {
									              finalVal += ',';
									            }
									            finalVal += result;
										        }
										        return finalVal + '\n';
													};

											    var csvFile = '';
											    for (var z = 0; z < headers.length; z++) {
											      csvFile += processRow(headers[z]);
											    }

											    for (var i = 0; i < rows.length; i++) {
											      csvFile += processRow(rows[i]);
											    }

											    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
											    if (navigator.msSaveBlob) { // IE 10+
											      navigator.msSaveBlob(blob, filename);
											    } else {
										        var link = document.createElement("a");
										        if (link.download !== undefined) { // feature detection
									            // Browsers that support HTML5 download attribute
									            var url = URL.createObjectURL(blob);
									            link.setAttribute("href", url);
									            link.setAttribute("download", filename);
									            link.style.visibility = 'hidden';
									            document.body.appendChild(link);
									            link.click();
									            document.body.removeChild(link);
										        }
											    }

											    button.prop('disabled', false).removeClass('dt-disabled');
											    button_s.text(button_dt);
				                }
				            });
				        	}
								},								
								{
									extend: 'copy',
									attr: { 
										class: 'dt-button buttons-delete buttons-delete-cpt-log'
									},
									text: '<span class="dashicons dashicons-trash"></span> Delete Logs',
									action: function (e, dt, node, config) {
										e.preventDefault();
										jQuery(document).on('click','.uat-admin-popup-clear-log-confirm .clear-all-logs',function(e) {
											jQuery.ajax({
				                "url": moove_backend_activity_scripts.ajaxurl + "?dt_nonce=" + dt_nonce + "&action=uat_activity_delete_dt_logs&type=cpt&id=" + log_id,
				                "data": dt.ajax.params(),
				                "success": function(res, status, xhr) {
				                	var response = JSON.parse( res );
				                	console.warn(response);
				                	if ( response.success ) {
				                		table.draw();
				                		console.warn('logs-deleted');
				                	} else {
				                		console.warn('error');
				                	}
				                	$('body,html').animate({
									          scrollTop: element.offset().top - 50
									        }, 300);
				                }
				               });
										});
									}
								},
							],
							"processing": true,
							"serverSide": true,
							"stateSave": uat_check_permissions(),
							"ajax": {
						    "url": dt_svs_nf,
						    "data": function( d ) {
						    	var filters = {};
									filters['dt-cpt_post_id'] = log_id;
									d.top_filters = filters;
						    }
						  },
						} );

						update_table_cols( table );
						table.column(1).visible(false);

						table.on( 'column-visibility.dt', function ( e, settings, column, state ) {
					    uat_cpt_colvis_update( e, settings, column, state );
						});

						table.on( 'length.dt', function ( e, settings, len ) {
					    update_table_len( len );
						});
		    	}
		    }
	    }

	    // dt-clear-post-logs

			$(document).on('click','.uat-cpt-accordion .moove-accordion-section-title', function(e){
	    	e.preventDefault();
	    	var log_id = $(this).attr('data-id');
	    	var item   = $(this);
	    	$(window).trigger('resize');
	    	if ( log_id && ! $(this).hasClass('uat_cpt-log-loaded') ) {
	    		if ( item.attr('data-type') === 'activity_log' ) {
	    			uat_cpt_get_log_table( log_id, item, 'get' );
	    		}
	    	}
	    	if ( $(this).is('.active') ) {
	    		window.location.replace( $(this).attr('href') );
	    	}
	    });

	    if(window.location.hash) {
	      var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
	      if ( hash.indexOf('moove-accordion-') === 0 && $('#'+hash).length > 0 ) {
	      	var item = $('a.moove-accordion-section-title[href=\\#'+hash+']');
	      	var log_id = item.attr('data-id');
	      	if ( log_id && ! item.hasClass('uat_cpt-log-loaded') ) {
		    		if ( item.attr('data-type') === 'activity_log' ) {
		    			uat_cpt_get_log_table( log_id, item, 'get' );
		    		}
		    	}
	      }
	    }

    $(document).on('input', '.dataTables_paginate .paginate_input', function(e) {
    	e.target.setAttribute('size', e.target.value.length);
    	console.warn(e);
    });

    $(document).on('click','a.uat-help-tab-toggle', function(e) {
          e.preventDefault();
          var target=$(this).attr('href');
          if ( $(target).length > 0 ) {
            $('.uat-help-content-block').slideUp();
            $(target).slideDown();
            $('.uat-help-tab-toggle').removeClass('active');
            var href = $(this).attr('href');
            $(document).find('a.uat-help-tab-toggle[href="'+href+'"]').addClass('active');
          }
          
        });

    $(document).on('click','.uat-et-trigger-box-actions-f.uat-et-help-example-f h4',function(e){
  		e.preventDefault();
  		$(this).closest('.uat-et-help-example-f').toggleClass('open');
  		$(this).closest('.uat-et-help-example-f').find('.trigger-collapse-example').slideToggle(300);
  	});

    if(window.location.hash) {
      var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
      if ( hash.indexOf('moove-accordion-') === 0 && $('#'+hash).length > 0 ) {
      	var item = $('a.moove-accordion-section-title[href=\\#'+hash+']');
      	// Add active class to section title
				item.addClass('active');
				// Open up the hidden content panel
				$('.moove-accordion #' + hash).slideDown(200).addClass('open');

      	$('body,html').animate({
          scrollTop: $('#'+hash).offset().top - 100
        }, 300);
      }
    }

    var reset_settings_confirmed = false;
    $(document).on('submit','.uat-reset-settings-form',function(e){
    	if ( ! reset_settings_confirmed ) {
	    	e.preventDefault();
	    	$('.uat-admin-popup.uat-admin-popup-reset-settings').fadeIn(200);
	    	return false;
    	}
    });

    $(document).on('click','.uat-admin-popup-reset-settings .button-reset-settings-confirm-confirm', function(){
    	reset_settings_confirmed = true;
    	$('.uat-reset-settings-form').submit();
    	$('.uat-admin-popup.uat-admin-popup-reset-settings').fadeOut(200);
    });

    $(document).on('click', '#uat-settings-cnt .nav-tab-collapse', function(){
    	if ( $('#uat-settings-cnt').hasClass('uat-collapsed') ) {
    		// Uncollapse
    		$('#uat-settings-cnt').removeClass('uat-collapsed');
    		
    		var page_url_update = $('#post-query-submit').attr('data-pageurl');
    		page_url_update = page_url_update  && page_url_update.indexOf("&collapsed") !== -1 ? page_url_update.replace('&collapsed','') : page_url_update;
    		$('#search-submit').attr('data-pageurl',page_url_update);
    		$('#post-query-submit').attr('data-pageurl',page_url_update);
    		$(document).find('th.manage-column.sortable').each(function(){
    			var anchor = $(this).find('a');
    			var link = anchor.attr('href');
    			link = link.indexOf("&collapsed") !== -1 ? link.replace('&collapsed','') : link;
    			anchor.attr('href',link);
    		});

    	} else {
    		// Collapse
    		$('#uat-settings-cnt').addClass('uat-collapsed');
    		
    		var page_url_update = $('#post-query-submit').attr('data-pageurl');
    		page_url_update = page_url_update && page_url_update.indexOf("&collapsed") !== -1 ? page_url_update : page_url_update + '&collapsed';
    		$('#search-submit').attr('data-pageurl',page_url_update);
    		$('#post-query-submit').attr('data-pageurl',page_url_update);
    		$(document).find('th.manage-column.sortable').each(function(){
    			var anchor = $(this).find('a');
    			var link = anchor.attr('href');
    			link = link.indexOf("&collapsed") !== -1 ? link : link + '&collapsed';
    			anchor.attr('href',link);
    		});

    	}
    	
    });

    $(document).on('click','.uat-admin-popup .uat-popup-overlay, .uat-admin-popup .uat-popup-close',function(e){
      e.preventDefault();
      $(this).closest('.uat-admin-popup').fadeOut(200);
    });

    $(document).on('click','.uat-admin-popup.uat-admin-popup-deactivate .button-deactivate-confirm',function(e){
      e.preventDefault();
      deactivation_started = true;
      $("<input type='hidden' value='1' />")
       .attr("id", "uat_deactivate_license")
       .attr("name", "uat_deactivate_license")
       .appendTo("#moove_uat_license_settings");
      $('#moove_uat_license_settings').submit();
      $(this).closest('.uat-admin-popup').fadeOut(200);
    });

    $(document).on('click','.uat-cookie-alert .uat-dismiss', function(e){
      e.preventDefault();
      $(this).closest('.uat-cookie-alert').slideUp(400);
      var ajax_url = $(this).attr('data-adminajax');
      var user_id = $(this).attr('data-uid');

      jQuery.post(
        ajax_url,
        {
          action: 'moove_hide_language_notice',
          user_id: user_id
        },
        function( msg ) {

        }
      );
    });


		if( $( '.moove-activity-log-report .moove-form-container select' ).length > 0 ) {
			$( '.moove-form-container select' ).select2();
			$( document.body ).on( "click", function() {
				$( '.moove-form-container select' ).select2();
			});
		}

		var individual_box_confirmed = false;
		// delete backlink
		$('body').on('change', '.ma-checkbox', function (e) {
			if ( $(this).is(':checked') ) {
				$('.uat-admin-popup-clear-log-confirm').fadeIn(200);
				$('.ma-checkbox').prop('checked',false);
				return false;
			}
			
		});

		$(document).on('click','.button-disable-tracking-individual-post', function(e){
			e.preventDefault();
			individual_box_confirmed = true;
			$('.ma-checkbox').prop('checked',true);
			$('.uat-admin-popup-clear-log-confirm').fadeOut(200);
			$('.uat-admin-popup-clear-filtered-log-confirm').fadeOut(200);
		});

		$(document).on('change','input[name="moove-activity-dtf"]',function(){
			console.log($(this).val());
			if ( $(this).val() === 'c' ) {
				$('#screen-options-wrap .moove-activity-screen-ctm').removeClass('moove-hidden');
			} else {
				$('#screen-options-wrap .moove-activity-screen-ctm').addClass('moove-hidden');
			}
		});

		// ACCORDION SETTINGS
		function close_accordion_section() {
			$('.moove-accordion .moove-accordion-section-title').removeClass('active');
			$('.moove-accordion .moove-accordion-section-content').slideUp(300).removeClass('open');
		}

		$('.moove-accordion-section-title').click(function(e) {
			// Grab current anchor value
			var currentAttrValue = $(this).attr('href');

			if($(e.target).is('.active')) {
				close_accordion_section();
			}else {
				close_accordion_section();

					// Add active class to section title
					$(this).addClass('active');
					// Open up the hidden content panel
					$('.moove-accordion ' + currentAttrValue).slideDown(200).addClass('open');
				}

				e.preventDefault();
			});

		$('.moove-form-container').on('click', '#post-query-submit', function(e){
			e.preventDefault();
			var page_url = $(this).attr('data-pageurl'),
			selected_date = $('#filter-by-date option:selected').val(),
			selected_post_type = $('#post_types option:selected').val(),
			user_selected = $('#uid option:selected').val(),
			role_selected = $('#user_role option:selected').val(),
			searched = $('#post-search-input').val();
			if ( $('#uid').length > 0 ) {
				var new_url = page_url + '&m=' + selected_date + '&cat=' + selected_post_type + '&uid=' + user_selected + '&user_role=' + role_selected + '&s=' + searched;
			} else {
				var new_url = page_url + '&m=' + selected_date + '&cat=' + selected_post_type + '&s=' + searched;
			}
			if ( new_url ) {
				window.location.replace( new_url );
			}
		});

		// CONFIRM ON DISABLE/ENABLE logging

		$('select.moove-activity-log-settings').on('change', function() {
			if ($(this).val() == '0' && parseInt($(this).attr('data-postcount'))) {
				if (!confirm('Are you sure? \nYou have '+$(this).attr('data-postcount')+' posts, where are log data!')) {
						$(this).val('1'); //set back
						return;
					}
				}
			});

		$('.moove-form-container .all-logs-header').on('click', '#search-submit', function(e){
			e.preventDefault();
			var page_url = $(this).attr('data-pageurl'),
			selected_date = $('#filter-by-date option:selected').val(),
			selected_post_type = $('#post_types option:selected').val(),
			user_selected = $('#uid option:selected').val(),
			role_selected = $('#user_role option:selected').val(),
			searched = $('#post-search-input').val();
			if ( $('#uid').length > 0 ) {
				var new_url = page_url + '&m=' + selected_date + '&cat=' + selected_post_type + '&uid=' + user_selected + '&user_role=' + role_selected + '&s=' + searched;
			} else {
				var new_url = page_url + '&m=' + selected_date + '&cat=' + selected_post_type + '&s=' + searched;
			}
			if ( new_url ) {
				window.location.replace( new_url );
			}
		});

		//CLEAR LOGS BUTTON
		var toggle_log_button = false;
		var clear_type = '';
		$(document).on('click','.moove-activity-log-report .clear-all-logs, #dt-clear-all-logs',function(e){
      if ( ! clear_all_log_started ) {
        e.preventDefault();
        toggle_log_button = $(this);
        clear_type = 'all';
        $('.uat-admin-popup-clear-log-confirm').fadeIn(200);
      }
    });

		$(document).on('click','.moove-activity-log-report .clear-all-logs, #dt-clear-filtered-logs',function(e){
      if ( ! clear_all_log_started ) {
        e.preventDefault();
        toggle_log_button = $(this);
        clear_type = 'filtered';
        $('.uat-admin-popup-clear-filtered-log-confirm').fadeIn(200);
      }
    });


    $(document).on('click','.moove-activity-log-report .clear-log, .buttons-delete-cpt-log',function(e){
      if ( ! clear_all_log_started ) {
        e.preventDefault();
        toggle_log_button = $(this);
        clear_type = 'single';
        $('.uat-admin-popup-clear-log-confirm').fadeIn(200);
      }
    });

    $(document).on('click','.moove-activity-log-report .clear-log-user',function(e){
      if ( ! clear_all_log_started ) {
        e.preventDefault();
        toggle_log_button = $(this);
        clear_type = 'user';
        $('.uat-admin-popup-clear-session-log-confirm').fadeIn(200);
      }
    });

    $(document).on('click','.uat-admin-popup-clear-session-log-confirm .button-primary.clear-session-logs',function(e){
    	e.preventDefault();
      clear_all_log_started = true;
      toggle_log_button.hide();
			var id = '#'+toggle_log_button.parent().closest('table').attr('class')+' tbody',
			link = toggle_log_button.attr('href')+'&clear-session-log='+ toggle_log_button.attr('data-uid'),
			accordion_id = '#'+toggle_log_button.closest('.moove-accordion-section-content').attr('id'),
			$post_title = $('.moove-accordion-section-title[href="' + accordion_id + '"');
			$('.moove-activity-log-report .load-more-container').load(link +' '+id+' tr', function(){
				$('#moove-activity-message-cnt').empty().html('<div id="message" class="error notice notice-error is-dismissible"><p>Activity Logs for <strong>' + $post_title.text() + '</strong> removed.</p></div>');
				$(accordion_id).slideToggle( 100, function(){
					$post_title.hide();
				});				
			});
			clear_all_log_started = false;
			$(this).closest('.uat-admin-popup').fadeOut(200);
    });
    

    $(document).on('click','.uat-admin-popup-clear-log-confirm .button-primary',function(e){
      e.preventDefault();
      clear_all_log_started = true;
      if ( clear_type === 'all' ) {
	      var id = '.'+toggle_log_button.closest('table').attr('class')+' tbody',
				link = toggle_log_button.attr('href');
				link = link ? link + '&clear-all-logs=1' : false;
				if ( link ) {
					$('.moove-activity-log-report .load-more-container').load(link +' '+id+' tr', function(){
						$('#moove-activity-message-cnt').empty().html('<div id="message" class="error notice notice-error is-dismissible"><p>Activity Logs removed.</p></div>');
						toggle_log_button.closest('.moove-form-container').find('table tbody').empty().html('<tr class="no-items"><td class="colspanchange" colspan="7">No posts found.</td></tr>');
						$('#moove-activity-buttons-container').empty();
						$('.moove-activity-log-report .tablenav .displaying-num').hide();
						toggle_log_button.hide();
					});
				}
			} else {
				toggle_log_button.hide();
				var id = '#'+toggle_log_button.parent().closest('table').attr('class')+' tbody',
				link = toggle_log_button.attr('href');
				link = link ? link+'&clear-log='+ toggle_log_button.attr('data-pid') : false,
				accordion_id = "#moove-accordion-" + toggle_log_button.attr('data-pid'),
				$post_title = $('.moove-accordion-section-title[href="' + accordion_id + '"');
				if ( link ) {
					$('.moove-activity-log-report .load-more-container').load(link +' '+id+' tr', function(){
						$('#moove-activity-message-cnt').empty().html('<div id="message" class="error notice notice-error is-dismissible"><p>Activity Logs for <strong>' + $post_title.text() + '</strong> removed.</p></div>');
						$(accordion_id).slideToggle( 100, function(){
							$post_title.hide();
						});
					});
				}
			}
			clear_all_log_started = false;
      $(this).closest('.uat-admin-popup').fadeOut(200);
    });



}); // end document ready



})(jQuery);
