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
import rightPanelReducer from "./reducers/right-panel";
import sentWordsReducer from "./reducers/sent-word";
import netStatusReducer from "./reducers/net-status";
import discussionReducer from "./reducers/discussion";
import wbwReducer from "./reducers/wbw";
import termOrderReducer from "./reducers/term-order";
import focusReducer from "./reducers/focus";
import prLoadReducer from "./reducers/pr-load";
import termClickReducer from "./reducers/term-click";
import cartModeReducer from "./reducers/cart-mode";
import discussionCountReducer from "./reducers/discussion-count";

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
    rightPanel: rightPanelReducer,
    sentWords: sentWordsReducer,
    netStatus: netStatusReducer,
    discussion: discussionReducer,
    wbw: wbwReducer,
    termOrder: termOrderReducer,
    focus: focusReducer,
    prLoad: prLoadReducer,
    termClick: termClickReducer,
    cartMode: cartModeReducer,
    discussionCount: discussionCountReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;
