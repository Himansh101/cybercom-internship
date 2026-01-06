const sections = document.querySelectorAll("section");

// const observer = new IntersectionObserver(entries => {
//   entries.forEach(entry => {
//     if (entry.isIntersecting) {
//       entry.target.style.opacity = 1;
//       entry.target.style.transform = "translateY(0)";
//     }
//   });
// }, { threshold: 0.1 });

// sections.forEach(section => {
//   section.style.opacity = 0;
//   section.style.transform = "translateY(30px)";
//   section.style.transition = "all 0.8s ease";
//   observer.observe(section);
// });

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
