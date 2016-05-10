try {

var input = $('#tempfile-input');
input.addClass('hidden');
var uploader = litepubl.fileman.uploader.handler;
            uploader.queue.push(input.get(0).files[0]);
uploader.start();
        } catch (e) {
return e.message;
}