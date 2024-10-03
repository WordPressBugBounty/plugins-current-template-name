/**
 * Current Template Name Admin JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
	'use strict';

	$.ctn_obj = {
        ajax_url: '',
		init: function () {

            //style inputs as color picker
            $('.ctn_highlighter_color').wpColorPicker();
            $('.ctn_bg_color').wpColorPicker();
            $('.ctn_text_color').wpColorPicker();
        },
	};

	$.ctn_obj.init();
});
