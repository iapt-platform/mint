import { configureStore } from "@reduxjs/toolkit";

import currentUserReducer from "./reducers/current-user";
import layoutReducer from "./reducers/layout";
import openArticleReducer from "./reducers/open-article";
import settingReducer from "./reducers/setting";
import commandReducer from "./reducers/command";
import suggestionReducer from "./reducers/suggestion";
import articleModeReducer from "./reducers/article-mode";
import inlineDictReducer from "./reducers/inline-dict";

const store = configureStore({
  reducer: {
    layout: layoutReducer,
    currentUser: currentUserReducer,
    openArticle: openArticleReducer,
    setting: settingReducer,
    command: commandReducer,
    suggestion: suggestionReducer,
    articleMode: articleModeReducer,
    inlineDict: inlineDictReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;
