// alert('hello student');

//variables
let x, y, z;  // Statement 1
x = 5;        // Statement 2
y = 6;        // Statement 3
z = x + y;    // Statement 4


// functions
function btnClicked() {
    document.getElementById("demo").innerHTML = "The value of z is " + z;
    document.getElementById("clickBtn").innerText = "Button is Clicked!";
}

/* This -----------------------------------------------------------
is a --------------------------------------------------------------
multiline ---------------------------------------------------------
comment -------------------------------------------------------- */

let a = 2; 
a = 3;  //valid
const b = 5; //not valid
// b = 8;
console.log(a + " " + b);
{
    let a = 5; //valid
    console.log(a);
}

let p = "5" + 2 + 3;
console.log(p);

p = 5 + 2 + "3";
console.log(p);

// Number
let length = 16;
let weight = 7.5;

// BigInt
let bigIntVar1 = 1234567890123456789012345n;
let bigIntVar2 = BigInt(1234567890123456789012345n)
// Strings
let color = "Yellow";
let lastName = "Johnson";


// Boolean
let bool1 = true;
let bool2 = false;

// Undefined
let undefinedVar;

// Null
let nullVar = null;

// Symbol
const symVar = Symbol();

// Object
const person1 = {firstName:"John", lastName:"Doe"};

// Array Object
const cars = ["Saab", "Volvo", "BMW"];

// Date Object
const date = new Date("2022-03-25");

let vr1 = 16 + 4 + "Volvo";
console.log(vr1); //this will give 20Volvo

let vr2 = "Volvo" + 16 + 4;
console.log(vr2); //this will give Volvo164

console.log(typeof "");             // Returns "string"
console.log(typeof "John");         // Returns "string"
console.log(typeof "John Doe");     // Returns "string"

console.log(typeof 0)              // Returns "number"
console.log(typeof 314)            // Returns "number"
console.log(typeof 3.14)           // Returns "number"
console.log(typeof (3));           // Returns "number"
console.log(typeof (3 + 4))        // Returns "number"

console.log(typeof {name:'John'});   // Returns "object"
console.log(typeof [1,2,3,4]);       // Returns "object"
console.log(typeof {});              // Returns "object"
console.log(typeof []);              // Returns "object"
console.log(typeof new Object());    // Returns "object"
console.log(typeof new Array());     // Returns "object"
console.log(typeof new Date());      // Returns "object"
console.log(typeof new Set());       // Returns "object"
console.log(typeof new Map());       // Returns "object"
console.log(typeof function () {});  // Returns "function"
console.log(typeof x );              // Returns "undefined"
console.log(typeof null);            // Returns "object"
