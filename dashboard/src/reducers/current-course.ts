import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";
import {
  ICourseDataResponse,
  ICourseMemberData,
} from "../components/api/Course";

export interface ITextbook {
  course?: ICourseDataResponse;
  courseId: string;
  articleId: string;
  channelId: string;
}
interface IState {
  course?: ITextbook;
  member?: ICourseMemberData[];
}

const initialState: IState = {};

export const slice = createSlice({
  name: "current-course",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ITextbook>) => {
      state.course = action.payload;
    },
    memberRefresh: (state, action: PayloadAction<ICourseMemberData[]>) => {
      state.member = action.payload;
    },
  },
});

export const { refresh, memberRefresh } = slice.actions;

export const currentCourse = (state: RootState): IState => state.currentCourse;

export const courseInfo = (state: RootState): ITextbook | undefined =>
  state.currentCourse.course;

export const memberInfo = (state: RootState): ICourseMemberData[] | undefined =>
  state.currentCourse.member;

export default slice.reducer;
