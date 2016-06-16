try {
var input = $('#tempfile-input');
if (!input.length) {
input = $('<input type="file" id="tempfile-input" class="hidden" />').appendTo('body');
}

litepubl.homeuploader.upload(null, input.get(0).files[0]);
        } catch (e) {
return e.message;
}