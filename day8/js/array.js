//declaring array
let arr10 = [42,23,55,45]
console.log(arr10);

let arr11 = new Array(42,23,55,45)
console.log(arr11);

//decalaring an array with 1 element with []
let oneArr = [40] 
console.log(oneArr);

//decalaring an array with new keyword
let oneArr1 = new Array(40)
console.log(oneArr1);



//Join method

const fruits = ["Banana", "Orange", "Apple", "Mango"];
console.log(fruits.join(" * "));

//check whether it is array or not
let isArr = Array.isArray(fruits);
console.log(isArr);

//deleting an element leaves "undefined" holes
delete fruits[0];
console.log(fruits);
console.log(fruits[0]);

//concat arrays
const girls = ["Cecilie", "Lone"];
const boys = ["Emil", "Tobias", "Linus"];

const children = girls.concat(boys);

console.log(children);

//The copyWithin() method copies array elements to another position in an array:
fruits.copyWithin(2, 0);
console.log(fruits);

const fruits1 = ["Banana", "Orange", "Apple", "Mango", "Kiwi"];
fruits1.copyWithin(2, 0, 2);
console.log(fruits1);

//flatening array
const myArr = [[1,2],[3,4],[5,6]];
console.log(myArr);

const newArr = myArr.flat();
console.log(newArr);

//searching position of an element
const fruits2 = ["Apple", "Orange", "Apple", "Mango"];
let position = fruits2.indexOf("Apple") + 1;

console.log(fruits2);
console.log("position of Apple is:", position);

//last position of an element
let positionLast = fruits2.lastIndexOf("Apple") + 1;
console.log("last position of Apple is:", positionLast);

//return true if element is present is the arrray
let checkMango = fruits2.includes("Mango"); // is true
console.log(checkMango);

//find element which satisfies conditions
const numbers = [4, 9, 16, 25, 29];
let first = numbers.find(myFunction);
let firstIndex = numbers.findIndex(myFunction);


function myFunction(value, index, array) {
    return value > 18;
}

console.log(first);
console.log(firstIndex);

//find last element which satisfes given condition

const temp = [27, 28, 30, 40, 42, 35, 30];
let high = temp.findLast(x => x > 40);
let posLast = temp.findLastIndex(x => x > 40);

console.log(high);
console.log(posLast);

//toSorted methos creates new array which is sorted
const months = ["Jan", "Feb", "Mar", "Apr"];
const sorted = months.toSorted();
const reversed = months.toReversed();

console.log(months);
console.log(sorted);
console.log(reversed);

//arr.sort is used different to sort num array 

const points = [40, 100, 1, 5, 25, 10];
console.log(points);

points.sort();
console.log(points);

points.sort(function(a,b){return a-b})
console.log(points);

//sorts array in random order

points.sort(function(){return 0.5 - Math.random()});
console.log(points);

function myArrayMin(arr) {
    return Math.min.apply(null, arr);
}

function myArrayMax(arr) {
    return Math.max.apply(null, arr);
}

//get minimum and maximum element of an array

let min = myArrayMin(points);
console.log(min);

let max =myArrayMax(points);
console.log(max);

//sorting array of objects

const cars = [
    {type:"Volvo", year:2016},
    {type:"Saab", year:2001},
    {type:"BMW", year:2010}
];

cars.sort(function(a, b){
    let x = a.type.toLowerCase();
    let y = b.type.toLowerCase();
    if (x < y) {return -1;}
    if (x > y) {return 1;}
    return 0;
})

console.log(cars);

//iterating over arrays

let txt1 ="";

const numbers3 = [45, 4, 9, 16, 25];
numbers3.forEach(myFunction);

function myFunction(value,index,array) {
    txt1 += value + ",";
}

console.log(txt1);


//map method to create new array
const numbers1 = [45, 4, 9, 16, 25];
const numbers2 = numbers1.map(myFunction);

function myFunction(value, index, array) {
  return value * 2;
}

console.log(numbers2);

const myArr1 = [1, 2, 3, 4, 5, 6];
const newArr1 = myArr1.flatMap((x) => x * 2);

console.log(newArr1);

//filter the elements

const over18 = numbers1.filter(myFunction);

function myFunction(value, index, array) {
  return value > 18;
}

console.log(over18);

//reduce the array

let sum = numbers1.reduce(myFunction);

function myFunction(total, value) {
  return total + value;
}

console.log("reduce output:",sum);

//adding in a non-zero value

let sum1 = numbers1.reduce(myFunction, 100);
console.log("reduce in 100:",sum1);

//reduceRight reduces from right to left

let sum2 = numbers1.reduceRight(myFunction);
console.log("reduceRight output:",sum2);

//every and sum to check the presence of an element which satisfies given condition
let allOver18 = numbers1.every(myFunction1);

function myFunction1(value, index, array) {
  return value > 18;
}

console.log("'every' method output",allOver18);

let anyOver18 = numbers1.every(myFunction);
console.log("'some' method output",anyOver18);

//The Array.from() method returns an Array object from string or object
let text = "ABCDEFG";
let myArr2 = Array.from(text);

console.log(text);
console.log(myArr2);

// Array.from() has an optional parameter which allows you to execute a function on each element of the new array:
const myNumbers = [1,2,3,4];
const myArr3 = Array.from(myNumbers, (x) => x * 2);
console.log(myArr3);

//The Array.keys() method returns an Array Iterator object with the keys of an array.
const fruits3 = ["Banana", "Orange", "Apple", "Mango"];
const keys = fruits3.keys();
console.log(keys);

const f = fruits3.entries();
console.log(f);

let text1 = "";
for (let x of keys) {
  text1 += x + " ";
}

console.log(text1);

for (let x of f){
    console.log(x);
}

//spread(...) operator used to expand an array into individual element
const arr1 = [1, 2, 3];
const arr2 = [4, 5, 6];

const arr3 = [...arr1, ...arr2];

console.log(arr3);

//spread ooperator to pass argumments
const numbers4 = [23,55,21,87,56];
let minValue = Math.min(...numbers4);
let maxValue = Math.max(...numbers4);

console.log(minValue);
console.log(maxValue);

//The rest operator (...) allows us to destruct an array and collect the leftovers
let a, rest;
const arr4 = [1,2,3,4,5,6,7,8];

[a, ...rest] = arr4;

console.log(a);
console.log(rest);






