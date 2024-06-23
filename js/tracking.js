/**
 * @file
 * Tracking video
 */

(function ($, Drupal) {
  Drupal.behaviors.internalVideoBehavior = {
    attach: function (context) {
      const video_wrappers = once('video-tracking', 'div.video-js[id^=internal-video-]', context);
      // Get all internal video trackings
      const internal_video_tracking = drupalSettings.internal_video_tracking;

      $(video_wrappers).each(function () {
        const tracking_id = this.getAttribute('tracking-id');

        if(tracking_id) {
          const video = this.children[0];

          $(this, context).click(function () {
            setTimeout(function () {
              if (!video.paused) {
                Drupal.behaviors.internalVideoBehavior.tracking(tracking_id);
              }
            }, internal_video_tracking['wait_time']);
          });
        }
      });
    },

    /**
     * An AJAX call to POST the data
     */
    tracking: function (tracking_id) {
      const internal_video_tracking = drupalSettings.internal_video_tracking;
      // Get current tracking
      var tracking = internal_video_tracking ? internal_video_tracking[tracking_id] : false;

      if(!tracking) return false;

      // Serialize the array to JSON format.
      tracking = JSON.stringify(tracking);

      // An AJAX call to track
      $.ajax({
        url: '/internal-video/tracking',
        method: 'POST', // Use POST to send data
        dataType: 'json',
        data: {
          tracking: tracking,
        },
        success: function (response) {
          console.log(response);
          if(['not_authorized', 'already_tracked', 'video_is_tracked'].includes(response)) {
            if(Object.hasOwn(internal_video_tracking, tracking_id)) {
              delete internal_video_tracking[tracking_id];
            }
          }
        },
        error: function (error) {
          console.error(error);
        }
      });
    }

  };
})(jQuery, Drupal);
