const form = document.getElementById("regForm");
const errorMsg = document.getElementById("errorMsg");
const userList = document.getElementById("userList");

// Array of objects
let users = [];

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const mobile = document.getElementById("mobile").value.trim();
  const address = document.getElementById("address").value.trim();
  const qualification = document.getElementById("qualification").value;
  const dob = document.getElementById("dob").value;
  const gender = document.querySelector('input[name="gender"]:checked');
  const terms = document.getElementById("terms").checked;

  // Validation
  if (!name || !email || !password || !mobile || !address || !qualification || !dob || !gender) {
    errorMsg.innerText = "All fields are required";
    return;
  }

  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    errorMsg.innerText = "Email should be in valid format"
  }

  if (password.length < 6) {
    errorMsg.innerText = "Password must be at least 6 characters";
    return;
  }

  if (!/^\+[1-9][0-9]{1,3}[1-9][0-9]{9}$/.test(mobile)) {
    errorMsg.innerText = "Enter valid mobile number with country code (e.g. +919876543210)";
    return;
  }

  if (!terms) {
    errorMsg.innerText = "Please accept terms and conditions";
    return;
  }

  errorMsg.innerText = "";

  // Store data in object
  const user = {
    name,
    email,
    mobile,
    address,
    qualification,
    dob,
    gender: gender.value
  };

  users.push(user);

  renderTable();
  form.reset();
});

// Display data
// function renderUsers() {
//   userList.innerHTML = "";

//   users.forEach((user, index) => {
//     const li = document.createElement("li");
//     li.innerHTML = `
//       <strong>${index + 1}. ${user.name}</strong><br>
//       Email: ${user.email}<br>
//       Mobile: ${user.mobile}<br>
//       Qualification: ${user.qualification}<br>
//       DOB: ${user.dob}<br>
//       Gender: ${user.gender}<br>
//       Address: ${user.address}
//     `;
//     userList.appendChild(li);
//   });
// }

function renderTable() {
  tableBody.innerHTML = "";

  users.forEach((user, index) => {
    const row = `
      <tr>
        <td>${index + 1}</td>
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${user.mobile}</td>
        <td>${user.qualification}</td>
        <td>${user.dob}</td>
        <td>${user.gender}</td>
        <td>${user.address}</td>
        <td>
          <button class="delete-btn" onclick="deleteUser(${index})">Delete</button>
        </td>
      </tr>
    `;
    tableBody.innerHTML += row;
  });
}

function deleteUser(index) {
  users.splice(index, 1);
  renderTable();
}
