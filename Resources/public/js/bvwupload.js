function BVWUpload(element, options) {


  this.coordinates = {};
  this.token = '';
  this.filename = '';

  this.thumbnailFormat = options.thumbnailFormat;
  this.targetFormat = options.targetFormat;
  this.storeUrl = options.storeUrl;
  this.cropAreaWidth = options.cropAreaWidth;
  this.cropAreaHeight = options.cropAreaHeight;

  this.aspectRatio = false;
  if (options.aspectRatio) {
    this.aspectRatio = options.aspectRatio;
  }

  var $element = $(element);
  var $modal = $element.find('.upload-widget-dialog');

  var self = this;

  this.init = function() {

    $('input[type="file"]', $modal).fileupload({
      dataType: 'json',
      fail: function(e, data) {
        self.hideProgress();
        alert("upload failed, please retry");
      },
      done: function (e, data) {
        self.hideProgress();
        if (data.result.error) {
          alert(data.result.error)
        } else {
          file = data.result.file;
          self.token = file.token;
          self.filename = file.filename;
          var img = $('<img />').attr('src', file.url);
          $modal.find('.crop-area').html(img);
          img.Jcrop({
            onSelect: function(c) {
              self.coordinates = c;
            },
            trueSize: [ file.width, file.height ],
            aspectRatio: self.aspectRatio,
            boxWidth: self.cropAreaWidth,
            boxHeight: self.cropAreaHeight,

            // set initial selection if we have a fixed aspect ratio
            setSelect: self.aspectRatio ?  [0, 0, self.cropAreaWidth, self.cropAreaHeight ] : null
          });


          $modal.find('.save-button').attr('disabled', false);
        }

      },
      progressall: function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('.bar', $modal).css(
          'width',
          progress + '%'
        );
      },
      start: function (e, data) {
        self.showProgress();
      }

    });

    $('.choose-image', $element).click(function(e) {
      e.preventDefault();
      $modal.modal();
    });

    $('.delete', $element).click(function(e) {
      e.preventDefault();
      e.stopPropagation();
      self.setData('', '', '');
    });

    $modal.find('.save-button').click(function(e) {
      e.preventDefault();
      self.save();
    })

    $modal.find('.cancel-button').click(function(e) {
      e.preventDefault();
      if (self.savejXHR) {
        self.savejXHR.abort();
      }
      $modal.modal('hide');
    });
  };

  this.setData = function(name, signature, src) {
    $('img.preview', $element).attr('src', src);
    $('input[name$="[signature]"]', $element).val(signature);
    $('input[name$="[name]"]', $element).val(name).change();
    self.refreshView();
  }

  this.refreshView = function() {
    if($('input[name$="[name]"]', $element).val()) {
      $('img.preview', $element).closest('.preview_container').show();
      $('.delete-button', $element).css('display', 'inline-block');
      $('.btn.choose-image', $element).hide();
    } else {
      $('img.preview', $element).closest('.preview_container').hide();
      $('.delete-button', $element).hide();
      $('.btn.choose-image', $element).show();
    }
  }


  this.showProgress = function() {
    $('.bar', $modal).css(
      'width','0%'
    );
    $('.progress', $modal).show();
  }

  this.hideProgress = function () {
    $('.progress', $modal).hide();
  }

  this.save = function() {
    $('.spinner-area', $modal).show();
    $('.save-button', $modal).attr('disabled', 'disabled');
    self.savejXHR = $.post(self.storeUrl,
      {
        form :
        {
          token: self.token,
          format: self.targetFormat,
          x : self.coordinates.x, y: self.coordinates.y,
          w: self.coordinates.w, h: self.coordinates.h,
          filename: self.filename,
          return_thumbnail: self.thumbnailFormat
        }
      },
      function(data) {
        $('.spinner-area', $modal).hide();
        $('.save-button', $modal).removeAttr('disabled')
        $modal.modal('hide');
        self.setData(data.name, data.signature, data.thumbnail_url);
        $element.trigger('imagechanged',[{ data: data.name, signature: data.signature, url: data.default_url, thumbnail_url: data.thumbnail_url } ]);
      },
      "json"
    ).fail(function(data) {
        $('.spinner-area', $modal).hide();
        $('.save-button', $modal).removeAttr('disabled')
      });
  };



}




(function($){

    $.fn.extend({

        bvwupload: function(action, params) {


          return this.each(function() {


              var bvwUpload = $(this).data('bvwUpload');
              if (!bvwUpload) {

                var aspectRatio = $(this).attr('data-aspect-ratio');
                if (aspectRatio == 0 || aspectRatio == 'false') {
                  aspectRatio = false
                } else {
                  aspectRatio = parseFloat(aspectRatio);
                }

                bvwUpload = new BVWUpload(this,
                {
                    storeUrl : $(this).attr('data-store-url'),
                    thumbnailFormat : $(this).attr('data-thumbnail-format'),
                    targetFormat: $(this).attr('data-target-format'),
                    cropAreaWidth: $(this).attr('data-crop-area-width'),
                    cropAreaHeight: $(this).attr('data-crop-area-height'),
                    aspectRatio: aspectRatio
                });

                $(this).data('bvwUpload', bvwUpload);
                bvwUpload.init();
              }

              if (action && action == 'refresh') {
                bvwUpload.refreshView();
              }
              if (typeof action == 'object') {
                var params = action;
                if (params.name && params.signature && params.src) {
                  bvwUpload.setData(params.name, params.signature, params.src);
                }
              }




          });

        }
    });
})(jQuery);
