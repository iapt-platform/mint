import { configureStore } from "@reduxjs/toolkit";

import currentUserReducer from "./reducers/current-user";
import layoutReducer from "./reducers/layout";

const store = configureStore({
  reducer: {
    layout: layoutReducer,
    currentUser: currentUserReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export default store;
