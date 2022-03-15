import "@fontsource/roboto/300.css";
import "@fontsource/roboto/400.css";
import "@fontsource/roboto/500.css";
import "@fontsource/roboto/700.css";
import "material-design-icons/iconfont/material-icons.css";
import "react-quill/dist/quill.snow.css";

import "./index.css";

import React from "react";
import ReactDOM from "react-dom";
import { IntlProvider } from "react-intl";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Provider } from "react-redux";
import CachedOutlinedIcon from "@mui/icons-material/CachedOutlined";
import loadable from "@loadable/component";

import plugins from "./plugins";
import { get as getLocale, messages as getMessages } from "./locales";
import NotFound from "./404";
import reportWebVitals from "./reportWebVitals";
import store from "./store";

const lang = getLocale();
const messages = getMessages(lang);

ReactDOM.render(
  <React.StrictMode>
    <Provider store={store}>
      <IntlProvider messages={messages} locale={lang}>
        <BrowserRouter basename="/my/">
          <Routes>
            {plugins.routes.map((it) => {
              const W = loadable(it.component, {
                fallback: <CachedOutlinedIcon />,
              });
              return <Route key={it.path} path={it.path} element={<W />} />;
            })}
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </IntlProvider>
    </Provider>
  </React.StrictMode>,
  document.getElementById("root")
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
