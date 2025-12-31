import { configureStore } from "@reduxjs/toolkit";

import layoutReducer from "./reducers/layout";
import sessionReducer from "./reducers/session";

const store = configureStore({
  reducer: {
    layout: layoutReducer,
    session: sessionReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export type AppStore = typeof store;

export default store;
