const menuIcon = document.getElementById("menu-icon");
const navLinks = document.getElementById("nav-links");
const closeIcon = document.getElementById("closeIcon");

closeIcon.addEventListener("click", () => {
  navLinks.classList.remove("active");
  menuIcon.style.display = "block";
    closeIcon.style.display = "none";
});
menuIcon.addEventListener("click", () => {
    menuIcon.style.display = "none";
    closeIcon.style.display = "block";
  navLinks.classList.add("active");
});
