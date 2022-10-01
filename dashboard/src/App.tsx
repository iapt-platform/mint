import React from "react";
import logo from "./logo.svg";
import "./App.css";

function demo_1(i: number, s: string): string {
  console.log();
  const str = `hello, react!(i = "${i}" s = "${s}")`;
  return str;
}

function App() {
  const demo_s = demo_1(123, "mint");
  return (
    <div className="App">
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <h1>{demo_s}</h1>
        <p>
          Edit <code>src/App.tsx</code> and save to reload.
        </p>
        <a
          className="App-link"
          href="https://reactjs.org"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn React
        </a>
      </header>
    </div>
  );
}

export default App;
