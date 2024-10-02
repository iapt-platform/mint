import { Suspense } from "react";
import { IntlProvider } from "react-intl";
import { Provider } from "react-redux";
import { BrowserRouter } from "react-router-dom";

import store from "./store";
import Loading from "./components/Loading";
import Router from "./Router";
import { get as getLocale } from "./locales";

import "react-quill/dist/quill.snow.css";

import "./App.css";

const lang = getLocale();

const Widget = () => {
  return (
    <Provider store={store}>
      <IntlProvider messages={{}} locale={lang} defaultLocale={"en-US"}>
        <BrowserRouter basename={import.meta.env.BASE_URL}>
          <Suspense fallback={<Loading />}>
            <Router />
          </Suspense>
        </BrowserRouter>
      </IntlProvider>
    </Provider>
  );
};

export default Widget;
