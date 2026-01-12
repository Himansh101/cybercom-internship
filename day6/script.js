//Operators
// 1. Arithmetic Operators
let a = 10;
let b = 5;

console.log("Arithmetic Operators:");
console.log("Addition:", a + b);
console.log("Subtraction:", a - b);
console.log("Multiplication:", a * b);
console.log("Division:", a / b);
console.log("Modulus:", a % b);

// 2. Assignment Operators
let x = 10;
x += 5;   // x = x + 5
console.log("\nAssignment Operator:");
console.log("x =", x);

// 3. Comparison Operators
console.log("\nComparison Operators:");
console.log(a > b);    // true
console.log(a < b);    // false
console.log(a == b);   // false
console.log(a != b);   // true
console.log(a === 10); // true 

// 4. Logical Operators
let age = 20;

console.log("\nLogical Operators:");
console.log(age > 18 && age < 60); // true
console.log(age < 18 || age > 60); // false
console.log(!(age < 18));          // true

// 5. Ternary Operator
let result = (age >= 18) ? "Adult" : "Minor";
console.log("\nTernary Operator:");
console.log(result);

// concatenate strings

let name = "Rohit"
let surname = "Sharma"

let fullname = name + " " + surname
console.log(fullname)

// adding strings and numbers

let text1 = 5 + 5;
let text2 = "5" + 5;
let text3 = "Hello" + 5;

console.log(text1)
console.log(text2)
console.log(text3)

//Conditional Assignment operations
//AND operators
let number1 = 1;
let number2 = number1 &&= 10;
console.log(number2)

let number3 = 0;
let number4 = number3 &&= 10;
console.log(number4)

let number5 = undefined;
let number6 = number5 &&= 10;
console.log(number6)

let number7 = null;
let number8 = number7 &&= 10;
console.log(number8)

//OR operators
let text12 = 1;
let text13 = text12 ||= 10;
console.log(text13)

let text14 = 0;
let text15 = text14 ||= 10;
console.log(text15)

let text16 = undefined;
let text17 = text16 ||= 10;
console.log(text17)

let text18 = null;
let text19 = text18 ||= 10;
console.log(text19)

// Nullish coalescing assignment operator

let num1;
num1 ??= 10;
console.log(num1)

let num2 = 0;
num2 ??= 10;
console.log(num2)

let num3 = null;
num3 ??= 10;
console.log(num3)

let num4 = 20;
num4 ??= 10;
console.log(num4)

let text = "12345";

let min = Math.min(...text);
let max = Math.max(...text);

console.log("min: " + min + " " + "max: " + max)

//Conditional Statements

let voteable;
let age1 = 26;
if (isNaN(age1)) {
    voteable = "Input is not a number";
} else {
    voteable = (age < 18) ? "Too young" : "Old enough";
}
console.log(voteable)

let marks = 85;

if (marks >= 90) {
    console.log("Grade: A");
} else if (marks >= 75) {
    console.log("Grade: B");
} else if (marks >= 50) {
    console.log("Grade: C");
} else {
    console.log("Grade: Fail");
}

let day = 3;

switch (day) {
    case 1:
        console.log("Monday");
        break;
    case 2:
        console.log("Tuesday");
        break;
    case 3:
        console.log("Wednesday");
        break;
    default:
        console.log("Invalid day");
}

let n = 10;

let check = (n % 2 === 0) ? "Even Number" : "Odd Number";
console.log(check);
