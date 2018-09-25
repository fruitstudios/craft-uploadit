var uploadits = {};

var UploaditAssets = (function() {
	"use strict";

	var defaults = {
		type: false,
		debug: false,
		sortable: {
			animation: 150,
			// handle: ".uploadit--handle",
			draggable: ".uploadit--asset",
			dragClass: "uploadit--assetDragging",
			ghostClass: "uploadit--assetGhost",
			chosenClass: "uploadit--assetChosen",
			filter: ".uploadit--controls, .uploadit--remove",
		},
		layout: "grid",
		view: "auto",
		csrfTokenName: "",
		csrfTokenValue: "",
		type: false,
		name: false,
		transform: "",
		enableReorder: true,
		enableRemove: true,
		target: false,
		saveOnUpload: false,
		limit: null,
		maxSize: 0,
		allowedFileExtensions: [],
		enableDropToUpload: true,
	};

	var templates = {
		// placeholder: '<li class="uploadit--placeholder uploadit--isLoading"><span class="uploadit--placeholderCancel">Cancel</span><span class="uploadit--placeholderProgress"></span><span class="uploadit--placeholderError"></span></li>'
		placeholder: '<li class="uploadit--placeholder uploadit--isLoading"><span class="uploadit--placeholderCancel"><a class="uploadit--remove"><svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><path d="M32.202 27.36L58.62.944c1.226-1.225 3.212-1.225 4.437 0s1.225 3.21 0 4.437L36.64 31.8l26.417 26.418c1.225 1.225 1.225 3.212 0 4.437s-3.21 1.225-4.437 0l-26.418-26.42L5.784 62.653c-1.225 1.225-3.212 1.225-4.437 0s-1.225-3.212 0-4.437l26.418-26.418L1.347 5.38C.122 4.154.122 2.168 1.347.943s3.212-1.225 4.437 0L32.202 27.36z"/></svg></a></span><span class="uploadit--placeholderProgress"></span><span class="uploadit--placeholderError"></span></li>'
	};

	var constructor = function(options) {

		// Public
		// =========================================================================

		var api = {};

		// Private
		// =========================================================================

		var settings;
		var queue = {};
		var dom = {
			uploader: null,
			assets: null,
			controls: null,
			input: null,
			preload: null,
			errors: null,
		};

		// Private Methods
		// =========================================================================

		var initDropToUpload = function() {
			if (settings.enableDropToUpload) {
				dom.uploader.addEventListener('dragover', dropToUploadHandler, false);
				dom.uploader.addEventListener('dragenter', dropToUploadHandler, false);
				dom.uploader.addEventListener('dragleave', dropToUploadHandler, false);
				dom.uploader.addEventListener('drop', dropToUploadHandler, false);
			}
		};

		var initReorderAssets = function() {
			if (settings.enableReorder) {
				Sortable.create(dom.assets, settings.sortable);
			}
		};

		var initRemoveAssets = function() {
			if (settings.enableRemove) {
				dom.uploader.addEventListener("click", removeAssetHandler, false);
			}
		};

		var objToParams = function(obj) {
			if (typeof obj === "string") return obj;
			var encoded = [];
			for (var prop in obj) {
				if (obj.hasOwnProperty(prop)) {
					encoded.push(encodeURIComponent(prop) + "=" + encodeURIComponent(obj[prop]));
				}
			}
			return encoded.join("&");
		};

		var htmlToElement = function(html) {
			var span = document.createElement("span");
			span.innerHTML = html.trim();
			return span.firstChild;
		};

		var ensureResponseIsJson = function(response) {
			return typeof response === 'string' ? JSON.parse(response) : response;
		};

		var checkLimit = function() {
			var numberOfUploadedAssets = api.getNumberOfUploadedAssets();
			if (settings.limit && numberOfUploadedAssets >= settings.limit) {
				dom.controls.classList.add("uploadit--isHidden");
			} else {
				dom.controls.classList.remove("uploadit--isHidden");
			}
		};

		// Event Handlers
		// =========================================================================

		var dropToUploadHandler = function(event) {
			var upload = event.target.closest(".uploadit--uploader");
			if (!upload) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			switch (event.type) {
				case "dragenter":
				case "dragover":
					upload.classList.add("uploadit--isDragging");
					break;

				case "dragleave":
					upload.classList.remove("uploadit--isDragging");
					break;

				case "drop":
					upload.classList.remove("uploadit--isDragging");
					var assets = event.dataTransfer.files;
					if (!assets.length) {
						api.setGlobalError("Drop Error");
						return;
					}
					api.uploadAssets(assets);
					break;
			}
		};

		var removeAssetHandler = function(event) {

			if (!event.target.closest(".uploadit--remove")) {
				return;
			}

			var asset = event.target.closest(".uploadit--asset");
			if (!asset) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			api.removeAsset(asset);
		};

		var uploadAssetHandler = function(event) {
			if (!event.target.closest(".uploadit--upload")) {
				return;
			}
			event.preventDefault();
			dom.input.value = null;
			dom.input.click();
		};

		var assetInputHandler = function(event) {
			if (!event.target.closest('[name="uploaditAssetInput"]')) {
				return;
			}
			event.preventDefault();
			api.uploadAssets(dom.input.files);
		};

		var cancelUploadHandler = function(event) {
			if (!event.target.closest('.uploadit--placeholderCancel')) {
				return;
			}
			event.preventDefault();
			var placeholder = event.target.closest('.uploadit--placeholder');
			cancelUpload(placeholder.getAttribute('data-qid'));
		};

		// Public Methods
		// =========================================================================

		var cancelAllUploads = function(error) {
			for (var qid in queue) {
				cancelUpload(qid);
			}
			queue = {};
			checkLimit();
			api.setGlobalError(error || false);
		};

		var cancelUpload = function(qid) {
			if (queue.hasOwnProperty(qid)) {
				if(queue[qid].xhr) {
					queue[qid].xhr.abort();
				}
				queue[qid].dom.placeholder.remove();
				delete queue[qid];
				checkLimit();
			}
		};

		var updateUploadProgress = function(qid, progress) {
			queue[qid].dom.progress.textContent = progress;
		};

		var setUploadError = function(qid, error) {
			queue[qid].dom.placeholder.classList.remove('uploadit--isLoading');
			queue[qid].dom.error.textContent = error;
		};

		api.uploadAssets = function(assets) {

			// Clear global errors
			api.setGlobalError('');

			// Guard
			assets = assets || false;
			if (!assets) {
				return;
			}

			// Limit
			if (settings.limit) {
				var numberOfUploadedAssets = api.getNumberOfUploadedAssets();
				var leftOfLimit = settings.limit - numberOfUploadedAssets;
				if (assets.length > leftOfLimit) {
					switch(leftOfLimit) {
						case(0):
							api.setGlobalError('You can\'t upload any more assets');
							break;
						case(1):
							api.setGlobalError('You can only upload another 1 asset');
							break;
						default:
							api.setGlobalError('You can only upload another ' + leftOfLimit + ' assets');
							break;
					}
					return;
				}
			}

			// Assets
			assets = Array.from(assets);

			// Queue & Placeholder
			assets.forEach(function(asset, i) {

				var qid = 'asset' + Math.random().toString(36).substr(2, 5);
				asset.qid = qid;

				var placeholder = htmlToElement(templates.placeholder);
				placeholder.setAttribute('data-qid', qid);

				queue[qid] = {
					asset: asset,
					xhr: null,
					dom: {
						placeholder: placeholder,
						error: placeholder.querySelector('.uploadit--placeholderError'),
						progress: placeholder.querySelector('.uploadit--placeholderProgress'),
					}
				};
				updateUploadProgress(qid, '0%');
				dom.assets.appendChild(placeholder);
			});
			checkLimit();

			// Upload
			assets.forEach(api.uploadAsset);

		};

		api.uploadAsset = function(asset) {

			// Validate Size
			if(asset.size > settings.maxSize) {
				setUploadError(asset.qid, 'File size cannot exceed ' + settings.maxSizeMb + 'MB');
				updateUploadProgress(asset.qid, '');
				return;
			}

			// Validate Type
			var extension = asset.name.split('.').pop();
			if(settings.allowedFileExtensions.indexOf(extension) === -1) {
				setUploadError(asset.qid, 'Invalid file type');
				updateUploadProgress(asset.qid, '');
				return;
			}

			// Request
			var xhr = new XMLHttpRequest();

			// Progress
			xhr.upload.onprogress = function(event) {
				var progress = Math.round(event.loaded / event.total * 100);
				updateUploadProgress(asset.qid, progress + '%');
			};

			xhr.open("POST", "/", true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("Accept", "application/json");

			// Setup our listener to process compeleted requests
			xhr.onreadystatechange = function() {
				if (xhr.readyState !== 4) return;

				var response = ensureResponseIsJson(xhr.response);

				if (xhr.status === 200) {

					if(response.error) {

						setUploadError(asset.qid, response.error);

					} else {

						updateUploadProgress(asset.qid, 'Processing Upload');

						var preview = htmlToElement(response.html);

						if(response.image) {
							preloadImage(response.image, function() {
								uploadAssetComplete(asset.qid, preview);
							});
						} else {
							uploadAssetComplete(asset.qid, preview);
						}
					}

				} else {
					if(xhr.status != 0 && response) {
						cancelAllUploads(response.error || 'Request Failed');
					}
				}
			};

			xhr.responseType = "json";
			xhr.send(getAssetFormData(asset));

			queue[asset.qid].xhr = xhr;
		};

		api.canUploadAssets = function(success) {

			var xhr = new XMLHttpRequest();
			xhr.open("POST", "/", true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("Accept", "application/json");

			xhr.onreadystatechange = function() {
				if (xhr.readyState !== 4) return;
				if (xhr.status === 200) {

					var response = ensureResponseIsJson(xhr.response);

					if(response.success) {
						success();
					} else {
						cancelAllUploads(response.error || 'Request Failed');
					}

				} else {
					cancelAllUploads(response.error || 'Request Failed');
				}
			};

			xhr.responseType = "json";
			var formData = new FormData();
			formData.append("action", "uploadit/upload/can-upload");
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			xhr.send(formData);
		};

		var uploadAssetComplete = function(qid, preview) {
			dom.assets.replaceChild(preview, queue[qid].dom.placeholder);
			delete queue[qid];
			checkLimit();
		}

		var getAssetFormData = function(asset) {
			var formData = new FormData();
			formData.append("action", "uploadit/upload");
			formData.append("assets-upload", asset);
			switch (settings.type) {
				case "field":
					formData.append("elementId", settings.target.elementId);
					formData.append("fieldId", settings.target.fieldId);
					formData.append("saveOnUpload", settings.saveOnUpload ? 1 : 0);
					formData.append('assetIds', api.getCurrentAssetIds());
					break;
				case "volume":
					formData.append("folderId", settings.target.folderId);
					break;
			}
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			formData.append("name", settings.name);
			formData.append("view", settings.view);
			formData.append("transform", settings.transform);
			formData.append("enableReorder", settings.enableReorder ? 1 : 0);
			formData.append("enableRemove", settings.enableRemove ? 1 : 0);
			return formData;
		};

		var preloadImage = function(url, success) {

	        var image = htmlToElement('<img style="display:none !important;" class="uploadit--isHidden" src="'+url+'">');
			image.addEventListener('load', function() {
				image.remove();
	        	success();
	        }, false);

	        dom.preload.before(image);
		};

		api.getNumberOfUploadedAssets = function() {
			return dom.assets.childElementCount;
		};

		api.getCurrentAssetIds = function() {
			var assetIds = [];
			if(settings.name)
			{
				var inputs = dom.uploader.querySelectorAll('[name="' + settings.name +'"]');
		        inputs.forEach(function (input, index) {
		        	if(input.value !== '') {
		        		assetIds.push(input.value);
		        	}
		        });
			}
			return assetIds;
		}

		api.setGlobalError = function(error) {
			error = error || false;
			if (error) {
				dom.errors.textContent = error;
				dom.errors.classList.remove("uploadit--isHidden");
			} else {
				dom.errors.textContent = "";
				dom.errors.classList.add("uploadit--isHidden");
			}
		};

		api.removeAsset = function(asset) {

			asset = asset || null;
			if (asset) {
				asset.remove();
				checkLimit();
			}
		};

		api.init = function(options) {
			// Prep Settings
			settings = extend(defaults, options || {});
			if (settings.debug) {
				console.log("[ASSETUP][ASSETS][" + settings.id + "]", settings);
			}
			settings.maxSizeMb = (settings.maxSize / (1024*1024)).toFixed(0);

			// Elements
			dom.uploader = document.getElementById(settings.id);
			dom.assets = dom.uploader.querySelector(".uploadit--assets");
			dom.controls = dom.uploader.querySelector(".uploadit--controls");
			dom.input = dom.uploader.querySelector('[name="uploaditAssetInput"]');
			dom.errors = dom.uploader.querySelector(".uploadit--errors");
			dom.preload = dom.uploader.querySelector(".uploadit--preload");

			// Reorder
			initReorderAssets();
			initDropToUpload();
			initRemoveAssets();

			// Checks
			checkLimit();

			// Input
			dom.uploader.addEventListener("change", assetInputHandler, false);
			dom.uploader.addEventListener("click", uploadAssetHandler, false);
			dom.uploader.addEventListener("click", cancelUploadHandler, false);
		};

		api.init(options);
		return api;
	};

	return constructor;
})();

var UploaditUserPhoto = (function() {
	"use strict";

	var defaults = {
		type: false,
		debug: false,
		csrfTokenName: "",
		csrfTokenValue: "",
		type: false,
		transform: "",
		enableRemove: true,
		target: false,
		limit: 1,
		maxSize: 0,
		allowedFileExtensions: [],
		enableDropToUpload: true,
	};

	var constructor = function(options) {

		// Public
		// =========================================================================

		var api = {};

		// Private
		// =========================================================================

		var settings;
		var dom = {
			uploader: null,
			photo: null,
			photoImage: null,
			defaultPhoto: null,
			controls: null,
			input: null,
			preload: null,
			errors: null,
		};

		// Private Methods
		// =========================================================================

		var initDropToUpload = function() {
			if (settings.enableDropToUpload) {
				dom.uploader.addEventListener('dragover', dropToUploadHandler, false);
				dom.uploader.addEventListener('dragenter', dropToUploadHandler, false);
				dom.uploader.addEventListener('dragleave', dropToUploadHandler, false);
				dom.uploader.addEventListener('drop', dropToUploadHandler, false);
			}
		};

		var initRemovePhoto = function() {
			if (settings.enableRemove) {
				dom.uploader.addEventListener("click", removePhotoHandler, false);
			}
		};

		var objToParams = function(obj) {
			if (typeof obj === "string") return obj;
			var encoded = [];
			for (var prop in obj) {
				if (obj.hasOwnProperty(prop)) {
					encoded.push(encodeURIComponent(prop) + "=" + encodeURIComponent(obj[prop]));
				}
			}
			return encoded.join("&");
		};

		var htmlToElement = function(html) {
			var span = document.createElement("span");
			span.innerHTML = html.trim();
			return span.firstChild;
		};

		var ensureResponseIsJson = function(response) {
			return typeof response === 'string' ? JSON.parse(response) : response;
		};

		// Event Handlers
		// =========================================================================

		var dropToUploadHandler = function(event) {

			var upload = event.target.closest(".uploadit--userPhotoUploader");
			if (!upload) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			switch (event.type) {
				case "dragenter":
				case "dragover":
					upload.classList.add("uploadit--isDragging");
					break;

				case "dragleave":
					upload.classList.remove("uploadit--isDragging");
					break;

				case "drop":
					upload.classList.remove("uploadit--isDragging");
					var photo = event.dataTransfer.files;
					if (!photo.length) {
						api.setGlobalError("Drop Error");
						return;
					}
					api.uploadPhoto(photo);
					break;
			}
		};

		var removePhotoHandler = function(event) {

			if (!event.target.closest(".uploadit--remove")) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			api.removePhoto();
		};

		var uploadPhotoHandler = function(event) {
			if (!event.target.closest(".uploadit--upload")) {
				return;
			}
			event.preventDefault();
			dom.input.value = null;
			dom.input.click();
		};

		var photoInputHandler = function(event) {
			if (!event.target.closest('[name="uploaditUserPhotoInput"]')) {
				return;
			}
			event.preventDefault();

			api.uploadPhoto(dom.input.files);
		};

		// Public Methods
		// =========================================================================

		api.uploadPhoto = function(photo) {

			// Clear global errors
			api.setGlobalError('');
			dom.uploader.classList.add('uploadit--isLoading');

			// Guard
			photo = photo[0] || false;
			if (!photo) {
				return;
			}

			// Validate Size
			if(photo.size > settings.maxSize) {
				api.setGlobalError('File size cannot exceed ' + settings.maxSizeMb + 'MB');
				return;
			}

			// Validate Type
			var extension = photo.name.split('.').pop();
			if(settings.allowedFileExtensions.indexOf(extension) === -1) {
				api.setGlobalError('Invalid file type');
				return;
			}

			// Request
			var xhr = new XMLHttpRequest();

			xhr.open("POST", "/", true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("Accept", "application/json");

			// Setup our listener to process compeleted requests
			xhr.onreadystatechange = function() {
				if (xhr.readyState !== 4) return;

				var response = ensureResponseIsJson(xhr.response);

				if (xhr.status === 200) {

					if(response.error) {

						api.setGlobalError(response.error);

					} else {

						preloadImage(response.photo, function() {

							api.setPhoto(response.photo);

						});
					}

				} else {
					api.setGlobalError(response.error || 'Request Failed');
				}

				dom.uploader.classList.remove('uploadit--isLoading');
			};

			xhr.responseType = "json";

			var formData = new FormData();
			formData.append("action", "uploadit/upload/user-photo");
			formData.append("photo", photo);
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			formData.append("transform", settings.transform);
			xhr.send(formData);
		};

		var preloadImage = function(url, success) {

	        var image = htmlToElement('<img style="display:none !important;" class="uploadit--isHidden" src="'+url+'">');
			image.addEventListener('load', function() {
				image.remove();
	        	success();
	        }, false);

	        dom.preload.before(image);
		};


		api.setGlobalError = function(error) {
			error = error || false;
			if (error) {
				dom.errors.textContent = error;
				dom.errors.classList.remove("uploadit--isHidden");
			} else {
				dom.errors.textContent = "";
				dom.errors.classList.add("uploadit--isHidden");
			}
			dom.uploader.classList.remove('uploadit--isLoading');
		};

		api.setPhoto = function(url) {

			dom.photoImage.src = url;
			dom.photo.classList.remove("uploadit--isHidden");
			dom.defaultPhoto.classList.add("uploadit--isHidden");

		};

		api.removePhoto = function() {

			// Clear global errors
			api.setGlobalError('');
			dom.uploader.classList.add('uploadit--isLoading');

			// Request
			var xhr = new XMLHttpRequest();

			xhr.open("POST", "/", true);
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("Accept", "application/json");

			// Setup our listener to process compeleted requests
			xhr.onreadystatechange = function() {
				if (xhr.readyState !== 4) return;

				if (xhr.status === 200) {

					var response = ensureResponseIsJson(xhr.response);

					if(response.error) {
						api.setGlobalError(response.error);
					} else {
						dom.photo.classList.add("uploadit--isHidden");
						dom.photoImage.src = '';
						dom.defaultPhoto.classList.remove("uploadit--isHidden");
					}

				} else {
					api.setGlobalError(response.error || 'Request Failed');
				}

				dom.uploader.classList.remove('uploadit--isLoading');
			};

			xhr.responseType = "json";

			var formData = new FormData();
			formData.append("action", "uploadit/upload/delete-user-photo");
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			xhr.send(formData);
		};

		api.init = function(options) {
			// Prep Settings
			settings = extend(defaults, options || {});
			if (settings.debug) {
				console.log("[ASSETUP][USERPHOTO][" + settings.id + "]", settings);
			}
			settings.maxSizeMb = (settings.maxSize / (1024*1024)).toFixed(0);

			// Elements
			dom.uploader = document.getElementById(settings.id);
			dom.photo = dom.uploader.querySelector(".uploadit--userPhoto");
			dom.photoImage = dom.uploader.querySelector(".uploadit--userPhotoImage");
			dom.defaultPhoto = dom.uploader.querySelector(".uploadit--defaultUserPhotoWrapper");
			dom.input = dom.uploader.querySelector('[name="uploaditUserPhotoInput"]');
			dom.errors = dom.uploader.querySelector(".uploadit--errors");
			dom.preload = dom.uploader.querySelector(".uploadit--preload");

			// Reorder
			initDropToUpload();
			initRemovePhoto();

			// Input
			dom.uploader.addEventListener("change", photoInputHandler, false);
			dom.uploader.addEventListener("click", uploadPhotoHandler, false);
		};

		api.init(options);
		return api;
	};

	return constructor;
})();


