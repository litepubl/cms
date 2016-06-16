var selector  = '#tempfile-input';
var input = $(selector );
if (input.length) {
input.removeClass('hidden');
} else {
input = $('<input type="file" id="tempfile-input" />').appendTo('body');
}

return selector ;