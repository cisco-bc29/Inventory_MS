
// DELETE PRODUCT CONFIRMATION

function confirmDelete() {
    return confirm("Are you sure you want to delete this product?");
}


// PRODUCT IMAGE PREVIEW

const imageInput = document.getElementById("image");

if (imageInput) {

    imageInput.addEventListener("change", function() {

        const preview = document.getElementById("preview");
        const previewText = document.getElementById("preview-text");

        const file = this.files[0];

        if (file) {

            if (!file.type.startsWith("image/")) {
                alert("Please select an image.");
                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event) {

                preview.src = event.target.result;
                preview.style.display = "block";
                previewText.style.display = "none";

            };

            reader.readAsDataURL(file);

        } else {

            preview.style.display = "none";
            previewText.style.display = "block";

        }

    });

}
