(function($){
	var log_id = 0;

	$(document).ready(function() {
		if ( 'undefined' !== typeof moove_frontend_activity_scripts && 'undefined' !== typeof moove_frontend_activity_scripts.log_enabled && 'undefined' !== typeof moove_frontend_activity_scripts.log_enabled.post_id ) {
			$.post(
				moove_frontend_activity_scripts.ajaxurl,
				{
					action: "moove_activity_track_pageview",
					post_id: moove_frontend_activity_scripts.post_id,
					is_single: moove_frontend_activity_scripts.is_single,
					is_page: moove_frontend_activity_scripts.is_page,
					user_id: moove_frontend_activity_scripts.current_user,
					referrer: moove_frontend_activity_scripts.referrer,
					extras: moove_frontend_activity_scripts.extras,
					is_archive: moove_frontend_activity_scripts.is_archive,
					is_front_page: moove_frontend_activity_scripts.is_front_page,
					is_home: moove_frontend_activity_scripts.is_home,
					archive_title: moove_frontend_activity_scripts.archive_title,
					request_url: window.location.href,
				},
				function( msg ) {
					try	{
						var response = msg ? JSON.parse(msg) : false;
						if ( typeof response === 'object' && response.id ) {
							log_id = response.id;
							try {
								var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
								if ( typeof moove_frontend_activity_scripts.extras !== 'undefined' ) {
									var extras_obj = JSON.parse( moove_frontend_activity_scripts.extras );
									if ( typeof extras_obj.ts_status !== 'undefined' && extras_obj.ts_status === '1' ) {
										window.addEventListener('beforeunload', function(event) {
											if ( typeof log_id !== 'undefined' ) {
												if ( 'function' === typeof navigator.sendBeacon ) {
													var log_data = new FormData();
													log_data.append('action', 'moove_activity_track_unload');
													log_data.append('log_id', log_id);
													navigator.sendBeacon( moove_frontend_activity_scripts.ajaxurl, log_data );
												} else {
													$.post(
														moove_frontend_activity_scripts.ajaxurl,
														{
															action: "moove_activity_track_unload",
															log_id: log_id,													
														},
														function( msg ) {
															console.warn(msg);
														}
													);
												}											
											}
										});
									}
								}
							} catch(e) {
								console.error(e);
							}
						}
					} catch(e){
						console.error(e);
					}
				}
			);
		}
	});

})(jQuery);

