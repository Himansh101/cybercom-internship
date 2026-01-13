// 1️. Basic for loop
// Print numbers from 1 to 5
for (let i = 1; i <= 5; i++) {
    console.log("Number:", i);
}

// 2. Reverse loop
// Print numbers from 10 to 1
for (let i = 10; i > 0; i--) {
    console.log("Reverse:", i);
}

// 3. Print even numbers from 1 to 20
for (let i = 1; i <= 20; i++) {
    if (i % 2 === 0) {
        console.log("Even:", i);
    }
}

// 4. while loop example
let count = 1;

while (count <= 5) {
    console.log("While loop:", count);
    count++;
}

// 5. do...while loop example
// Runs at least once
let num = 10;

do {
    console.log("Do while:", num);
    num++;
} while (num < 5);

// 6. break and continue
for (let i = 1; i <= 10; i++) {
    if (i === 5) {
        break; // stop loop
    }
    console.log("Break example:", i);
}

for (let i = 1; i <= 5; i++) {
    if (i === 3) {
        continue; // skip 3
    }
    console.log("Continue example:", i);
}

// 7. Looping through an array (for loop)
let fruits = ["Apple", "Banana", "Mango"];

for (let i = 0; i < fruits.length; i++) {
    console.log("Fruit:", fruits[i]);
}

// 8. Looping through an array (for...of)
for (let fruit of fruits) {
    console.log("for...of:", fruit);
}

// 9. Looping through an object (for...in)
let user = {
    name: "Alex",
    age: 25,
    role: "Backend Developer"
};

for (let key in user) {
    console.log(key + ":", user[key]);
}

//10. Looping through an object (for...of)
for (let key of Object.keys(user)) {
    console.log(key, ":", user[key]);
}


