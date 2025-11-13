// Revenue & Booking Trend Chart
const revenueCtx = document.getElementById("revenueChart").getContext("2d");
let revenueChart = new Chart(revenueCtx, {
  type: "line",
  data: {
    labels: [
      "T1",
      "T2",
      "T3",
      "T4",
      "T5",
      "T6",
      "T7",
      "T8",
      "T9",
      "T10",
      "T11",
      "T12",
    ],
    datasets: [
      {
        label: "Doanh Thu (triệu VNĐ)",
        data: [650, 720, 680, 850, 920, 880, 950, 1020, 980, 1050, 1100, 850],
        borderColor: "#2196F3",
        backgroundColor: "rgba(33, 150, 243, 0.1)",
        tension: 0.4,
        fill: true,
        yAxisID: "y",
      },
      {
        label: "Số Đặt Phòng",
        data: [180, 195, 185, 220, 245, 230, 255, 268, 260, 275, 285, 248],
        borderColor: "#4CAF50",
        backgroundColor: "rgba(76, 175, 80, 0.1)",
        tension: 0.4,
        fill: true,
        yAxisID: "y1",
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: "index",
      intersect: false,
    },
    plugins: {
      legend: {
        position: "top",
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            let label = context.dataset.label || "";
            if (label) {
              label += ": ";
            }
            if (context.parsed.y !== null) {
              if (context.datasetIndex === 0) {
                label += context.parsed.y + " triệu VNĐ";
              } else {
                label += context.parsed.y + " đặt phòng";
              }
            }
            return label;
          },
        },
      },
    },
    scales: {
      y: {
        type: "linear",
        display: true,
        position: "left",
        title: {
          display: true,
          text: "Số lần đặt",
        },
      },
    },
  },
});

// Biểu đồ phân bổ doanh thu (Phòng vs Dịch vụ)
const ctxRevenue = document.getElementById("revenuePieChart").getContext("2d");

const revenuePieChart = new Chart(ctxRevenue, {
  type: "doughnut",
  data: {
    labels: ["Phòng", "Dịch vụ"],
    datasets: [
      {
        data: [75, 25], // ví dụ tỉ lệ doanh thu
        backgroundColor: [
          "rgba(54, 162, 235, 0.8)", // Phòng
          "rgba(255, 99, 132, 0.8)", // Dịch vụ
        ],
        borderColor: ["rgba(54, 162, 235, 1)", "rgba(255, 99, 132, 1)"],
        borderWidth: 1,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom",
        labels: {
          boxWidth: 20,
          color: "#444",
        },
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const dataset = context.dataset;
            const total = dataset.data.reduce((a, b) => a + b, 0);
            const value = dataset.data[context.dataIndex];
            const percentage = ((value / total) * 100).toFixed(1) + "%";
            return `${
              context.label
            }: ${value.toLocaleString()} (${percentage})`;
          },
        },
      },
    },
    cutout: "50%", // tạo khoảng rỗng giữa
  },
});
const ctxRoom = document.getElementById("roomTypeChart").getContext("2d");

const roomTypeChart = new Chart(ctxRoom, {
  type: "bar",
  data: {
    labels: ["Spa", "Giải Trí", "Ăn Uống", "Sự Kiện"],
    datasets: [
      {
        label: "Doanh thu (VNĐ)",
        data: [50000000, 80000000, 120000000, 100000000], // dữ liệu mẫu
        backgroundColor: [
          "rgba(255, 206, 86, 0.8)",
          "rgba(75, 192, 192, 0.8)",
          "rgba(153, 102, 255, 0.8)",
          "rgba(255, 159, 64, 0.8)",
        ],
        borderColor: [
          "rgba(255, 206, 86, 1)",
          "rgba(75, 192, 192, 1)",
          "rgba(153, 102, 255, 1)",
          "rgba(255, 159, 64, 1)",
        ],
        borderWidth: 1,
        borderRadius: 6,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value) {
            return value.toLocaleString("vi-VN") + "₫";
          },
        },
        title: {
          display: true,
          text: "Doanh thu (VNĐ)",
          color: "#333",
          font: { weight: "bold" },
        },
      },
      x: {
        title: {
          display: true,
          text: "Loại dịch vụ",
          color: "#333",
          font: { weight: "bold" },
        },
      },
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function (context) {
            return `${context.label}: ${context.parsed.y.toLocaleString(
              "vi-VN"
            )}₫`;
          },
        },
      },
    },
  },
});
// Occupancy Chart
const occupancyCtx = document.getElementById("occupancyChart").getContext("2d");
new Chart(occupancyCtx, {
  type: "bar",
  data: {
    labels: ["Tầng 1", "Tầng 2", "Tầng 3"],
    datasets: [
      {
        label: "Tỷ lệ lấp đầy (%)",
        data: [68, 75, 72],
        backgroundColor: "rgba(33, 150, 243, 0.6)",
        borderColor: "#2196F3",
        borderWidth: 2,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: "y",
    plugins: {
      legend: {
        display: false,
      },
    },
    scales: {
      x: {
        beginAtZero: true,
        max: 100,
        title: {
          display: true,
          text: "Tỷ lệ (%)",
        },
      },
    },
  },
});

// Functions
function setTimeRange(range) {
  // Remove active class from all buttons
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active");
  });
  // Add active class to clicked button
  event.target.classList.add("active");

  // Update data based on range
  console.log("Time range changed to:", range);
  // Here you would typically reload data from server
}

function updateRevenueChart(period) {
  // Remove active class from all chart buttons
  document.querySelectorAll(".chart-btn").forEach((btn) => {
    btn.classList.remove("active");
  });
  event.target.classList.add("active");

  let newLabels, newRevenueData, newBookingData;

  if (period === "month") {
    newLabels = [
      "T1",
      "T2",
      "T3",
      "T4",
      "T5",
      "T6",
      "T7",
      "T8",
      "T9",
      "T10",
      "T11",
      "T12",
    ];
    newRevenueData = [
      650, 720, 680, 850, 920, 880, 950, 1020, 980, 1050, 1100, 850,
    ];
    newBookingData = [
      180, 195, 185, 220, 245, 230, 255, 268, 260, 275, 285, 248,
    ];
  } else if (period === "quarter") {
    newLabels = ["Q1", "Q2", "Q3", "Q4"];
    newRevenueData = [2050, 2650, 2950, 850];
    newBookingData = [560, 695, 783, 248];
  } else if (period === "year") {
    newLabels = ["2020", "2021", "2022", "2023", "2024", "2025"];
    newRevenueData = [7800, 8500, 9200, 10500, 11200, 850];
    newBookingData = [2100, 2300, 2500, 2800, 3000, 248];
  }

  revenueChart.data.labels = newLabels;
  revenueChart.data.datasets[0].data = newRevenueData;
  revenueChart.data.datasets[1].data = newBookingData;
  revenueChart.update();
}

function exportReport() {
  alert(
    "Chức năng xuất báo cáo đang được phát triển!\n\nSẽ xuất file Excel/PDF với toàn bộ dữ liệu thống kê."
  );
  // Here you would implement actual export functionality
}

// Update date range
document.getElementById("startDate").addEventListener("change", function () {
  console.log("Start date changed:", this.value);
  // Reload data based on new date range
});

document.getElementById("endDate").addEventListener("change", function () {
  console.log("End date changed:", this.value);
  // Reload data based on new date range
});
