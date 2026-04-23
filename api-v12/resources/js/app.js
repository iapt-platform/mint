// resources/js/app.js
import "./bootstrap";
import "./search-suggest";
import { initNavbar } from "./modules/navbar";

document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
});
