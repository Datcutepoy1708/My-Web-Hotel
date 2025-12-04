// Star Rating
const stars = document.querySelectorAll("#starRating i");
const ratingInput = document.getElementById("ratingInput");

stars.forEach((star) => {
  star.addEventListener("click", function () {
    const rating = this.getAttribute("data-rating");
    ratingInput.value = rating;

    stars.forEach((s) => {
      s.classList.remove("fas", "active");
      s.classList.add("far");
    });

    for (let i = 0; i < rating; i++) {
      stars[i].classList.remove("far");
      stars[i].classList.add("fas", "active");
    }
  });

  star.addEventListener("mouseenter", function () {
    const rating = this.getAttribute("data-rating");
    for (let i = 0; i < rating; i++) {
      if (!stars[i].classList.contains("active")) {
        stars[i].classList.remove("far");
        stars[i].classList.add("fas");
      }
    }
  });

  star.addEventListener("mouseleave", function () {
    const currentRating = ratingInput.value;
    stars.forEach((s, index) => {
      if (index >= currentRating) {
        if (!s.classList.contains("active")) {
          s.classList.remove("fas");
          s.classList.add("far");
        }
      }
    });
  });
});

// Image Preview
function previewImages(event) {
  const preview = document.getElementById("imagePreview");
  preview.innerHTML = "";
  const files = event.target.files;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const reader = new FileReader();

    reader.onload = function (e) {
      const div = document.createElement("div");
      div.className = "preview-item";
      div.innerHTML = `
                        <img src="${e.target.result}">
                        <button type="button" class="remove-btn" onclick="removePreview(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
      preview.appendChild(div);
    };

    reader.readAsDataURL(file);
  }
}

function removePreview(btn) {
  btn.parentElement.remove();
}

// Open Image Modal
function openImageModal(src) {
  document.getElementById("modalImage").src = src;
  new bootstrap.Modal(document.getElementById("imageModal")).show();
}

// Load More Reviews
function loadMoreReviews() {
  // Implementation for loading more reviews
  alert("Tính năng đang được phát triển");
}
