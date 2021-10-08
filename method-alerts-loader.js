var internalAlerts = [];

var ts = Math.round((new Date()).getTime() / 1000);
var currentTime = ts;
ts = (ts-(ts%1000))/1000;

jQuery.ajax({
    url: ( data_object.site_url + '/wp-content/uploads/method-alerts/data.json?ts=' + ts ),
    dataType: 'json',
    cache: true,
})
.done(function( data ) {
    jQuery.each( data , function( i, item ) {
		if ( item.targets.length > 0 ) {
			jQuery.each( item.targets, function( i, id ) {
				if ( id == data_object.postid ) {
					if ( item.schedule.status == 'off' ) {
    					pushInternal( item.attr.headline, item.attr.content, item.attr.theme );
    				} else {
		    			if ( item.schedule.start < currentTime && item.schedule.end > currentTime  ) {
		    				pushInternal( item.attr.headline, item.attr.content, item.attr.theme );
		    			}
		    		}
    			}
    		})
    	}
	});
    pushToPage();
})
.fail(function (a, b, c) {
    console.log('The script encountered problems loading the alerts file.');
});

function pushInternal( headline, content, theme ) {
	internalAlerts.push( '<div class="alert alert-' + theme + '" role="alert"><h4 class="alert-heading">' + headline + '</h4>' + content + '</div>' );
}

function pushToPage() {
	if ( internalAlerts.length > 0 ) {
		jQuery.each( internalAlerts, function( i, item ) {
			jQuery( ".method-alerts-container" ).append( item );
			jQuery( "body" ).addClass( "has-method-alert" );
		})
	}
}
