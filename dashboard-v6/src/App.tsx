// src/App.tsx
import { App as AntdApp, ConfigProvider, theme } from "antd";
import { Suspense, useEffect, useState } from "react";
import { IntlProvider } from "react-intl";
import { Provider, useSelector } from "react-redux";

import Router from "./Router";
import store from "./store";
import { detect as detect_locale, messages as get_messages } from "./locales";
import Loading from "./components/loading/Loading";
import onLoad from "./load";
import { mode as _mode } from "./reducers/theme";

onLoad();
const locale = detect_locale();
const messages = get_messages(locale);

const prefersDark = window.matchMedia("(prefers-color-scheme: dark)");

const ThemedApp = () => {
  const themeMode = useSelector(_mode);
  const [systemIsDark, setSystemIsDark] = useState(prefersDark.matches);

  useEffect(() => {
    localStorage.setItem("theme/mode", themeMode);
  }, [themeMode]);

  useEffect(() => {
    const handler = (e: MediaQueryListEvent) => setSystemIsDark(e.matches);
    prefersDark.addEventListener("change", handler);
    return () => prefersDark.removeEventListener("change", handler);
  }, []);

  const isDark =
    themeMode === "dark" || (themeMode === "system" && systemIsDark);

  return (
    <ConfigProvider
      theme={{
        algorithm: isDark ? theme.darkAlgorithm : theme.defaultAlgorithm,
        token: {
          colorPrimary: "#1677ff",
          borderRadius: 6,
        },
        components: {
          Menu: {
            collapsedWidth: 40,
          },
        },
      }}
    >
      <AntdApp>
        <Router />
      </AntdApp>
    </ConfigProvider>
  );
};

const Widget = () => {
  return (
    <IntlProvider locale={locale} messages={messages}>
      <Suspense fallback={<Loading />}>
        <Provider store={store}>
          <ThemedApp />
        </Provider>
      </Suspense>
    </IntlProvider>
  );
};

export default Widget;
