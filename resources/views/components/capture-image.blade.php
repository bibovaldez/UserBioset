<form id="imageForm" action="{{ route('image.upload') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="text-center w-full max-w-md mx-auto">
            <h1 class="text-4xl font-bold mt-3 mb-1">BioseT v1.0</h1>
            <div id="wrapper" class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex flex-col space-y-4">
                    <button id="cameraButton" type="button" class="bg-gray-200 p-4 rounded-md">
                        <div
                            class="border-4 border-dashed border-red-600 rounded-md p-6 flex items-center justify-center">
                            <p class="text-gray-600">Capture Image</p>
                        </div>
                    </button>
                    <div>
                        <p class="text-gray-600">OR</p>
                    </div>
                    <label for="file"
                        class="block w-full text-center bg-red-500 text-white font-bold py-2 px-4 rounded-md cursor-pointer hover:bg-red-600">
                        Choose File
                    </label>
                    <input type="file" name="image" id="file" class="hidden" accept="image/*">
                    <div id="cameraModal"
                        class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden">
                        <div class="bg-white p-4 rounded-lg shadow-lg max-w-xl w-full mx-4">
                            <video id="video" autoplay
                                class="rounded-md w-full h-auto max-h-[70vh] object-contain"></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            <div class="mt-4 flex flex-col sm:flex-row justify-between gap-2">
                                <button type="button" id="closeCamera"
                                    class="bg-red-600 text-white px-4 py-2 rounded-md w-full sm:w-auto">Close</button>
                                <button type="button" id="captureImage"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md w-full sm:w-auto">Capture</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="imagePreviewContainer"
            class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden z-50">
            <div class="bg-white p-4 rounded-lg shadow-lg relative max-w-xl w-full mx-4">
                <button type="button" id="cancelImage"
                    class="absolute top-2 right-2 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img id="preview" class="mx-auto rounded-md w-full max-w-xs md:max-w-md lg:max-w-lg" />
                <div class="flex justify-between mt-4">
                    <button id="retakeButton" type="button"
                        class="bg-yellow-600 text-white px-4 py-2 hidden rounded-md">Capture</button>
                    <button id="rechooseButton" type="button"
                        class="bg-blue-600 text-white px-4 py-2 hidden rounded-md">Change</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md">Upload</button>
                </div>
            </div>
        </div>
        <section id="progress-area" class="w-full max-w-md mx-auto"></section>
        <section id="uploaded-area" class="scroll-smooth w-full max-w-md mx-auto"></section>
    </div>
</form>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const elements = {
            form: document.getElementById("imageForm"),
            video: document.getElementById("video"),
            canvas: document.getElementById("canvas"),
            cameraModal: document.getElementById("cameraModal"),
            preview: document.getElementById("preview"),
            imagePreviewContainer: document.getElementById("imagePreviewContainer"),
            fileInput: document.getElementById("file"),
            retakeButton: document.getElementById("retakeButton"),
            rechooseButton: document.getElementById("rechooseButton"),
            progressArea: document.getElementById("progress-area"),
            uploadedArea: document.getElementById("uploaded-area"),
            cameraButton: document.getElementById("cameraButton"),
            wrapper: document.getElementById("wrapper"),
            closeCamera: document.getElementById("closeCamera"),
            captureImage: document.getElementById("captureImage"),
            cancelImage: document.getElementById("cancelImage"),
        };
        const context = elements.canvas.getContext("2d");
        let userClicked = false;

        function toggleCamera(action) {
            if (action === "start") {
                elements.imagePreviewContainer.classList.add("hidden");
                userClicked = true;
                navigator.mediaDevices
                    .getUserMedia({
                        video: true,
                    })
                    .then((stream) => {
                        elements.video.srcObject = stream;
                        elements.cameraModal.classList.remove("hidden");
                    })
                    .catch(() => {
                        showAlert(
                            "Error",
                            "Camera cannot be accessed, please allow access to the camera",
                            "error"
                        );
                    });
            } else if (action === "stop") {
                const stream = elements.video.srcObject;
                const tracks = stream.getTracks();
                tracks.forEach((track) => track.stop());
                elements.video.srcObject = null;
                elements.cameraModal.classList.add("hidden");
                userClicked = false;
            }
        }

        function captureImage() {
            elements.canvas.width = elements.video.videoWidth;
            elements.canvas.height = elements.video.videoHeight;
            context.drawImage(
                elements.video,
                0,
                0,
                elements.canvas.width,
                elements.canvas.height
            );

            elements.canvas.toBlob(function(blob) {
                const file = new File([blob], "captured_image.png", {
                    type: "image/png",
                });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                elements.fileInput.files = dataTransfer.files;
                elements.preview.src = URL.createObjectURL(file);
                showImagePreview();
                toggleCamera("stop");
            }, "image/png");
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    elements.preview.src = reader.result;
                    showImagePreview();
                };
                reader.readAsDataURL(file);
                elements.rechooseButton.classList.remove("hidden");
            }
        }

        function showImagePreview() {
            if (userClicked) {
                elements.retakeButton.classList.remove("hidden");
            }
            elements.imagePreviewContainer.classList.remove("hidden");
        }

        function cancelImage() {
            elements.imagePreviewContainer.classList.add("hidden");
            elements.fileInput.value = "";
            elements.preview.src = "";
            elements.retakeButton.classList.add("hidden");
            elements.rechooseButton.classList.add("hidden");
            userClicked = false;
        }

        elements.form.addEventListener("submit", function(event) {
            event.preventDefault();
            const formData = new FormData(elements.form);
            const imageSize = formData.get("image").size;
            const sizeInKB = imageSize / 1024;
            const sizeInMB = sizeInKB / 1024;
            let fileSize =
                sizeInMB >= 1 ?
                `${sizeInMB.toFixed(2)} MB` :
                `${sizeInKB.toFixed(2)} KB`;

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "/image/upload", true);
            xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

            xhr.upload.addEventListener("progress", updateProgress);

            // Define the event listener for ready state change
            xhr.onreadystatechange = function() {
                setUIState(false);
                if (xhr.readyState === 4) {
                    elements.progressArea.innerHTML = ""; // Clear progress area
                    setUIState(false);

                    let icon, text, color;
                    if (xhr.status === 201) {
                        icon = "check";
                        text = "Image Uploaded";
                        color = "green";
                    } else if (xhr.status === 413) {
                        icon = "exclamation-triangle";
                        text = "File Too Large";
                        color = "yellow";
                    } else {
                        icon = "times";
                        text = "Upload Failed";
                        color = "red";
                    }

                    const html = `
            <li class="list-none column-2 bg-white p-3 rounded-lg">
                <div class="content flex flex-col justify-center">
                    <div class="details flex justify-between items-center">
                        <div class="details ml-4 flex flex-col">
                            <span class="name"><i class="fas fa-image text-${color}-500 text-xl"></i> ${text}</span>
                            <span class="size">${fileSize}</span>
                        </div>
                        <i class="fas fa-${icon} text-${color}-500"></i>
                    </div>
                </div>
            </li>`;

                    const alert = {
                        title: xhr.status === 201 ?
                            "Success" : xhr.status === 413 ?
                            "Warning" : "Failed",
                        text: xhr.status === 201 ?
                            "Image uploaded successfully" : xhr.status === 413 ?
                            "The file is too large, please try again" :
                            "Image upload failed, please try again",
                        icon: xhr.status === 201 ?
                            "success" : xhr.status === 413 ?
                            "warning" : "error",
                    };
                    elements.uploadedArea.insertAdjacentHTML("afterbegin", html);
                    showAlert(alert.title, alert.text, alert.icon);
                }
            };
            xhr.onerror = function() {
                elements.progressArea.innerHTML = ""; // Clear progress area
                setUIState(false);
                showAlert(
                    "Failed",
                    "Image upload failed, please try again",
                    "error"
                );
            };
            xhr.send(formData);
            cancelImage();
        });

        function updateProgress(event) {
            setUIState(true);
            const {
                loaded,
                total
            } = event;
            const fileLoaded = Math.floor((loaded / total) * 100);
            elements.progressArea.innerHTML = `
                <li class="list-none column-2 bg-white p-3 rounded-lg">
                    <div class="content flex flex-col justify-center">
                        <div class="details flex justify-between items-center">
                            <span class="name"><i class="fas fa-image text-blue-500 text-xl"></i> Image Uploading ${fileLoaded}%</span>
                        </div>
                        <div class="progress-bar bg-gray-300 rounded-full h-2 mt-2">
                            <div class="progress bg-blue-500 h-full rounded-full" style="width: ${fileLoaded}%"></div>
                        </div>
                    </div>
                </li>`;
        }

        function showAlert(title, text, icon) {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: "OK",
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
        }

        function setUIState(isUploading) {
            elements.cameraButton.disabled = isUploading;
            elements.fileInput.disabled = isUploading;
            elements.wrapper.classList.toggle("opacity-50", isUploading);
        }

        // Event Listeners
        elements.cameraButton.addEventListener("click", () =>
            toggleCamera("start")
        );
        elements.closeCamera.addEventListener("click", () => toggleCamera("stop"));
        elements.captureImage.addEventListener("click", captureImage);
        elements.fileInput.addEventListener("change", previewImage);
        elements.retakeButton.addEventListener("click", () =>
            toggleCamera("start")
        );
        elements.rechooseButton.addEventListener("click", () =>
            elements.fileInput.click()
        );
        elements.cancelImage.addEventListener("click", cancelImage);
    });
</script>
<style>
    #progress-area .content .progress-bar {
        height: 6px;
        width: 100%;
        margin-bottom: 4px;
        background: #fff;
        border-radius: 30px;
    }

    .content #progress-bar #progress {
        height: 100%;
        width: 0%;
        background: #6990f2;
        border-radius: inherit;
    }

    #uploaded-area.onprogress {
        max-height: 150px;
    }

    #uploaded-area .row .content {
        display: flex;
        align-items: center;
    }

    #uploaded-area .row .details {
        display: flex;
        margin-left: 15px;
        flex-direction: column;
    }

    #uploaded-area .row .details .size {
        color: #404040;
        font-size: 11px;
    }

    #uploaded-area i.fa-check {
        font-size: 16px;
    }
</style>
