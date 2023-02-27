import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ITextbook {
  courseId: string;
  articleId: string;
}
interface IState {
  course?: ITextbook;
}

const initialState: IState = {};

export const slice = createSlice({
  name: "current-course",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ITextbook>) => {
      state.course = action.payload;
    },
  },
});

export const { refresh } = slice.actions;

export const currentCourse = (state: RootState): IState => state.currentCourse;

export const courseInfo = (state: RootState): ITextbook | undefined =>
  state.currentCourse.course;

export default slice.reducer;
