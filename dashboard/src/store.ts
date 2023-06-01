import { configureStore } from "@reduxjs/toolkit";

import currentUserReducer from "./reducers/current-user";
import courseUserReducer from "./reducers/course-user";
import layoutReducer from "./reducers/layout";
import openArticleReducer from "./reducers/open-article";
import settingReducer from "./reducers/setting";
import commandReducer from "./reducers/command";
import suggestionReducer from "./reducers/suggestion";
import articleModeReducer from "./reducers/article-mode";
import inlineDictReducer from "./reducers/inline-dict";
import currentCourseReducer from "./reducers/current-course";
import sentenceReducer from "./reducers/sentence";
import themeReducer from "./reducers/theme";
import acceptPrReducer from "./reducers/accept-pr";
import termVocabularyReducer from "./reducers/term-vocabulary";
import nissayaEndingVocabularyReducer from "./reducers/nissaya-ending-vocabulary";
import relationReducer from "./reducers/relation";
import relationAddReducer from "./reducers/relation-add";
import termChangeReducer from "./reducers/term-change";
import paraChangeReducer from "./reducers/para-change";

const store = configureStore({
  reducer: {
    layout: layoutReducer,
    currentUser: currentUserReducer,
    courseUser: courseUserReducer,
    openArticle: openArticleReducer,
    setting: settingReducer,
    command: commandReducer,
    suggestion: suggestionReducer,
    articleMode: articleModeReducer,
    inlineDict: inlineDictReducer,
    currentCourse: currentCourseReducer,
    sentence: sentenceReducer,
    theme: themeReducer,
    acceptPr: acceptPrReducer,
    termVocabulary: termVocabularyReducer,
    nissayaEndingVocabulary: nissayaEndingVocabularyReducer,
    relation: relationReducer,
    relationAdd: relationAddReducer,
    termChange: termChangeReducer,
    paraChange: paraChangeReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;
