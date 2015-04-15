/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $(document).ready(function() {

//set selected
if ("idcat" in ltoptions) $("#id-jumpto-forum").val(ltoptions.idcat);

$("#id-jump-to-button").click(function() {
var url = $("#id-jumpto-forum option:selected").data("url");
window.location = ltoptions.url + url;
return false;
});
    //only logged users
var iduser = get_cookie("litepubl_user_id");
    if (iduser) {
if ((iduser == 1) || (iduser == $("#forum-edit-link").data("author"))) $("#forum-edit-link").show();
}
  });
  
})( jQuery );