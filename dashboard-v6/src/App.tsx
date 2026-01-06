import { Suspense } from "react";
import { IntlProvider } from "react-intl";
import { Provider } from "react-redux";

import Loading from "./components/Loading";
import Router from "./Router";
import store from "./store";
import { detect as detect_locale, messages as get_messages } from "./locales";

const locale = detect_locale();
const messages = get_messages(locale);

const Widget = () => {
  return (
    <IntlProvider locale={locale} messages={messages}>
      <Suspense fallback={<Loading />}>
        <Provider store={store}>
          <Router />
        </Provider>
      </Suspense>
    </IntlProvider>
  );
};

export default Widget;
