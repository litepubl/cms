/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */


(function(window) {
  window.lang = window.lang || {};
  window.lang.mediaplayer = {
    playText: 'Воспроизвести',
    pauseText: 'Пауза',
    stopText: 'Остановить',
    muteText: 'Отключение звука',
    fullscreenText: 'Развернуть',
    tracksText: 'Субтитры',
    postrollCloseText: 'Закрыть',
    progessHelpText: 'Используйте клавиши влево/вправо  чтобы прокрутить на 1 секунду. Используйте клавиши вверх/вниз чтобы прокрутить на 10 секунд',
    allyVolumeControlText: 'Используйте клавиши вверх/вниз чтобы изменить громкость'
  };

})(window);

;
(function(exports, undefined) {
  "use strict";

  if (typeof exports.ru === 'undefined') {
    exports.ru = {
      "None": "Отсутствует",
      "Unmute": "Включить звук",
      "Fullscreen": "Во весь экран",
      "Download File": "Скачать файл",
      "Mute Toggle": "Переключить звук",
      "Play/Pause": "Воспроизвести/Пауза",
      "Captions/Subtitles": "названия/Субтитры",
      "Download Video": "Скачать видео",
      "Mute": "Без звука",
      "Turn off Fullscreen": "Вернуться от полного экрана",
      "Go Fullscreen": "Во весь экран",
      "Close": "Закрыть"
    };
  }

}(mejs.i18n.locale.strings))