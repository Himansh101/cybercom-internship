// SECTION 1 – Changing HTML
function changeText() {
  document.getElementById("text").innerText = "Text changed using DOM!";
}

// SECTION 2 – Changing CSS
function changeColor() {
  const el = document.getElementById("colorText");
  el.style.color = el.style.color === "red" ? "green" : "red";
}

// SECTION 3 – Events & Counter
let count = 0;

function increase() {
  count++;
  document.getElementById("count").innerText = count;
}

function decrease() {
  count--;
  document.getElementById("count").innerText = count;
}

// SECTION 4 – Form Validation
function validateForm() {
  const input = document.getElementById("username").value.trim();
  const msg = document.getElementById("message");

  if (input === "") {
    msg.innerText = "Username cannot be empty!";
    msg.className = "error";
  } else {
    msg.innerText = "Form submitted successfully!";
    msg.className = "success";
  }
}

// SECTION 5 – Dynamic Elements
function addItem() {
  const input = document.getElementById("itemInput");
  const list = document.getElementById("list");

  if (input.value === "") return;

  const li = document.createElement("li");
  li.innerText = input.value;
  list.appendChild(li);

  input.value = "";
}

// SECTION 6 – DOM Animation
function animateBox() {
  const box = document.getElementById("box");
  let position = 0;

  const interval = setInterval(() => {
    if (position >= 300) {
      clearInterval(interval);
    } else {
      position += 5;
      box.style.left = position + "px";
    }
  }, 20);
}
