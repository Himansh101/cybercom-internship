// SECTION 1: Mouse Events
const clickBtn = document.getElementById("clickBtn");
const dblBtn = document.getElementById("dblBtn");
const mouseBox = document.getElementById("mouseBox");
const mouseMsg = document.getElementById("mouseMsg");

clickBtn.addEventListener("click", () => {
  mouseMsg.innerText = "Button clicked!";
});

dblBtn.addEventListener("dblclick", () => {
  mouseMsg.innerText = "Button double-clicked!";
});

mouseBox.addEventListener("mouseover", () => {
  mouseBox.style.background = "green";
});

mouseBox.addEventListener("mouseout", () => {
  mouseBox.style.background = "steelblue";
});

// SECTION 2: Keyboard Events

const keyInput = document.getElementById("keyInput");
const keyMsg = document.getElementById("keyMsg");

keyInput.addEventListener("keydown", () => {
  keyMsg.innerText = event.key + " Key is pressed";
});

keyInput.addEventListener("keyup", () => {
  keyMsg.innerText = event.key + " Key is released";
});

// SECTION 3: Form Events
const form = document.getElementById("myForm");
const formMsg = document.getElementById("formMsg");

form.addEventListener("submit", (e) => {
  e.preventDefault();
  formMsg.innerText = "Form submitted successfully!";
  formMsg.className = "success";
});

// SECTION 4: Input / Focus Events
const focusInput = document.getElementById("focusInput");
const focusMsg = document.getElementById("focusMsg");

focusInput.addEventListener("focus", () => {
  focusMsg.innerText = "Input focused";
});

focusInput.addEventListener("blur", () => {
  focusMsg.innerText = "Input lost focus";
});

focusInput.addEventListener("input", () => {
  focusMsg.innerText = "Typing: " + focusInput.value;
});

// SECTION 5: Window Events
const windowMsg = document.getElementById("windowMsg");
const scrollBox = document.getElementById("scrollBox");

window.addEventListener("load", () => {
  windowMsg.innerText = "Page loaded successfully!";
});

window.addEventListener("resize", () => {
  windowMsg.innerText = "Window resized";
});

scrollBox.addEventListener("scroll", () => {
  windowMsg.innerText = "Scrolling detected";
});

// SECTION 6: DOM Animation
const animateBtn = document.getElementById("animateBtn");
const animateBox = document.getElementById("animateBox");

animateBtn.addEventListener("click", () => {
  let pos = 0;

  const interval = setInterval(() => {
    if (pos >= 300) {
      clearInterval(interval);
    } else {
      pos += 5;
      animateBox.style.left = pos + "px";
    }
  }, 20);
});
