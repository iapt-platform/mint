import { BrowserRouter } from "react-router-dom";
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
      <BrowserRouter basename={process.env.PUBLIC_URL}>
        <Router />
      </BrowserRouter>
    </IntlProvider>
  );
}

export default Widget;
