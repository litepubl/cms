try {
var input = $('#tempfile-input');
litepubl.homeuploader.upload(null, input.get(0).files[0]);
        } catch (e) {
return e.message;
}