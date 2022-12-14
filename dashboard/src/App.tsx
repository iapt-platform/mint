import { BrowserRouter } from "react-router-dom";
import { ConfigProvider } from "antd";
import { IntlProvider } from "react-intl";
import { Provider } from "react-redux";
import { pdfjs } from "react-pdf";
import mermaid from "mermaid";
import { enableMapSet } from "immer";

import Router from "./Router";
import store from "./store";
import locales, {
  get as getLocale,
  DEFAULT as DEFAULT_LOCALE,
} from "./locales";
import { API_HOST } from "./request";
import onLoad from "./load";

import "./App.css";

pdfjs.GlobalWorkerOptions.workerSrc = `${API_HOST}/assets/pdf.worker.min.js`;
mermaid.initialize({ startOnLoad: true });
enableMapSet();

onLoad();
const lang = getLocale();
const i18n = locales(lang);

function Widget() {
  return (
    <Provider store={store}>
      <IntlProvider
        messages={i18n.messages}
        locale={lang}
        defaultLocale={DEFAULT_LOCALE}
      >
        <ConfigProvider locale={i18n.antd}>
          <BrowserRouter basename={process.env.PUBLIC_URL}>
            <Router />
          </BrowserRouter>
        </ConfigProvider>
      </IntlProvider>
    </Provider>
  );
}

export default Widget;
