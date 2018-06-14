(function($) {

	if (typeof wp === 'undefined' || !wp.hasOwnProperty('media')) return;

  var $imageHolder = $('#lpImageHolder');
  var $imageInput = $('#lpImage');
  var $lpMetaBox = $('#lpMetaBox');
  var $lpSettingsPage = $('#lpSettingsPage');
	var $uploadedFileName = $('#lpUploadedFileName');

  var customUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Open Graph Image',
      button: {
        text: 'Set As Open Graph Image'
      },
      multiple: true
    });
  var thumbURL;
  var attachment;

  customUploader.on('select', function(e) {
    attachment = customUploader.state().get('selection').first().toJSON();
    thumbURL = attachment.sizes['medium'] == undefined ? attachment.url : attachment.sizes['medium'].url;

    if($lpMetaBox) {
      $lpMetaBox.removeClass('has-no-image');
    }

    if($lpSettingsPage) {
      $lpSettingsPage.removeClass('has-no-image');
    }

    $imageHolder.css('background-image', 'url(' + thumbURL + ')');
    $uploadedFileName.html(attachment.url.split('/').reverse()[0]);
console.log( attachment);
    //-- Set input values.
    $imageInput.val(attachment.id);
  });

  $('#lpImageSelectButton').on('click', function(e){
    customUploader.open();
    return true;
  });

  $('#ogRemoveImage').on('click', function() {
    if($lpMetaBox) {
      $lpMetaBox.addClass('has-no-image');
    }
    if($lpSettingsPage) {
      $lpSettingsPage.addClass('has-no-image');
    }
    $uploadedFileName.html('');
    $imageHolder.css('background-image', 'url("")');
    $imageInput.val('');
  });

  $('#ogForceAll').on('change', function () {
  	if($(this).is(':checked')) {
  		$lpSettingsPage.addClass('is-forcing-all');
  	} else {
  		$lpSettingsPage.removeClass('is-forcing-all');
  	}
  });
})(jQuery);
