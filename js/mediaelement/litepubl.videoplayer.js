;(function( $){
$.fn.videoplayer= function(opt) {
return $(this).mediaelementplayer($.extend(opt ? opt : {}, 
{
pluginPath: ltoptions.files + "/js/mediaelement/",
        features: ['playpause','progress','current','volume']
},
ltoptions.lang != "ru" ? {} : {
		playText: 'Воспроизвести',
		pauseText: 'Пауза',
				stopText: 'Остановить',
		muteText: 'Отключение звука',
				fullscreenText: 'Развернуть',
						tracksText: 'Субтитры',
								postrollCloseText: 'Закрыть',
progessHelpText: 'Используйте клавиши влево/вправо  чтобы прокрутить на 1 секунду. Используйте клавиши вверх/вниз чтобы прокрутить на 10 секунд',
allyVolumeControlText: 'Используйте клавиши вверх/вниз чтобы изменить громкость'
}));
};
})( jQuery);