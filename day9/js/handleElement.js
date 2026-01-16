// ELEMENT SELECTION

const title = document.getElementById("title");
const paragraphs = document.getElementsByClassName("para");
const allPs = document.getElementsByTagName("p");
const firstPara = document.querySelector(".para");
const allParas = document.querySelectorAll(".para");

console.log(paragraphs);

console.log(allParas);


// CHANGING CONTENT

title.innerText = "DOM Handling Mastery";
firstPara.textContent = "Updated using textContent";
allParas[1].innerHTML = "<b>Updated using innerHTML</b>";

// ATTRIBUTE HANDLING

title.setAttribute("data-info", "heading");
console.log(title.getAttribute("data-info"));
title.removeAttribute("data-info");


// STYLE & CLASS HANDLING

title.style.color = "blue";
title.classList.add("highlight");
console.log(title.classList.contains("highlight"));
title.classList.toggle("highlight");


// CREATE & APPEND ELEMENT
const li = document.createElement("li");
li.innerText = "Dynamically Added Item";
document.getElementById("list").appendChild(li);

// REMOVE ELEMENT
setTimeout(() => {
  li.remove();
}, 3000);

// EVENTS

const btn = document.getElementById("btn");
const removeBtn = document.getElementById("removeBtn");

function handleClick() {
  alert("Button Clicked!");
}

btn.addEventListener("click", handleClick);

// REMOVE EVENT LISTENER
removeBtn.addEventListener("click", () => {
  btn.removeEventListener("click", handleClick);
  alert("Event Removed");
});
