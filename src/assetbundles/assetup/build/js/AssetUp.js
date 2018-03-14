var AssetUp = (function () {
	'use strict';

	var defaults = {
		sortable: {
			animation: 150,
			handle: ".assetup--handle",
			draggable: ".assetup--asset",
		}
	};

	var dom = {
		uploader: null,
		assets: null,
		controls: null,
		input: null,
		errors: null,
		progress: null,
	};

	var constructor = function (options) {

	    // Public
	    // =========================================================================

		var api = {};

	    // Private
	    // =========================================================================

		var settings;

	    // Private Methods
	    // =========================================================================

	    var initDropToUpload = function() {
	    	if(settings.enableDropToUpload) {
	    		['dragover', 'dragenter', 'dragleave', 'drop'].forEach(name => {
					dom.uploader.addEventListener(name, dropToUploadHandler, false);
				});
	    	}
	    }

	   	var initReorderAssets = function() {
	    	if(settings.enableReorder) {
	    		Sortable.create(dom.assets, settings.sortable);
	    	}
	    }

	    var initRemoveAssets = function() {
	    	if(settings.enableRemove) {
				dom.uploader.addEventListener('click', removeAssetHandler, false);
	    	}
	    }

		var setElementLoading = function (element, loading) {
			element = element || false;
			loading = typeof(loading) === 'boolean' ? loading : true;

			if(loading) {
				element.classList.add('assetup--isLoading')
			} else {
				element.classList.remove('assetup--isLoading')
			}
		};

		var objToParams = function (obj) {
			if (typeof (obj) === 'string') return obj;
			var encoded = [];
			for (var prop in obj) {
				if (obj.hasOwnProperty(prop)) {
					encoded.push(encodeURIComponent(prop) + '=' + encodeURIComponent(obj[prop]));
				}
			}
			return encoded.join('&');
		};

	    // Event Handlers
	    // =========================================================================

		var dropToUploadHandler = function (event) {

			var upload = event.target.closest('.assetup--upload');
			if(!upload) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			switch (event.type) {

				case 'dragenter':
				case 'dragover':
					upload.classList.add('assetup--isDragging');
					break;

				case 'dragleave':
					upload.classList.remove('assetup--isDragging');
					break;

				case 'drop':
					upload.classList.remove('assetup--isDragging');
	                var assets = event.dataTransfer.files;
	                if(!assets) {
	                	api.setError('Drop Error');
	                	return;
	                }
	                api.uploadAssets(assets)
					break;
			}

		};

		var removeAssetHandler = function (event) {

			if (!event.target.classList.contains('assetup--remove')) {
				return;
        	}
        	event.preventDefault();

	        var asset = event.target.closest('.assetup--asset');
	        if (asset) {
        		api.removeAsset(asset);
	        }

		};

		var uploadAssetHandler = function (event) {

			if (!event.target.closest('.assetup--upload')) {
				return;
        	}
        	event.preventDefault();
        	dom.input.click();
		};

		var assetInputHandler = function (event) {

			if (!event.target.closest('[name="assetUpAssetInput"]')) {
				return;
        	}
        	event.preventDefault();
        	api.uploadAssets(dom.input.files);
		};

	    // Public Methods
	    // =========================================================================

	    api.log = function (key, data) {
			console.log('[AssetUp][#'+settings.id+']['+key+']', data);
		};

		api.setError = function (error) {
			error = error || false;
			if(error) {
				dom.errors.textContent = error;
				dom.errors.classList.remove('assetup--isHidden');
			} else {
				dom.errors.textContent = '';
				dom.errors.classList.add('assetup--isHidden');
			}
		};

		api.uploadAssets = function (assets) {
			api.log('api.uploadAssets()', assets);

			assets = assets || false;
			if(!assets) {
				return;
			}

	        assets = [...assets];
	        //   initializeProgress(assets.length);
	        assets.forEach(api.uploadAsset);
	        //   assets.forEach(previewFile);
		}

	    api.uploadAsset = function (asset, i) {

			// setElementLoading(controls, true);

	        //
	        // Will need:
	        // folderId or fieldId
	        // elementId - need this with fieldId requests TODO: work out if needed.


			var xhr = new XMLHttpRequest();
			var formData = new FormData();
			formData.append('action', 'assets/save-asset');
			if(settings.fieldId) {
				formData.append('fieldId', settings.field); // TODO: This need to be properly hoekd up with the new model
			} else {
				formData.append('folderId', 6);
			}
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			formData.append('assets-upload', asset);

			xhr.open('POST', '/', true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Accept', 'application/json');

			// Update progress (can be used to show progress indicator)
			// xhr.upload.addEventListener("progress", function(e) {
			// 	updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
			// })

			// Setup our listener to process compeleted requests
			xhr.onreadystatechange = function () {

				if (xhr.readyState !== 4) return;

				if (xhr.status === 200) {

					console.log(xhr.response);
					api.setAssetPreview(xhr.response.assetId);

					// This is now done in the setAssetPreview method
					// updateProgress(i, 100) // <- Add this

				} else {
					// Fail
					console.log('The request failed!');
				}

				// Always
				// console.log('This always runs...');
			};

			xhr.responseType = 'json';
			xhr.send(formData);

			// https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/

	        // let uploadProgress = []
	        // let progressBar = document.getElementById('progress-bar')

	        // function initializeProgress(numFiles) {
	        //   progressBar.value = 0
	        //   uploadProgress = []

	        //   for(let i = numFiles; i > 0; i--) {
	        //     uploadProgress.push(0)
	        //   }
	        // }

	        // function updateProgress(fileNumber, percent) {
	        //   uploadProgress[fileNumber] = percent
	        //   let total = uploadProgress.reduce((tot, curr) => tot + curr, 0) / uploadProgress.length
	        //   console.debug('update', fileNumber, percent, total)
	        //   progressBar.value = total
	        // }

	        // function handleFiles(files) {
	        //   files = [...files]
	        //   initializeProgress(files.length)
	        //   files.forEach(uploadFile)
	        //   files.forEach(previewFile)
	        // }

	        // function previewFile(file) {
	        //   let reader = new FileReader()
	        //   reader.readAsDataURL(file)
	        //   reader.onloadend = function() {
	        //     let img = document.createElement('img')
	        //     img.src = reader.result
	        //     document.getElementById('gallery').appendChild(img)
	        //   }
	        // }

	        // function uploadFile(file, i) {
	        //   var url = 'https://api.cloudinary.com/v1_1/joezim007/image/upload'
	        //   var xhr = new XMLHttpRequest()
	        //   var formData = new FormData()
	        //   xhr.open('POST', url, true)
	        //   xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')

	        //   // Update progress (can be used to show progress indicator)
	        //   xhr.upload.addEventListener("progress", function(e) {
	        //     updateProgress(i, (e.loaded * 100.0 / e.total) || 100)
	        //   })

	        //   xhr.addEventListener('readystatechange', function(e) {
	        //     if (xhr.readyState == 4 && xhr.status == 200) {
	        //       updateProgress(i, 100) // <- Add this
	        //     }
	        //     else if (xhr.readyState == 4 && xhr.status != 200) {
	        //       // Error. Inform the user
	        //     }
	        //   })

	        //   formData.append('upload_preset', 'ujpu6gyk')
	        //   formData.append('file', file)
	        //   xhr.send(formData)
	        // }







		 //                var imageLoader = imagesLoaded(elem, function() {
		 //                    app.slider.init(elem);
		 //                    app.spinner.stop(elem);
		 //                });

		 //                imageLoader.on( 'progress', function( instance, image ) {
		 //                    var result = image.isLoaded ? 'loaded' : 'broken';
		 //                    console.api.log( 'image is ' + result + ' for ' + image.img.src );
		 //                });
		 //            }







		};

		api.setAssetPreview = function (id) {

			// See: https://gist.github.com/sgnl/bd760187214681cdb6dd

			var xhr = new XMLHttpRequest();
			var data = {
				action: 'assetup/upload/asset-preview',
				[settings.csrfTokenName]: settings.csrfTokenValue,
				assetId: id,
				view: 'image',
				transfrom: '',
				allowReorder: true,
				allowRemove: true,
			};

			xhr.open('POST', '/', true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Accept', 'application/json');
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

			xhr.onreadystatechange = function () {

				if (xhr.readyState !== 4) return;

				if (xhr.status === 200) {

					dom.assets.innerHTML += xhr.response.html;

					// This is where  we should set progress to complete
					// updateProgress(i, 100) // <- Add this

				} else {
					// Fail
					console.log('Could not get preview!');
				}
			};

  			xhr.responseType = 'json';
			xhr.send(objToParams(data));
		}

		api.removeAsset = function (asset) {
			asset = asset || null;
			if(asset) {
				asset.remove();
			}
		}

		api.init = function (options) {

			// Prep Settings
			settings = extend(defaults, options || {});
			api.log('init()', settings);

			// Elements
			dom.uploader = document.getElementById(settings.id);
			dom.assets = dom.uploader.querySelector('.assetup--assets');
			dom.controls = dom.uploader.querySelector('.assetup--controls');
			dom.input = dom.uploader.querySelector('[name="assetUpAssetInput"]');
			dom.errors = dom.uploader.querySelector('.assetup--errors');
			dom.progress = dom.uploader.querySelector('.assetup--progress');

			// Reorder
			initReorderAssets();
			initDropToUpload();
			initRemoveAssets();

			// Input
			dom.uploader.addEventListener('change', assetInputHandler, false);
			dom.uploader.addEventListener('click', uploadAssetHandler, false);

		};

		api.init(options);
		return api;
	};

	return constructor;

})();


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
