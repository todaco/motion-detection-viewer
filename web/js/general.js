var MotionDetectionViewer = {};

/**
 * Initialize event listeners.
 */
MotionDetectionViewer.init = function() {
  MotionDetectionViewer.addUnveilImagesListener();
};

/**
 * Add unveil images listener.
 */
MotionDetectionViewer.addUnveilImagesListener = function() {
  $("#thumbnails img").unveil(800);
};

$(document).ready(function() {
  MotionDetectionViewer.init();
});
