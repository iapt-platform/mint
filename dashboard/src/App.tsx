import React from "react";
import { BrowserRouter } from "react-router-dom";

import Router from "./Router";

import "./App.css";

function Widget() {
  return (
    <BrowserRouter basename={process.env.PUBLIC_URL}>
      <Router />
    </BrowserRouter>
  );
}

export default Widget;
