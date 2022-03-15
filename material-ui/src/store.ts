import { configureStore } from "@reduxjs/toolkit";

import currentUser from "./reducers/current-user";
import siteInfo from "./reducers/site-info";

const store = configureStore({
  reducer: {
    currentUser,
    siteInfo,
  },
});

export type RootState = ReturnType<typeof store.getState>;

export type AppDispatch = typeof store.dispatch;

export default store;
