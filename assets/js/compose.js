(function ($) {
            $.fn.getCursorPosition = function () {
                var input = this.get(0);
                if (!input)
                    return; // No (input) element found
                if ('selectionStart' in input) {
                    // Standard-compliant browsers
                    return input.selectionStart;
                } else if (document.selection) {
                    // IE
                    input.focus();
                    var sel = document.selection.createRange();
                    var selLen = document.selection.createRange().text.length;
                    sel.moveStart('character', -input.value.length);
                    return sel.text.length - selLen;
                }
            }
        })(jQuery);
        
         function insert_field(areaId, text) {
            var txtarea = document.getElementById(areaId);
            var scrollPos = txtarea.scrollTop;
            var strPos = 0;
            var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
                    "ff" : (document.selection ? "ie" : false));
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                strPos = range.text.length;
            } else if (br == "ff")
                strPos = txtarea.selectionStart;

            var front = (txtarea.value).substring(0, strPos);
            var back = (txtarea.value).substring(strPos, txtarea.value.length);
            txtarea.value = front + text + back;
            strPos = strPos + text.length;
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart('character', -txtarea.value.length);
                range.moveStart('character', strPos);
                range.moveEnd('character', 0);
                range.select();
            } else if (br == "ff") {
                txtarea.selectionStart = strPos;
                txtarea.selectionEnd = strPos;
                txtarea.focus();
            }
            txtarea.scrollTop = scrollPos;
        }

        function add_image()
        {

            var image_title = '';
            var image_alt = '';
            var image_custom = '';

            if (document.getElementById('image_title').checked)
            {
                image_title = "title='#--imgtitle--#'";
            }
            if (document.getElementById('image_alt').checked)
            {
                image_alt = "alt='#--imgalt--#'";
            }


            image_custom = document.getElementById("image_custom").value;




            var img_label = document.getElementById("image_label").value;
            var imgtag = "<img src='#--img--" + img_label + "--#' " + image_title + "   " + image_alt + "  " + image_custom + " />";

            var cursor_position = jQuery('#_wp_plugin_define_repeater').getCursorPosition();

            insert_field('_wp_plugin_define_repeater', imgtag)
        }

        function append(insert_type)
        {
            switch (insert_type)
            {
                case 'img':
                    document.getElementById('image-details-open').style.display = 'inherit';
                    break;
                default:
                    var label = prompt("Please Provide a Label for this field.' ", "");
                    if (label != null)
                        insert_field("_wp_plugin_define_repeater", '#--' + insert_type + '--' + label + '--#');

                    break;
            }
        }