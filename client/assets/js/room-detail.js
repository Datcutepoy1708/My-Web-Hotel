let currentSlide = 0;
const slides = document.querySelectorAll(".slide");
const thumbnails = document.querySelectorAll(".gallery-item");
const sliderTrack = document.getElementById("sliderTrack");
const thumbnailGallery = document.getElementById("thumbnailGallery");
const totalSlides = slides.length;

function updateSlider() {
  // Update main slider
  sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;

  // Update active thumbnail
  thumbnails.forEach((thumb, index) => {
    thumb.classList.remove("active");
    if (index === currentSlide) {
      thumb.classList.add("active");
    }
  });

  // Scroll thumbnail gallery to show active thumbnail
  const activeThumb = thumbnails[currentSlide];
  const thumbnailWidth = activeThumb.offsetWidth + 10; // width + gap
  const galleryWidth = thumbnailGallery.offsetWidth;
  const scrollPosition =
    currentSlide * thumbnailWidth - galleryWidth / 2 + thumbnailWidth / 2;
<<<<<<< HEAD

=======
>>>>>>> main
  thumbnailGallery.scrollTo({
    left: scrollPosition,
    behavior: "smooth",
  });
}

function moveSlide(direction) {
  currentSlide += direction;

  // Loop around
  if (currentSlide < 0) {
    currentSlide = totalSlides - 1;
  } else if (currentSlide >= totalSlides) {
    currentSlide = 0;
  }

  updateSlider();
}

function goToSlide(index) {
  currentSlide = index;
  updateSlider();
}

// Keyboard navigation
document.addEventListener("keydown", function (e) {
  if (e.key === "ArrowLeft") {
    moveSlide(-1);
  } else if (e.key === "ArrowRight") {
    moveSlide(1);
  }
});

// Touch swipe support
let touchStartX = 0;
let touchEndX = 0;
<<<<<<< HEAD

=======
>>>>>>> main
const slider = document.querySelector(".main-image-slider");

slider.addEventListener("touchstart", (e) => {
  touchStartX = e.changedTouches[0].screenX;
});

slider.addEventListener("touchend", (e) => {
  touchEndX = e.changedTouches[0].screenX;
  handleSwipe();
});

function handleSwipe() {
  if (touchEndX < touchStartX - 50) {
    moveSlide(1);
  }
  if (touchEndX > touchStartX + 50) {
    moveSlide(-1);
  }
}

<<<<<<< HEAD
// Suggestion cards
document.querySelectorAll(".view-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    alert("Chuyển đến trang chi tiết phòng khác");
  });
});
=======
// Xử lý nút "Xem Chi Tiết" trong suggestion cards
document.querySelectorAll(".view-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();

    // Lấy href từ thẻ <a>
    const href = this.getAttribute("href");

    // Kiểm tra href có tồn tại không
    if (href && href !== "#") {
      // Chuyển hướng đến trang chi tiết phòng
      window.location.href = href;
    } else {
      console.error("Không tìm thấy link chi tiết phòng");
    }
  });
});

>>>>>>> main
// Lấy ngày hôm nay
const today = new Date().toISOString().split("T")[0];
const checkin = document.getElementById("checkin");
const checkout = document.getElementById("checkout");

// Không cho chọn ngày quá khứ
checkin.min = today;
checkout.min = today;

// Khi người dùng chọn ngày nhận phòng
checkin.addEventListener("change", function () {
  const checkinDate = checkin.value;

  // Gán min cho checkout bằng checkin
  checkout.min = checkinDate;

  // Nếu ngày checkout hiện tại < checkin → tự động cập nhật
  if (checkout.value && checkout.value < checkinDate) {
    checkout.value = checkinDate;
  }
});
<<<<<<< HEAD
//Kiem Tra tinh trang danh nhap
document.addEventListener("DOMContentLoaded", () => {
  const checkBtn = document.getElementById("check-room-btn");
  const popup = document.getElementById("login-popup");
  const loginBtn = document.getElementById("login-btn");
  const closePopup = document.getElementById("close-popup");

  checkBtn.addEventListener("click", () => {
    if (!window.IS_LOGGED_IN) {
      // Nếu chưa đăng nhập → hiển thị popup
      popup.style.display = "flex";
    } else {
      // Nếu đã đăng nhập → thực hiện hành động mong muốn
      alert(`Xin chào ${window.CURRENT_USER_NAME || "bạn"}!`);
      // Ví dụ: window.location.href = 'check-room.php';
    }
  });

  loginBtn.addEventListener("click", () => {
    // Chuyển đến trang đăng nhập
    const currentUrl = encodeURIComponent(window.location.href);
    window.location.href =
      "/My-Web-Hotel/pages/login.php?redirect=" + currentUrl;
  });

  closePopup.addEventListener("click", () => {
    popup.style.display = "none";
  });
});
=======
>>>>>>> main
