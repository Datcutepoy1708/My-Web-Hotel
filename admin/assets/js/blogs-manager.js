// Initialize CKEditor for Blog
let blogEditorInstance;
ClassicEditor.create(document.querySelector("#blogEditor"), {
  toolbar: {
    items: [
      "heading",
      "|",
      "bold",
      "italic",
      "link",
      "bulletedList",
      "numberedList",
      "|",
      "outdent",
      "indent",
      "|",
      "imageUpload",
      "blockQuote",
      "insertTable",
      "mediaEmbed",
      "|",
      "undo",
      "redo",
      "|",
      "fontSize",
      "fontFamily",
      "fontColor",
      "fontBackgroundColor",
      "|",
      "alignment",
      "horizontalLine",
      "code",
      "codeBlock",
    ],
  },
  language: "vi",
  image: {
    toolbar: [
      "imageTextAlternative",
      "imageStyle:inline",
      "imageStyle:block",
      "imageStyle:side",
      "linkImage",
    ],
  },
  table: {
    contentToolbar: ["tableColumn", "tableRow", "mergeTableCells"],
  },
  mediaEmbed: {
    previewsInData: true,
  },
  // Simulated image upload
  simpleUpload: {
    uploadUrl: "/upload-image.php",
    headers: {
      "X-CSRF-TOKEN": "CSRF-Token",
    },
  },
})
  .then((editor) => {
    blogEditorInstance = editor;
    console.log("Blog Editor ready", editor);
  })
  .catch((error) => {
    console.error("Error initializing blog editor:", error);
  });

// Initialize CKEditor for Reply
let replyEditorInstance;
setTimeout(() => {
  ClassicEditor.create(document.querySelector("#replyEditor"), {
    toolbar: {
      items: [
        "bold",
        "italic",
        "link",
        "|",
        "bulletedList",
        "numberedList",
        "|",
        "undo",
        "redo",
      ],
    },
    language: "vi",
  })
    .then((editor) => {
      replyEditorInstance = editor;
      console.log("Reply Editor ready", editor);
    })
    .catch((error) => {
      console.error("Error initializing reply editor:", error);
    });
}, 1000);

// Preview image
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Save blog
function saveBlog() {
  const content = blogEditorInstance.getData();
  console.log("Blog content:", content);
  alert(
    "Đã lưu bài viết thành công!\n\nNội dung:\n" +
    content.substring(0, 100) +
    "..."
  );
  // Here you would send data to server
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("addBlogModal")
  );
  modal.hide();
}

// Edit blog
function editBlog(id) {
  console.log("Edit blog:", id);
  // Load blog data and open modal
  const modal = new bootstrap.Modal(document.getElementById("addBlogModal"));
  modal.show();
}

// Delete blog
function deleteBlog(id) {
  if (confirm("Bạn có chắc chắn muốn xóa bài viết này?")) {
    console.log("Delete blog:", id);
    alert("Đã xóa bài viết!");
  }
}

// Delete review
function deleteReview(id) {
  if (confirm("Bạn có chắc chắn muốn xóa đánh giá này?")) {
    console.log("Delete review:", id);
    alert("Đã xóa đánh giá!");
  }
}

// Drag and drop for image upload
const uploadArea = document.querySelector(".image-upload-area");

["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
  uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
  e.preventDefault();
  e.stopPropagation();
}

["dragenter", "dragover"].forEach((eventName) => {
  uploadArea.addEventListener(
    eventName,
    () => {
      uploadArea.style.borderColor = "#d4b896";
      uploadArea.style.background = "#f8f9fa";
    },
    false
  );
});

["dragleave", "drop"].forEach((eventName) => {
  uploadArea.addEventListener(
    eventName,
    () => {
      uploadArea.style.borderColor = "#ddd";
      uploadArea.style.background = "";
    },
    false
  );
});

uploadArea.addEventListener("drop", handleDrop, false);

function handleDrop(e) {
  const dt = e.dataTransfer;
  const files = dt.files;

  if (files.length > 0) {
    document.getElementById("blogImage").files = files;
    previewImage(document.getElementById("blogImage"), "blogPreview");
  }
}

function editArticle() {
  if (confirm("Chuyển sang chế độ chỉnh sửa?")) {
    window.location.href = "edit-blog.php?id=1";
  }
}

function toggleStatus() {
  if (confirm("Thay đổi trạng thái bài viết (Xuất bản/Bản nháp)?")) {
    alert("Đã thay đổi trạng thái!");
  }
}

function deleteArticle() {
  if (confirm("Bạn có chắc chắn muốn xóa bài viết này?")) {
    alert("Đã xóa bài viết!");
    window.location.href = "blog-list.php";
  }
}

// Share functionality
document.querySelectorAll(".share-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    const platform = this.classList[1];
    const url = window.location.href;
    const title = document.querySelector(".article-title").textContent;

    let shareUrl = "";
    switch (platform) {
      case "facebook":
        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        break;
      case "twitter":
        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
        break;
      case "linkedin":
        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
        break;
      case "email":
        window.location.href = `mailto:?subject=${title}&body=${url}`;
        return;
    }

    if (shareUrl) {
      window.open(shareUrl, "_blank", "width=600,height=400");
    }
  });
});

