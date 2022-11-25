import { configureStore } from "@reduxjs/toolkit";

import currentUserReducer from "./reducers/current-user";
import layoutReducer from "./reducers/layout";
import openArticleReducer from "./reducers/open-article";
import settingReducer from "./reducers/setting";
import commandReducer from "./reducers/command";

const store = configureStore({
  reducer: {
    layout: layoutReducer,
    currentUser: currentUserReducer,
    openArticle: openArticleReducer,
    setting: settingReducer,
    command: commandReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;
