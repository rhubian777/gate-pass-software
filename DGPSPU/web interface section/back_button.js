document.addEventListener("DOMContentLoaded", function () {
  const menuBtn = document.getElementById("menu-btn");
  const sidebar = document.getElementById("sidebar");

  menuBtn.addEventListener("click", function () {
      if (sidebar.classList.contains("show")) {
          sidebar.classList.remove("show"); // Hide sidebar if open
      } else {
          sidebar.classList.add("show"); // Show sidebar
      }
  });
});
