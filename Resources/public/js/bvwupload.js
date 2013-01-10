function BVWUpload(element, options) {

  this.$element = $(element);
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

  this.dialog = this.$element.find('.upload-widget-dialog');

  var self = this;

  this.init = function() {

    $('input[type="file"]', this.dialog).fileupload({
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
          self.$element.find('.crop-area').html(img);
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


          self.$element.find('.save-button').attr('disabled', false);
        }

      },
      progressall: function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('.bar', self.$element).css(
          'width',
          progress + '%'
        );
      },
      start: function (e, data) {
        self.showProgress();
      }

    });

    $('.change-button', self.$element).click(function(e) {
      e.preventDefault();
      self.dialog.modal();
    });

    $('.delete-button', self.$element).click(function(e) {
      $('.preview', self.$element).hide();
      $('input[name$="[name]"]', self.$element).val('');
      $('input[name$="[signature]"]', self.$element).val('');
      $('.delete-button', self.$element).hide();
    });

    self.$element.find('.save-button').click(function(e) {
      e.preventDefault();
      self.save();
    })

    self.$element.find('.cancel-button').click(function(e) {
      e.preventDefault();
      if (self.savejXHR) {
        self.savejXHR.abort();
      }
      self.dialog.modal('hide');
    });
  };

  this.showProgress = function() {
    $('.bar', self.$element).css(
      'width','0%'
    );
    $('.progress', self.$element).show();
  }

  this.hideProgress = function () {
    $('.progress', self.$element).hide();
  }

  this.save = function() {
    $('.spinner-area', self.$element).show();
    $('.save-button', self.$element).attr('disabled', 'disabled');
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
        $('.spinner-area', self.$element).hide();
        $('.save-button', self.$element).removeAttr('disabled')
        self.dialog.modal('hide');
        $('img.preview', self.$element).show().attr('src', data.thumbnail_url);
        $('input[name$="[name]"]', self.$element).val(data.name);
        $('input[name$="[signature]"]', self.$element).val(data.signature);
        $('.delete-button', self.$element).css('display', 'inline-block');
      },
      "json"
    ).fail(function(data) {
        $('.spinner-area', self.$element).hide();
        $('.save-button', self.$element).removeAttr('disabled')
      });
  };



}



