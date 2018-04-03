var assetups = {};

var AssetUp = (function() {
	"use strict";

	var defaults = {
		debug: false,
		sortable: {
			animation: 150,
			// handle: ".assetup--handle",
			draggable: ".assetup--asset",
			dragClass: "assetup--assetDragging",
			ghostClass: "assetup--assetGhost",
			chosenClass: "assetup--assetChosen",
			filter: ".assetup--controls, .assetup--remove",
		},
		layout: "grid",
		preview: "image",
		csrfTokenName: "",
		csrfTokenValue: "",
		name: false,
		transform: "",
		enableReorder: true,
		enableRemove: true,
		target: false,
		limit: null,
		maxSize: 0,
		allowedFileExtensions: [],
	};

	var templates = {
		placeholder: '<li class="assetup--placeholder assetup--isLoading"><span class="assetup--placeholderCancel">CANCEL</span><span class="assetup--placeholderProgress"></span><span class="assetup--placeholderError"></span></li>'
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
				["dragover", "dragenter", "dragleave", "drop"].forEach(name => {
					dom.uploader.addEventListener(
						name,
						dropToUploadHandler,
						false
					);
				});
			}
		};

		var initReorderAssets = function() {
			if (settings.enableReorder) {
				Sortable.create(dom.assets, settings.sortable);
			}
		};

		var initRemoveAssets = function() {
			if (settings.enableRemove) {
				dom.uploader.addEventListener(
					"click",
					removeAssetHandler,
					false
				);
			}
		};

		var objToParams = function(obj) {
			if (typeof obj === "string") return obj;
			var encoded = [];
			for (var prop in obj) {
				if (obj.hasOwnProperty(prop)) {
					encoded.push(
						encodeURIComponent(prop) +
							"=" +
							encodeURIComponent(obj[prop])
					);
				}
			}
			return encoded.join("&");
		};

		var htmlToElement = function(html) {
			var span = document.createElement("span");
			span.innerHTML = html.trim();
			return span.firstChild;
		};

		var checkLimit = function() {
			var numberOfUploadedAssets = api.getNumberOfUploadedAssets();
			if (settings.limit && numberOfUploadedAssets >= settings.limit) {
				dom.controls.classList.add("assetup--isHidden");
			} else {
				dom.controls.classList.remove("assetup--isHidden");
			}
		};

		// Event Handlers
		// =========================================================================

		var dropToUploadHandler = function(event) {
			var upload = event.target.closest(".assetup--upload");
			if (!upload) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			switch (event.type) {
				case "dragenter":
				case "dragover":
					upload.classList.add("assetup--isDragging");
					break;

				case "dragleave":
					upload.classList.remove("assetup--isDragging");
					break;

				case "drop":
					upload.classList.remove("assetup--isDragging");
					var assets = event.dataTransfer.files;
					if (!assets) {
						api.setGlobalError("Drop Error");
						return;
					}
					api.uploadAssets(assets);
					break;
			}
		};

		var removeAssetHandler = function(event) {
			var asset = event.target.closest(".assetup--asset");
			if (!asset) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			api.removeAsset(asset);
		};

		var uploadAssetHandler = function(event) {
			if (!event.target.closest(".assetup--upload")) {
				return;
			}
			event.preventDefault();
			dom.input.click();
		};

		var assetInputHandler = function(event) {
			if (!event.target.closest('[name="assetUpAssetInput"]')) {
				return;
			}
			event.preventDefault();
			api.uploadAssets(dom.input.files);
		};

		var cancelUploadHandler = function(event) {
			if (!event.target.closest('.assetup--placeholderCancel')) {
				return;
			}
			event.preventDefault();
			var placeholder = event.target.closest('.assetup--placeholder');
			cancelUpload(placeholder.getAttribute('data-qid'));
		};

		// Public Methods
		// =========================================================================

		var cancelAllUploads = function() {
			for (var qid in queue) {
				cancelUpload(qid);
			}
			queue = {};
			checkLimit();
			setGlobalError(false);
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
			queue[qid].dom.placeholder.classList.remove('assetup--isLoading');
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
			assets = [...assets];

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
						error: placeholder.querySelector('.assetup--placeholderError'),
						progress: placeholder.querySelector('.assetup--placeholderProgress'),
					}
				};
				updateUploadProgress(qid, '0%');
				dom.controls.before(placeholder);

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

				if (xhr.status === 200) {

					if(xhr.response.error) {

						setUploadError(asset.qid, xhr.response.error);

					} else {

						updateUploadProgress(asset.qid, 'Processing Upload');

						var preview = htmlToElement(xhr.response.html);

						if(xhr.response.image) {
							preloadImage(xhr.response.image, function() {
								uploadAssetComplete(asset.qid, preview);
							});
						} else {
							uploadAssetComplete(asset.qid, preview);
						}
					}

				} else {
					api.setGlobalError(xhr.response.error || 'Request Failed');
					cancelAllUploads();
				}
			};

			xhr.responseType = "json";
			xhr.send(getAssetFormData(asset));

			queue[asset.qid].xhr = xhr;
		};

		var uploadAssetComplete = function(qid, preview) {
			dom.assets.replaceChild(preview, queue[qid].dom.placeholder);
			delete queue[qid];
			checkLimit();
		}

		var getAssetFormData = function(asset) {
			var formData = new FormData();
			formData.append("action", "assetup/upload");
			formData.append("assets-upload", asset);
			switch (settings.target.type) {
				case "field":
					formData.append("elementId", settings.target.elementId);
					formData.append("fieldId", settings.target.fieldId);
					break;
				case "folder":
					formData.append("folderId", settings.target.folderId);
					break;
			}
			formData.append(settings.csrfTokenName, settings.csrfTokenValue);
			formData.append("name", settings.name);
			formData.append("preview", settings.preview);
			formData.append("transform", settings.transform);
			formData.append("enableReorder", settings.enableReorder);
			formData.append("enableRemove", settings.enableRemove);
			return formData;
		};

		var preloadImage = function(url, success) {

	        var image = htmlToElement('<img style="display:none !important;" class="assetup--isHidden" src="'+url+'">');
			image.addEventListener('load', function() {
				image.remove();
	        	success();
	        }, false);

	        dom.preload.before(image);
		};

		api.getNumberOfUploadedAssets = function() {
			return dom.assets.childElementCount - 1;
		};

		api.setGlobalError = function(error) {
			error = error || false;
			if (error) {
				dom.errors.textContent = error;
				dom.errors.classList.remove("assetup--isHidden");
			} else {
				dom.errors.textContent = "";
				dom.errors.classList.add("assetup--isHidden");
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
				console.log(
					"[ASSETUP][" + settings.id + "] - Settings",
					settings
				);
			}
			settings.maxSizeMb = (settings.maxSize / (1024*1024)).toFixed(0);

			// Elements
			dom.uploader = document.getElementById(settings.id);
			dom.assets = dom.uploader.querySelector(".assetup--assets");
			dom.controls = dom.uploader.querySelector(".assetup--controls");
			dom.input = dom.uploader.querySelector(
				'[name="assetUpAssetInput"]'
			);
			dom.errors = dom.uploader.querySelector(".assetup--errors");
			dom.preload = dom.uploader.querySelector(".assetup--preload");

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

// ready(function () {

// });
