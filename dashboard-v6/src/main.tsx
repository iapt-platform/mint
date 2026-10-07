import { StrictMode } from "react";
import { createRoot } from "react-dom/client";

import { installApiErrorHandler } from "./api/error";
import App from "./App.tsx";

import "./index.css";

// 移除首次加载的 loading
const removeAppLoading = () => {
  const loadingElement = document.getElementById("app-loading");
  if (loadingElement) {
    loadingElement.style.opacity = "0";
    loadingElement.style.transition = "opacity 0.3s ease-out";
    setTimeout(() => {
      loadingElement.remove();
    }, 300);
  }
};

// v3 API 错误的全局兜底：业务代码不 catch 时消掉 "Uncaught (in promise)" 噪音
installApiErrorHandler();

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <App />
  </StrictMode>
);

// React 渲染完成后移除 loading
removeAppLoading();
