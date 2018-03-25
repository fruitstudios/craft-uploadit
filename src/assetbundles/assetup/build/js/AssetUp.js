var assetups = {};

var AssetUp = (function () {
	'use strict';

	var defaults = {
		sortable: {
			animation: 150,
			// handle: ".assetup--handle",
			draggable: ".assetup--asset",
			dragClass: "assetup--asset-dragging",
			ghostClass: "assetup--asset-ghost",
			chosenClass: "assetup--asset-chosen",
			filter: ".assetup--controls, .assetup--remove",
			// filter: ".assetup--remove, .assetup--controls",
			onFilter: function (event) {
				// event.item.click();
				// api.removeAsset(event.item);
				// evt.item.parentNode.removeChild(evt.item);
			}
		},
		layout: 'grid',
		preview: 'image',
		csrfTokenName: '',
		csrfTokenValue: '',
		name: false,
		transform: '',
		enableReorder: true,
		enableRemove: true,
		target: false
	};

	var dom = {
		uploader: null,
		assets: null,
		controls: null,
		input: null,
		errors: null,
		progress: null,
		overlay: null, // TODO: Possibly remove when upload progress reworked.
		uploadPercent: null,
	};

	var constructor = function (options) {

	    // Public
	    // =========================================================================

		var api = {};

	    // Private
	    // =========================================================================

		var settings;
		var processing;

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

		var htmlAsElement = function(html) {
			var span = document.createElement('span');
			span.innerHTML = html.trim();
			return span.firstChild;
		}

		var startProcessingUploads = function () {
			dom.uploader.classList.add('assetup--isLoading')
			dom.progress.classList.remove('assetup--isHidden')
			// TODO: POssibly remove when upload progress reworked.
			dom.overlay.classList.remove('assetup--isHidden')
		};

		var stoppedProcessingUploads = function () {
			dom.uploader.classList.remove('assetup--isLoading')
			dom.progress.classList.add('assetup--isHidden')
			// TODO: POssibly remove when upload progress reworked.
			dom.overlay.classList.add('assetup--isHidden')
		};

		var checkLimit = function() {
			var numberOfUploadedAssets = api.getNumberOfUploadedAssets();
			if(settings.limit && numberOfUploadedAssets >= settings.limit) {
				dom.controls.classList.add('assetup--isHidden');
			} else {
				dom.controls.classList.remove('assetup--isHidden');
			}
		}

		// https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/

		var initializeUploadProgress = function (numberOfAssets) {
			startProcessingUploads();
			dom.uploadPercent.textContent = '0';
			processing = {
				uploads: [],
				previews: []
			};
			for(let i = numberOfAssets; i > 0; i--) {
				processing.uploads.push(0);
				processing.previews.push(0);
			}
		}

		var updateUploadProgress = function (i, percent) {
			processing.uploads[i] = percent;
			var total = processing.uploads.reduce((total, current) => total + current, 0) / processing.uploads.length;
			dom.uploadPercent.textContent = total;
			if(total == 100) {
				dom.progress.textContent = 'Processing Uploads...';
			}
		}

		var updatePreviewProgress = function (i, percent) {
			processing.previews[i] = percent;
			var totalProcessed = processing.previews.filter(function(percent) { return percent === 100; });
			dom.progress.textContent = 'Processing ' + (totalProcessed.length + 1) + ' of ' + processing.previews.length;
			if(totalProcessed.length == processing.previews.length) {
				stoppedProcessingUploads();
			}
		}


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

	        var asset = event.target.closest('.assetup--asset');
			if (!asset) {
				return;
        	}
        	event.preventDefault();
        	event.stopPropagation();

        	api.removeAsset(asset);
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

		api.getNumberOfUploadedAssets = function () {
			return (dom.assets.childElementCount - 1);
		}

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

			assets = assets || false;
			if(!assets) {
				return;
			}

			// Check Limit
			if(settings.limit) {
				var numberOfUploadedAssets = api.getNumberOfUploadedAssets();
				var leftOfLimit = settings.limit - numberOfUploadedAssets;
				if(assets.length > leftOfLimit)
				{
					api.setError('You can only upload another ' + leftOfLimit + ' assets');
					return;
				}
			}

	        assets = [...assets];
	        initializeUploadProgress(assets.length);
	        assets.forEach(api.uploadAsset);
		}

	    api.uploadAsset = function (asset, i) {


	    	// TODO: Some Validation

	    	//         var allowedFileTypes = FINDARACEGLOBAL.ASSETS.allowedFileTypes,
	    	//         maxUpload = FINDARACEGLOBAL.ASSETS.maxUpload,
	    	//         maxUploadMB = (maxUpload / (1024*1024)).toFixed(0);

	    	//         if(asset.size > maxUpload)
	    	//         {
	    	//             FINDARACE.forms.addFieldError($field, 'File size cannot exceed ' + maxUploadMB + 'MB');
	    	//             $assetUploadHolder.removeClass('is-loading');
	    	//             return false;
	    	//         }



			var xhr = new XMLHttpRequest();
			var formData = new FormData();
			// formData.append('action', 'assets/save-asset');
			formData.append('action', 'assetup/upload/upload');
			formData.append('assets-upload', asset);
			switch(settings.target.type) {
				case('field'):
					formData.append('elementId', settings.target.elementId);
					formData.append('fieldId', settings.target.fieldId);
					break;
				case('folder'):
					formData.append('folderId', settings.target.folderId);
					break;
			}
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);

			// Update progress
			xhr.upload.onprogress = function(event) {
				updateUploadProgress(i, Math.round(event.loaded / event.total * 100));
			};

			xhr.open('POST', '/', true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Accept', 'application/json');
			// xhr.setRequestHeader('Content-type', asset.type);

			// Setup our listener to process compeleted requests
			xhr.onreadystatechange = function () {

				if (xhr.readyState !== 4) return;

				if (xhr.status === 200) {

					api.setAssetPreview(xhr.response.assetId, i);

				} else {
					// TODO: Error Message
					console.log('The request failed!');
				}

			};

			xhr.responseType = 'json';
			xhr.send(formData);
		};

		api.setAssetPreview = function (id, i) {

			updatePreviewProgress(i, 0);

			// See: https://gist.github.com/sgnl/bd760187214681cdb6dd
			var xhr = new XMLHttpRequest();
			var data = {
				action: 'assetup/upload/asset-preview',
				[settings.csrfTokenName]: settings.csrfTokenValue,
				assetId: id,
				name: settings.name,
				preview: settings.preview,
				transform: settings.transform,
				enableReorder: settings.enableReorder,
				enableRemove: settings.enableRemove,
			};

			xhr.open('POST', '/', true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Accept', 'application/json');
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

			xhr.onreadystatechange = function () {

				if (xhr.readyState !== 4) return;

				if (xhr.status === 200) {

					var preview = htmlAsElement(xhr.response.html);

	                imagesLoaded(preview, function() {
						dom.controls.before(preview); // TODO: Some transition here, fade images in
						updatePreviewProgress(i, 100);
						checkLimit();
	                });

				} else {
					// TODO: Set Errors
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
				checkLimit();
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
			dom.overlay = dom.uploader.querySelector('.assetup--overlay'); // TODO: POssibly remove when upload progress reworked.
			dom.uploadPercent = dom.progress.querySelector('.assetup--uploadPercent');

			// Reorder
			initReorderAssets();
			initDropToUpload();
			initRemoveAssets();

			// Checks
			checkLimit();

			// Input
			dom.uploader.addEventListener('change', assetInputHandler, false);
			dom.uploader.addEventListener('click', uploadAssetHandler, false);

		};

		api.init(options);
		return api;
	};

	return constructor;

})();

// ready(function () {

// });
