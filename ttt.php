<form>
	<input type="text" id="search-text-input" value="" style="padding: 5px; margin: 0;"/>
	<button id="search-btn" style="padding:3px 8px;">
		Найти
	</button>
</form>

<div id="search-results"></div>

<script type="text/javascript">
    jQuery('#search-btn').on('click', function () {
        jQuery('#search-btn').html('<img src="/loader.gif" style="width: 18px; height: 18px;"/> Ищем...');
        jQuery('#search-results').html('');
        jQuery.post(
            '/csv.php',
	        {
	            q: jQuery('#search-text-input').val()
	        },
	        function (data) {
                jQuery('#search-btn').html('Найти');
                jQuery('#search-results').html(data.content);
	        },
	        'json'
        );

        return false;
    });
</script>