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
stars.forEach((star) => {
  star.addEventListener("click", () => {
    const value = star.getAttribute("data-value");
    stars.forEach((s, i) => {
      if (i < value) {
        s.classList.add("active");
      } else {
        s.classList.remove("active");
      }
    });
  });
});
