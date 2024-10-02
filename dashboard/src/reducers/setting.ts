import { createSlice, PayloadAction } from "@reduxjs/toolkit";

import type { RootState } from "../store";

export interface ISettingItem {
  key: string;
  value: string | number | boolean | string[] | undefined;
}

interface IState {
  settings?: ISettingItem[];
  temp?: ISettingItem[];
  key?: string;
  value?: string | string[] | number | boolean;
}

const initialState: IState = {};

const KEY = "user-settings";

const set = (settings: ISettingItem[]) => {
  localStorage.setItem(KEY, JSON.stringify(settings));
};

export const slice = createSlice({
  name: "setting",
  initialState,
  reducers: {
    refresh: (state, action: PayloadAction<ISettingItem[]>) => {
      state.settings = action.payload;
    },
    onChange: (state, action: PayloadAction<ISettingItem>) => {
      state.key = action.payload.key;
      state.value = action.payload.value;
      //将新的改变放入 settings
      if (typeof state.settings !== "undefined") {
        const index = state.settings.findIndex(
          (element) => element.key === action.payload.key
        );
        if (index >= 0) {
          state.settings[index].value = action.payload.value;
        } else {
          state.settings.push(action.payload);
        }
      } else {
        state.settings = [action.payload];
      }
      set(state.settings);
    },
    tempSet: (state, action: PayloadAction<ISettingItem>) => {
      //将新的改变放入 settings
      if (typeof state.temp !== "undefined") {
        const index = state.temp.findIndex(
          (element) => element.key === action.payload.key
        );
        if (index >= 0) {
          state.temp[index].value = action.payload.value;
        } else {
          state.temp.push(action.payload);
        }
      } else {
        state.temp = [action.payload];
      }
    },
  },
});

export const { refresh, onChange, tempSet } = slice.actions;

export const setting = (state: RootState): IState => state.setting;

export const settingInfo = (state: RootState): ISettingItem[] | undefined =>
  state.setting.settings;

export const temp = (state: RootState): ISettingItem[] | undefined =>
  state.setting.temp;

export const onChangeKey = (state: RootState): string | undefined =>
  state.setting.key;
export const onChangeValue = (
  state: RootState
): string | string[] | number | boolean | undefined => state.setting.value;

export default slice.reducer;
