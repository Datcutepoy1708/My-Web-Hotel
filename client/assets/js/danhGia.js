const reviews = document.querySelectorAll(".review");
const showMoreBtn = document.getElementById("showMore");
let visibleCount = 3; // số review hiển thị ban đầu

// Ẩn các review sau 3 cái đầu
reviews.forEach((r, i) => {
  if (i >= visibleCount) r.classList.add("hidden");
});

// Khi bấm nút thì hiện thêm
showMoreBtn.addEventListener("click", () => {
  visibleCount += 3;
  reviews.forEach((r, i) => {
    if (i < visibleCount) r.classList.remove("hidden");
  });
  if (visibleCount >= reviews.length) {
    showMoreBtn.style.display = "none"; // ẩn nút nếu hiện hết
  }
});
// chọn số sao khi viết đánh giá
const stars = document.querySelectorAll("#starInput span");
let selectedRating = 0;

stars.forEach((star) => {
  star.addEventListener("click", () => {
    const value = parseInt(star.getAttribute("data-value"));
    selectedRating = value;
    document.getElementById("ratingInput").value = value;
    
    stars.forEach((s, i) => {
      if (i < value) {
        s.classList.add("active");
      } else {
        s.classList.remove("active");
      }
    });
  });
});

// Preview nhiều ảnh review
function previewReviewImages(input) {
  const preview = document.getElementById("reviewImagesPreview");
  preview.innerHTML = "";
  
  if (input.files && input.files.length > 0) {
    const maxFiles = Math.min(input.files.length, 6);
    
    for (let i = 0; i < maxFiles; i++) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement("div");
        div.className = "position-relative";
        div.style.cssText = "width: 120px; height: 120px;";
        
        const img = document.createElement("img");
        img.src = e.target.result;
        img.style.cssText = "width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;";
        
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-danger btn-sm position-absolute top-0 end-0 m-1";
        btn.innerHTML = "×";
        btn.onclick = function() {
          div.remove();
          // Xóa file khỏi input
          const dt = new DataTransfer();
          const files = Array.from(input.files);
          files.splice(i, 1);
          files.forEach(file => dt.items.add(file));
          input.files = dt.files;
        };
        
        div.appendChild(img);
        div.appendChild(btn);
        preview.appendChild(div);
      };
      reader.readAsDataURL(input.files[i]);
    }
  }
}

// Xử lý submit form review
document.getElementById("reviewForm")?.addEventListener("submit", function(e) {
  const rating = document.getElementById("ratingInput").value;
  const comment = document.getElementById("commentInput").value.trim();
  
  if (rating === "0" || rating === "") {
    e.preventDefault();
    alert("Vui lòng chọn số sao đánh giá!");
    return false;
  }
  
  if (!comment) {
    e.preventDefault();
    alert("Vui lòng nhập nội dung đánh giá!");
    return false;
  }
});
