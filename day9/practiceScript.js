//* 1. ARRAYS – CREATION & BASICS

let numbers = [10, 20, 30, 40, 50];
console.log(numbers);


// Accessing elements
console.log(numbers[0]); // 10

// Modifying elements
numbers[1] = 25;

// Length
console.log(numbers.length); // 5


//* 2. BASIC ARRAY METHODS (MUTATING)

numbers.push(60);     // add at end
console.log(numbers,"after push");

numbers.pop();        // remove from end
console.log(numbers,"after pop");

numbers.unshift(5);   // add at start
console.log(numbers,"after unshift");

numbers.shift();      // remove from start
console.log(numbers,"after shift");


//* 3. ARRAY ITERATION (for & for...of)

for (let i = 0; i < numbers.length; i++) {
  console.log("for loop:", numbers[i]);
}

for (let num of numbers) {
  console.log("for...of:", num);
}


//* 4. ARRAY METHODS (NON-MUTATING)

// map → returns NEW array
let squaredNumbers = numbers.map(n => n * n);
console.log(squaredNumbers);

// filter → returns NEW array
let evenNumbers = numbers.filter(n => n % 2 === 0);
console.log(evenNumbers);

// reduce → returns single value
let totalSum = numbers.reduce((sum, n) => sum + n, 0);
console.log(totalSum);

// forEach → no return value
numbers.forEach(n => console.log("forEach:", n));


//* 5. SPLICE (MUTATING)

// Remove + Insert
let removedItems = numbers.splice(2, 1, 99);

// removedItems contains removed values
console.log("Removed:", removedItems);

// numbers is mutated
console.log("After splice:", numbers);


// 6. SORT(MUTATING)

numbers.sort((a, b) => a - b);
console.log("Sorted:", numbers);

//OBJECT CREATION AND ACCESS

let user = {
  name: "Sam",
  age: 22,
  skills: ["JavaScript", "HTML", "CSS"],
  isActive: true
};

console.log(user);

// Dot notation
console.log(user.name);

// Bracket notation
console.log(user["age"]);

// Add & update properties
user.city = "Delhi";
user.age = 23;

console.log(user);

// Delete property
delete user.isActive;

//OBJECT METHOD AND THIS KEYWORD

user.greet = function () {
  return "Hello, my name is " + this.name;
};

console.log(user.greet());

//OBJECT ITERATION

// for...in
for (let key in user) {
  console.log("for...in:", key, user[key]);
}

// Object.keys + for loop
let keys = Object.keys(user);
for (let i = 0; i < keys.length; i++) {
  console.log("keys:", keys[i], user[keys[i]]);
}

// Object.entries + for...of
for (let [key, value] of Object.entries(user)) {
  console.log("entries:", key, value);
}

//ARRAY OF OBJECTS(REAL WORLD)

let products = [
  { name: "Laptop", price: 50000 },
  { name: "Phone", price: 20000 },
  { name: "Tablet", price: 30000 }
];

// Iteration over array of objects
for (let product of products) {
  console.log(product.name, product.price);
}

// filter + map chaining
let expensiveProductNames = products
  .filter(p => p.price > 25000)
  .map(p => p.name);

console.log("Expensive Products:", expensiveProductNames);

//PASS BY VALUE AND PASS BY REFERENCE

// Pass by value
let a = 10;
let b = a;
b = 20;
console.log(a); // 10

// Pass by reference
let obj1 = { value: 100 };
let obj2 = obj1;
obj2.value = 200;
console.log(obj1.value); // 200