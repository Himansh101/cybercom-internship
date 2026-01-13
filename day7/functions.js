// 2. Functions (Normal & Arrow)

// Normal function with return
function square(num) {
    return num ** 2;
}
console.log("Square of 5:", square(5));

// Arrow function
const multiply = (a, b) => a * b;
console.log("Multiply 5*3:", multiply(5, 3));

// Check even
const checkEven = num => num % 2 === 0;
console.log("Is 4 even?", checkEven(4));

// Default parameter
function greet(name = "Guest") {
    console.log("Hello " + name);
}
greet();
greet("Sam");

// Different Types of Return Values

// a) Return a primitive
function add(a, b) {
    return a + b;
}
console.log("Return primitive:", add(5, 3)); // 8

// b) Return a string
function greet(name) {
    return "Hello " + name;
}
console.log("Return string:", greet("Alex")); // Hello Alex

// c) Return an object
function createUser(name, age) {
    return { name: name, age: age };
}
const user2 = createUser("Sam", 25);
console.log("Return object:", user2);

// d) Return a function (closure)
function multiplier(x) {
    return function (y) {
        return x * y;
    };
}
const double = multiplier(2);
console.log("Return function:", double(5)); // 10

// e) Return an array
function getNumbers() {
    return [1, 2, 3, 4, 5];
}
const numbers2 = getNumbers();
console.log("Return array:", numbers2);

// Pass by Value (Primitives)
// Primitives are passed by value → original not affected
let num1 = 10;

function changeNumber(n) {
    n = n + 5; // modifies local copy
    console.log("Inside function (num):", n);
}

changeNumber(num1);            // 15
console.log("Outside function (num):", num1); // 10 → unchanged


//  Pass by Reference (Objects/Arrays)

// Objects & arrays are passed by reference → original affected
const obj = { name: "Alex", age: 25 };

function changeUser(user) {
    user.age += 5;             // modifies original object
    user.city = "New York";    // add new property
    console.log("Inside function (user):", user);
}

changeUser(obj);
console.log("Outside function (user):", obj);

// Array example
const arr = [1, 2, 3];
function modifyArray(a) {
    a.push(4);
    console.log("Inside array function:", a);
}

modifyArray(arr);
console.log("Outside array function:", arr); // [1,2,3,4]


// 2. Callbacks & Higher-Order Function

// Callback function example
function processUser(callback) {
    const userName = "Alex";
    callback(userName);
}

processUser(name => console.log("Callback greeting:", name));

// Higher-order function example
const applyOperation = (a, b, fn) => fn(a, b);

console.log("Add 5+3:", applyOperation(5, 3, (x, y) => x + y));
console.log("Multiply 5*3:", applyOperation(5, 3, (x, y) => x * y));

// Array HOF + callbacks (map, filter, reduce)
const numbers = [1, 2, 3, 4, 5, 6, 7];
const evenNumbers = numbers.filter(n => n % 2 === 0);
const squares = numbers.map(n => n * n);
const sum = numbers.reduce((acc, curr) => acc + curr, 0);

console.log("Even:", evenNumbers, "Squares:", squares, "Sum:", sum);

// 3. Closures Examples

// Counter closure
function createCounter() {
    let count = 0;
    return function () {
        count++;
        return count;
    }
}

const counter = createCounter();
console.log("Counter:", counter());
console.log("Counter:", counter());
console.log("Counter:", counter());

// Greeting factory closure
function greetMessage(message) {
    return function (name) {
        return message + " " + name;
    }
}

const greetHello = greetMessage("Hello");
console.log(greetHello("Alex"));
console.log(greetHello("Sam"));

// Memoization example
const memoize = fn => {
    const cache = {};
    return arg => {
        if (cache[arg] !== undefined) {
            console.log("Fetching from cache:", arg);
            return cache[arg];
        }
        console.log("Calculating:", arg);
        const result = fn(arg);
        cache[arg] = result;
        return result;
    };
};

const squareMemo = memoize(x => x * x);
console.log(squareMemo(5)); // Calculating
console.log(squareMemo(5)); // Cached
console.log(squareMemo(6)); // Calculating


// 4. Real-world pattern: Counter with callback
function createDynamicCounter(callback) {
    let count = 0;
    return function () {
        count++;
        callback(count);
    }
}

const printCount = createDynamicCounter(count => {
    console.log("Dynamic counter:", count);
});

printCount(); // 1
printCount(); // 2
printCount(); // 3

// 5. Greeting Factory + Arrow Function
const createGreeting = greeting => name => `${greeting}, ${name}!`;

const sayHello = createGreeting("Hello");
const sayHi = createGreeting("Hi");

console.log(sayHello("Alex")); // Hello, Alex!
console.log(sayHello("Sam"));  // Hello, Sam!
console.log(sayHi("John"));    // Hi, John!

// 6. Error Handling

function divide(a, b) {
    if (b === 0) throw new Error("Cannot divide by zero!");
    return a / b;
}

try {
    console.log(divide(10, 2));
    console.log(divide(5, 0));
} catch (error) {
    console.log(error.message);
}

// 7. Destructuring & Spread/Rest

const user1 = { name: "Sam", age: 25, role: "Dev" };
const { name: userName, age, role } = user1;
console.log(userName, age, role);

const numbers1 = [1, 2, 3, 4];
const [first, ...rest] = numbers1;
console.log(first, rest);

const arr1 = [1, 2];
const arr2 = [3, 4];
const combined = [...arr1, ...arr2];
console.log(combined);


// 8. Optional Chaining & Nullish Coalescing
const profile = { settings: { theme: null } };
const theme = profile.settings?.theme ?? "light";
console.log(theme);

const email = profile.contact?.email ?? "No email";
console.log(email);

// 9. Async/Await & Promises
function fetchData() {
    return new Promise(resolve => {
        setTimeout(() => resolve("Data loaded!"), 1000);
    });
}

async function getData() {
    console.log("Start fetching");
    const result = await fetchData();
    console.log(result);
    console.log("End fetching");
}

getData();