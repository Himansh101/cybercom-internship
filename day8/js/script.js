// 1️⃣ DATA STORAGE (ARRAY + OBJECT)

// Get users from localStorage OR empty array
let users = JSON.parse(localStorage.getItem("users")) || [];

// 2️⃣ SAVE DATA

function saveUser() {
  let name = document.getElementById("name").value;
  let age = document.getElementById("age").value;

  if (!name || !age) {
    alert("Please enter all details");
    return;
  }

  // Create object
  let user = {
    name: name,
    age: Number(age)
  };

  // Store in array
  users.push(user);

  // Save to localStorage
  localStorage.setItem("users", JSON.stringify(users));

  // Clear inputs
  document.getElementById("name").value = "";
  document.getElementById("age").value = "";

  // Update UI
  displayUsers();
}

// 3️⃣ DISPLAY DATA (DOM MANIPULATION)

function displayUsers() {
  let list = document.getElementById("userList");
  list.innerHTML = ""; // Clear old content

  users.forEach((user, index) => {
    let li = document.createElement("li");
    li.innerText = `${user.name} (${user.age} years)`;

    list.appendChild(li);
  });
}

// 4️⃣ LOAD DATA ON PAGE REFRESH

displayUsers();
