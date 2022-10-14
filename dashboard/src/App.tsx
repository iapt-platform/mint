import { BrowserRouter } from "react-router-dom";
import { ConfigProvider } from "antd";
import { IntlProvider } from "react-intl";

import Router from "./Router";
import locales, {
  get as getLocale,
  DEFAULT as DEFAULT_LOCALE,
} from "./locales";

import "./App.css";

const lang = getLocale();
const i18n = locales(lang);

function Widget() {
  return (
    <IntlProvider
      messages={i18n.messages}
      locale={lang}
      defaultLocale={DEFAULT_LOCALE}
    >
      <ConfigProvider locale={i18n.antd}>
        <BrowserRouter basename={process.env.REACT_APP_PUBLIC_URL}>
          <Router />
        </BrowserRouter>
      </ConfigProvider>
    </IntlProvider>
  );
}

export default Widget;
