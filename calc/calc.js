const display = document.getElementById("display");
const buttons = document.querySelectorAll(".btn");

let currentInput = "";

// Function to calculate result safely
function calculate(input) {
  const tokens = input.match(/(\d+\.?\d*|\+|\-|\*|\/)/g);
  if (!tokens) return "";

  let stack = [];
  let num = 0;
  let operator = "+";

  for (let token of tokens) {
    if (!isNaN(token)) {
      num = parseFloat(token);
      if (operator === "+") stack.push(num);
      else if (operator === "-") stack.push(-num);
      else if (operator === "*") stack.push(stack.pop() * num);
      else if (operator === "/") stack.push(stack.pop() / num);
    } else {
      operator = token;
    }
  }

  return stack.reduce((a, b) => a + b, 0);
}

// Button clicks
buttons.forEach(button => {
  button.addEventListener("click", () => {
    const value = button.getAttribute("data-value");

    if (button.id === "clear") {
      currentInput = "";
      display.value = "";
    } else if (button.id === "equals") {
      try {
        display.value = calculate(currentInput);
        currentInput = display.value;
      } catch {
        display.value = "Error";
        currentInput = "";
      }
    } else {
      currentInput += value;
      display.value = currentInput;
    }
  });
});

// Keyboard support
document.addEventListener("keydown", (e) => {
  const allowed = "0123456789+-*/.";
  if (allowed.includes(e.key)) {
    currentInput += e.key;
    display.value = currentInput;
  } else if (e.key === "Enter") {
    display.value = calculate(currentInput);
    currentInput = display.value;
  } else if (e.key === "Backspace") {
    currentInput = currentInput.slice(0, -1);
    display.value = currentInput;
  } else if (e.key.toLowerCase() === "c") {
    currentInput = "";
    display.value = "";
  }
});
