var AssetUp = (function () {
	'use strict';

	var defaults = {};

	var constructor = function (options) {

		var api = {};
		var settings;

		var log = function (value) {
			console.log('AssetUp', value);
		};

		api.init = function (options) {

			settings = extend(defaults, options || {});

			log(settings);
			log(window);

		};

		api.init(options);
		return api;
	};

	return constructor;

})();

// (function ( FINDARACE ) {
//     "use strict";

//     // TODO: *** This works but does not handle errors well.
//     //     : It would be best to handle this in one hit, needs reworking.
//     var allowMultipleUploads = false;

//     var init = function () {

//         FINDARACE.DOM.$body.on('click', '.js-assetUpload', function(event) {
//             event.preventDefault();
//             var $trigger = $(this);
//             $trigger.next('input[name="assetFileInput"]').trigger('click');
//         });

//         FINDARACE.DOM.$body.on('change', 'input[name="assetFileInput"]', saveAssets);

//         FINDARACE.DOM.$body.on('click', '.js-assetFieldDelete', deleteAsset);

//         initAssetFields();
//         initAssetDrop();
//     };

//     var initAssetFields = function(context) {

//         var assetFields = context ? context.querySelectorAll('.js-assetFieldAssets') : document.querySelectorAll('.js-assetFieldAssets');

//         assetFields.forEach(function (assetFieldAssets, index) {

//             var sortable = new Sortable(assetFieldAssets, {
//     	        handle: ".js-assetFieldReOrderHandle",
//                 filter: ".hidden",
//             });
//         });

//     };

//     var initAssetDrop = function() {

//         FINDARACE.DOM.$body.on('dragover', '.js-assetUpload', function(event) {
//             event.preventDefault();
//             event.stopPropagation();
//             $(this).addClass('is-dragging');
//         });

//         FINDARACE.DOM.$body.on('dragleave', '.js-assetUpload', function(event) {
//             event.preventDefault();
//             event.stopPropagation();
//             $(this).removeClass('is-dragging');
//         });

//         FINDARACE.DOM.$body.on('drop', '.js-assetUpload', function(event) {
//             event.preventDefault();
//             event.stopPropagation();
//             var droppedFiles = event.originalEvent.dataTransfer.files,
//                 $fileInput = $(this).closest('.js-assetUploadHolder').find('input[name=assetFileInput]');

//             if(allowMultipleUploads) {

//                 if(droppedFiles.length >= 0)
//                 {
//                     for (var i = 0; i < droppedFiles.length; i++) {
//                         saveAsset(event, $fileInput, droppedFiles[i]);
//                     }
//                 }
//                 else
//                 {
//                     $.flash({
//                         class: 'error',
//                         message: 'Sorry, doesn\'t look like you have added any files.',
//                         position: 'top'
//                     });
//                 }

//             } else {

//                 if(droppedFiles.length === 1)
//                 {
//                     saveAsset(event, $fileInput, droppedFiles[0]);
//                 }
//                 else
//                 {
//                     $.flash({
//                         class: 'error',
//                         message: 'Sorry, you can\'t upload multiple files',
//                         position: 'top'
//                     });
//                 }

//             }

//             $(this).removeClass('is-dragging');
//         });

//     };

//     var deleteAsset = function(event) {

//         event.preventDefault();

//         var $trigger = $(this),
//             $thisAssetFieldPreview = $trigger.closest('.js-assetFieldPreview'),
//             $assetField = $thisAssetFieldPreview.closest('.js-assetField'),
//             $allAssetFieldPreviews = $assetField.find('.js-assetFieldPreview'),
//             limit = $.dataOrDefault($assetField, 'limit', false);

//         if(limit && limit > ($allAssetFieldPreviews.length - 1) ) {
//             $assetField.find('.js-assetUploadHolder').removeClass('is-hidden');
//         }

//         $thisAssetFieldPreview.remove();
//     };

//     var saveAssets = function(event) {

//         event.preventDefault();

//         var $fileInput = $(this);

//         if(allowMultipleUploads) {

//             if($fileInput[0].files.length >= 0)
//             {
//                 for (var i = 0; i < $fileInput[0].files.length; i++) {
//                     // TODO: SEE ABOVE ***
//                     saveAsset(event, $fileInput, $fileInput[0].files[i]);
//                 }
//             }
//             else
//             {
//                 $.flash({
//                     class: 'error',
//                     message: 'Sorry, doesn\'t look like you have added any files.',
//                     position: 'top'
//                 });
//             }

//         } else {

//             if($fileInput[0].files.length === 1)
//             {
//                 saveAsset(event, $fileInput, $fileInput[0].files[0]);
//             }
//             else
//             {
//                 $.flash({
//                     class: 'error',
//                     message: 'Sorry, you can\'t upload multiple files',
//                     position: 'top'
//                 });
//             }

//         }

//     };

//     var saveAsset = function(event, $fileInput, asset) {

//         asset = asset ? asset : $fileInput[0].files[0];

//         var $field = $fileInput.closest('.js-field'),
//         $assetField = $fileInput.closest('.js-assetField'),
//         $assetUploadHolder = $assetField.find('.js-assetUploadHolder'),
//         $assetFieldAssets = $assetField.find('.js-assetFieldAssets'),
//         $assetFieldPreviewTemplate = $assetField.find('.js-assetFieldPreviewTemplate'),
//         name = $.dataOrDefault($assetField, 'name', '');

//         FINDARACE.forms.clearFieldError($field);
//         $assetUploadHolder.addClass('is-loading');

//         var data = new FormData();
//         data.append('action', 'findarace/assets/saveAsset');
//         data.append('asset', asset);
//         data.append('folder', $fileInput.data('folder'));
//         data.append('fieldId', $fileInput.data('field-id'));
//         data.append('source', $fileInput.data('source'));
//         data.append('elementId', $fileInput.data('element-id'));
//         data.append('transform', $fileInput.data('transform'));
//         data.append(FINDARACEGLOBAL.CSRF.tokenName, FINDARACEGLOBAL.CSRF.token);

//         // Pre Upload Checks
//         var allowedFileTypes = FINDARACEGLOBAL.ASSETS.allowedFileTypes,
//         maxUpload = FINDARACEGLOBAL.ASSETS.maxUpload,
//         maxUploadMB = (maxUpload / (1024*1024)).toFixed(0);

//         if(asset.size > maxUpload)
//         {
//             FINDARACE.forms.addFieldError($field, 'File size cannot exceed ' + maxUploadMB + 'MB');
//             $assetUploadHolder.removeClass('is-loading');
//             return false;
//         }

//         $.ajax({
//             method: 'POST',
//             url: '/',
//             data: data,
//             cache: false,
//             dataType: 'json',
//             processData: false,
//             contentType: false
//         })
//         .done( function(response) {

//             if (response.success) {

//                 var $newAssetFieldPreviewFromTemplate = $assetFieldPreviewTemplate.clone();
//                 $newAssetFieldPreviewFromTemplate.append('<input type="hidden" name="' + name + '" value="' + response.asset.id + '" />');

//                 var $thisAssetFieldPreview = $newAssetFieldPreviewFromTemplate.insertBefore($assetFieldPreviewTemplate),
//                     $thisAssetPreview = $thisAssetFieldPreview.find('.js-assetPreview');

//                 $thisAssetFieldPreview.toggleClass('js-assetFieldPreviewTemplate js-assetFieldPreview');

//                 var preLoaded = false;
//                 switch ($thisAssetPreview.data('view')) {

//                     case 'background':
//                         preLoadImage(response.asset.url, function() {

//                             $thisAssetPreview.css({ backgroundImage: 'url(' + response.asset.url + ')' });

//                             $assetUploadHolder.removeClass('is-loading');
//                             $thisAssetFieldPreview.removeClass('is-hidden');

//                         });
//                         break;

//                     case 'image':
//                         preLoadImage(response.asset.url, function() {

//                             $thisAssetPreview.find('img').attr('src', response.asset.url);

//                             $assetUploadHolder.removeClass('is-loading');
//                             $thisAssetFieldPreview.removeClass('is-hidden');

//                         });
//                         break;

//                     case 'file':

//                         $thisAssetPreview.find('.js-assetFilename').text(response.asset.filename);

//                         $assetUploadHolder.removeClass('is-loading');
//                         $thisAssetFieldPreview.removeClass('is-hidden');

//                         break;
//                 }

//                 var limit = $.dataOrDefault($assetField, 'limit', false);
//                 if(limit && $assetField.find('.js-assetFieldPreview').length >= limit) {
//                     $assetField.find('.js-assetUploadHolder').addClass('is-hidden');
//                 }

//             } else {

//                 $assetUploadHolder.removeClass('is-loading');

//                 if(response.error)
//                 {
//                     FINDARACE.forms.addFieldError($field, response.error);
//                 }
//                 else
//                 {
//                     $.flash({
//                         class: 'error',
//                         message: response.message ? response.message : 'Upload Failed',
//                         position: 'top',
//                         duration: 2500
//                     });
//                 }
//             }

//             $fileInput.val('');
//             return response.success;

//         });

//     };

//     var preLoadImage = function(url, success, error) {
//         var $img = $('<img style="display:none !important;">');
//         $img.attr('src', url);
//         $img.appendTo('body');
//         $img.on('load', function(){
//             $img.remove();
//             success();
//         }).on('error', function(){
//             $img.remove();
//             error();
//         });
//     };

//     FINDARACE.assets = {
//         init: init,
//         preLoadImage: preLoadImage,
//     };
// })( window.FINDARACE );
