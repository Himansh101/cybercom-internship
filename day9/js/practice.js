// SECTION 1: CHANGE CONTENT

document.querySelector("#changeTextBtn").addEventListener("click", () => {
  document.getElementById("text").innerText = "Text changed using DOM!";
});

// SECTION 2: CHANGE CSS

document.getElementById("styleBtn").addEventListener("click", () => {
  document.getElementById("mainTitle").classList.toggle("active");
});

// SECTION 3: FORM VALIDATION

document.getElementById("submitBtn").addEventListener("click", () => {
  const name = document.getElementById("username").value;
  const msg = document.getElementById("formMsg");

  if (name === "") {
    msg.textContent = "Name is required!";
    msg.style.color = "red";
  } else {
    msg.textContent = "Welcome, " + name;
    msg.style.color = "green";
  }
});

// SECTION 4: DYNAMIC UI

document.getElementById("addItemBtn").addEventListener("click", () => {
  const li = document.createElement("li");
  li.textContent = "New Item";
  document.getElementById("list").appendChild(li);
});

// SECTION 5: EVENTS

const eventBtn = document.getElementById("eventBtn");

eventBtn.addEventListener("click", () => {
  alert("Button Clicked");
});

eventBtn.addEventListener("mouseover", () => {
  eventBtn.style.background = "orange";
});

eventBtn.addEventListener("mouseout", () => {
  eventBtn.style.background = "";
});

// SECTION 6: DOM ANIMATION

document.getElementById("animateBtn").addEventListener("click", () => {
  let pos = 0;
  const box = document.getElementById("box");

  const interval = setInterval(() => {
    if (pos >= 300) {
      clearInterval(interval);
    } else {
      pos++;
      box.style.left = pos + "px";
    }
  }, 5);
});
