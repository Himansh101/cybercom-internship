//decalaring objects
const person = {
  firstName: "John",
  lastName: "Doe",
  age: 50,
  eyeColor: "blue"
};

console.log(person);

// Create an Object
const person1 = {};

// Add Properties
person.firstName = "John";
person.lastName = "Doe";
person.age = 50;
person.eyeColor = "blue";

console.log(person1);

// Create an Object
const person2 = new Object({
  firstName: "John",
  lastName: "Doe",
  age: 50,
  eyeColor: "blue"
});

console.log(person2);

//access the porperty of object

console.log(person.lastName);
console.log(person["lastName"]);

const person3 = {
  firstName: "John",
  lastName : "Doe",
  id       : 5566,
  fullName : function() {
    return this.firstName + " " + this.lastName;
  }
};


let name = person3.fullName()
console.log(name);

let p1 = person
console.log(p1);

// Constructor Function for Person objects
function Person(first, last, age, eye) {
  this.firstName = first;
  this.lastName = last;
  this.age = age;
  this.eyeColor = eye;
}

// Create a Person object
const myFather = new Person("Jane", "Doe", 47, "Green");

// Display age
let displayAge ="My father is " + myFather.age + " years old"; 
console.log(displayAge);

delete person.age;

console.log(person);

//nested object

myObj = {
  name:"John",
  age:30,
  myCars: {
    car1:"Ford",
    car2:"BMW",
    car3:"Fiat"
  }
}

console.log(myObj);

//Object.values() creates an array from the property values:

// Create an Array
const myArray = Object.values(person);

// Stringify the Array
let text = myArray.toString();
console.log(text);

//Object.entries() makes it simple to use objects in loops

const fruits = {Bananas:300, Oranges:200, Apples:500};

let text1 = "";
for (let [fruit, value] of Object.entries(fruits)) {
  text1 += fruit + ": " + value + " ";
}

console.log(text1);

//JavaScript objects can be converted to a string with JSON method JSON.stringify().
// Stringify Object
let text2 = JSON.stringify(fruits);
console.log(text2);
